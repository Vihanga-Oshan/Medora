package com.example.base.dao;

import com.example.base.model.ChatMessage;
import java.sql.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class ChatDAO {
    private Connection connection;

    public ChatDAO(Connection connection) {
        this.connection = connection;
    }

    public boolean addMessage(ChatMessage msg) throws SQLException {
        String query = "INSERT INTO chat_messages (sender_type, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)";
        try (PreparedStatement ps = connection.prepareStatement(query)) {
            ps.setString(1, msg.getSenderType());
            ps.setString(2, msg.getSenderId());
            ps.setString(3, msg.getReceiverId());
            ps.setString(4, msg.getMessageText());
            return ps.executeUpdate() > 0;
        }
    }

    public List<com.example.base.model.ChatConversation> getPharmacistSupplierConversations() throws SQLException {
        List<com.example.base.model.ChatConversation> conversations = new ArrayList<>();
        String query = "SELECT \n" +
                "    s.id, \n" +
                "    s.name, \n" +
                "    m.message_text, \n" +
                "    m.sent_at, \n" +
                "    (SELECT COUNT(*) FROM chat_messages cm WHERE cm.sender_type = 'supplier' AND cm.sender_id = CAST(s.id AS CHAR) AND cm.receiver_id = 'PHARMACIST' AND cm.is_read = 0) as unread\n"
                +
                "FROM supplier s\n" +
                "LEFT JOIN chat_messages m ON m.id = (\n" +
                "    SELECT MAX(id) \n" +
                "    FROM chat_messages m2 \n" +
                "    WHERE (m2.sender_type = 'supplier' AND m2.sender_id = CAST(s.id AS CHAR) AND m2.receiver_id = 'PHARMACIST')\n"
                +
                "       OR (m2.receiver_id = CAST(s.id AS CHAR) AND m2.sender_type = 'pharmacist')\n" +
                ")\n" +
                "ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC";

        try (PreparedStatement ps = connection.prepareStatement(query);
                ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                com.example.base.model.ChatConversation conv = new com.example.base.model.ChatConversation();
                conv.setNic(String.valueOf(rs.getInt("id"))); // Use ID as NIC/Key for suppliers
                conv.setName(rs.getString("name"));
                conv.setLastMessage(rs.getString("message_text"));
                conv.setLastMessageTime(rs.getTimestamp("sent_at"));
                conv.setUnreadCount(rs.getInt("unread"));
                conversations.add(conv);
            }
        }
        return conversations;
    }

    public List<ChatMessage> getMessagesBetween(String userId, String targetId, int lastId, boolean isSupplier)
            throws SQLException {
        List<ChatMessage> messages = new ArrayList<>();
        String query;

        if (isSupplier) {
            // Context: Pharmacist viewing messages for a specific SUPLIER (targetId =
            // supplier ID)
            // OR Supplier viewing their chat (not fully impl yet, but logic is symetric)
            // Here we assume Pharmacist View:
            // 1. Sender = Supplier (ID=targetId) AND Receiver = PHARMACIST
            // 2. Sender = Pharmacist AND Receiver = Supplier (ID=targetId)
            query = "SELECT * FROM chat_messages WHERE id > ? AND (" +
                    "(sender_type = 'supplier' AND sender_id = ? AND receiver_id = 'PHARMACIST') OR " +
                    "(sender_type = 'pharmacist' AND receiver_id = ?) ) " +
                    "ORDER BY sent_at ASC";
        } else if ("PHARMACIST".equals(targetId)) {
            // Context: Patient viewing their shared pharmacist chat
            query = "SELECT * FROM chat_messages WHERE id > ? AND (" +
                    "(sender_id = ? AND receiver_id = 'PHARMACIST') OR " +
                    "(sender_type = 'pharmacist' AND receiver_id = ?)) " +
                    "ORDER BY sent_at ASC";
        } else {
            // Context: Pharmacist viewing messages for a specific patient (targetId =
            // patient NIC)
            query = "SELECT * FROM chat_messages WHERE id > ? AND (" +
                    "(sender_id = ? AND receiver_id = 'PHARMACIST') OR " +
                    "(sender_type = 'pharmacist' AND receiver_id = ?)) " +
                    "ORDER BY sent_at ASC";
        }

        try (PreparedStatement ps = connection.prepareStatement(query)) {
            ps.setInt(1, lastId);
            if (isSupplier) {
                ps.setString(2, targetId);
                ps.setString(3, targetId);
            } else if ("PHARMACIST".equals(targetId)) {
                ps.setString(2, userId); // Patient is sender
                ps.setString(3, userId); // Patient is receiver
            } else {
                ps.setString(2, targetId); // targetId is patient NIC
                ps.setString(3, targetId); // targetId is patient NIC
            }

            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    ChatMessage msg = new ChatMessage();
                    msg.setId(rs.getInt("id"));
                    msg.setSenderType(rs.getString("sender_type"));
                    msg.setSenderId(rs.getString("sender_id"));
                    msg.setReceiverId(rs.getString("receiver_id"));
                    msg.setMessageText(rs.getString("message_text"));
                    msg.setSentAt(rs.getTimestamp("sent_at"));
                    msg.setRead(rs.getBoolean("is_read"));
                    messages.add(msg);
                }
            }
        }
        return messages;
    }

    // Overload for backward compatibility
    public List<ChatMessage> getMessagesBetween(String userId, String targetId, int lastId) throws SQLException {
        return getMessagesBetween(userId, targetId, lastId, false);
    }

    public void markAsRead(String userId, String targetId, boolean isSupplier) throws SQLException {
        String query;
        if (isSupplier) {
            query = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = 'PHARMACIST' AND sender_type = 'supplier' AND sender_id = ? AND is_read = 0";
        } else if ("PHARMACIST".equals(targetId)) {
            // Context: Patient reading messages from the pharmacist pool
            query = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_type = 'pharmacist' AND is_read = 0";
        } else {
            // Context: Pharmacist reading messages from a specific patient
            query = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = 'PHARMACIST' AND sender_id = ? AND is_read = 0";
        }

        try (PreparedStatement ps = connection.prepareStatement(query)) {
            if (isSupplier) {
                ps.setString(1, targetId);
            } else if ("PHARMACIST".equals(targetId)) {
                ps.setString(1, userId); // userId is patient NIC
            } else {
                ps.setString(1, targetId); // targetId is patient NIC
            }
            ps.executeUpdate();
        }
    }

    // Overload
    public void markAsRead(String userId, String targetId) throws SQLException {
        markAsRead(userId, targetId, false);
    }

    public Map<String, Integer> getUnreadCountsForPharmacist() throws SQLException {
        Map<String, Integer> counts = new HashMap<>();
        String query = "SELECT sender_id, COUNT(*) as count FROM chat_messages WHERE receiver_id = 'PHARMACIST' AND is_read = 0 GROUP BY sender_id";
        try (PreparedStatement ps = connection.prepareStatement(query);
                ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                counts.put(rs.getString("sender_id"), rs.getInt("count"));
            }
        }
        return counts;
    }

    public List<com.example.base.model.ChatConversation> getPharmacistConversations() throws SQLException {
        List<com.example.base.model.ChatConversation> conversations = new ArrayList<>();
        String query = "SELECT \n" +
                "    p.nic, \n" +
                "    p.name, \n" +
                "    m.message_text, \n" +
                "    m.sent_at, \n" +
                "    (SELECT COUNT(*) FROM chat_messages cm WHERE cm.sender_id = p.nic AND cm.receiver_id = 'PHARMACIST' AND cm.is_read = 0) as unread\n"
                +
                "FROM patient p\n" +
                "LEFT JOIN chat_messages m ON m.id = (\n" +
                "    SELECT MAX(id) \n" +
                "    FROM chat_messages m2 \n" +
                "    WHERE (m2.sender_id = p.nic AND m2.receiver_id = 'PHARMACIST')\n" +
                "       OR (m2.receiver_id = p.nic AND m2.sender_type = 'pharmacist')\n" +
                ")\n" +
                "ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC";

        try (PreparedStatement ps = connection.prepareStatement(query);
                ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                com.example.base.model.ChatConversation conv = new com.example.base.model.ChatConversation();
                conv.setNic(rs.getString("nic"));
                conv.setName(rs.getString("name"));
                conv.setLastMessage(rs.getString("message_text"));
                conv.setLastMessageTime(rs.getTimestamp("sent_at"));
                conv.setUnreadCount(rs.getInt("unread"));
                conversations.add(conv);
            }
        }
        return conversations;
    }
}
