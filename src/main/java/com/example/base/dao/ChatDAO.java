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

    public List<ChatMessage> getMessagesBetween(String userId, String targetId, int lastId) throws SQLException {
        List<ChatMessage> messages = new ArrayList<>();

        // Revised query:
        // 1. Messages sent by the patient to 'PHARMACIST'
        // 2. Messages sent by ANY pharmacist to this patient (receiver_id = patientId)
        String query;
        if ("PHARMACIST".equals(targetId)) {
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
            if ("PHARMACIST".equals(targetId)) {
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

    public void markAsRead(String userId, String targetId) throws SQLException {
        String query;
        if ("PHARMACIST".equals(targetId)) {
            // Context: Patient reading messages from the pharmacist pool
            query = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_type = 'pharmacist' AND is_read = 0";
        } else {
            // Context: Pharmacist reading messages from a specific patient
            query = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = 'PHARMACIST' AND sender_id = ? AND is_read = 0";
        }

        try (PreparedStatement ps = connection.prepareStatement(query)) {
            if ("PHARMACIST".equals(targetId)) {
                ps.setString(1, userId); // userId is patient NIC
            } else {
                ps.setString(1, targetId); // targetId is patient NIC
            }
            ps.executeUpdate();
        }
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
}
