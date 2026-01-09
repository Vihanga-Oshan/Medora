package com.example.base.dao;

import com.example.base.model.Notification;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class NotificationDAO {
    private Connection conn;

    public NotificationDAO(Connection conn) {
        this.conn = conn;
    }

    public void createNotification(String patientNic, String message, String type) throws SQLException {
        String sql = "INSERT INTO notifications (patient_nic, message, type, is_read) VALUES (?, ?, ?, 0)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            stmt.setString(2, message);
            stmt.setString(3, type);
            stmt.executeUpdate();
        }
    }

    public List<Notification> getNotificationsByPatient(String patientNic) throws SQLException {
        List<Notification> list = new ArrayList<>();
        String sql = "SELECT * FROM notifications WHERE patient_nic = ? ORDER BY created_at DESC";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    Notification n = new Notification();
                    n.setId(rs.getInt("id"));
                    n.setPatientNic(rs.getString("patient_nic"));
                    n.setMessage(rs.getString("message"));
                    n.setType(rs.getString("type"));
                    n.setRead(rs.getBoolean("is_read"));
                    n.setDate(rs.getTimestamp("created_at").toLocalDateTime());
                    list.add(n);
                }
            }
        }
        return list;
    }

    public void markAsRead(int id) throws SQLException {
        String sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            stmt.executeUpdate();
        }
    }

    public void markAllAsRead(String patientNic) throws SQLException {
        String sql = "UPDATE notifications SET is_read = 1 WHERE patient_nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            stmt.executeUpdate();
        }
    }

    public void deleteNotification(int id) throws SQLException {
        String sql = "DELETE FROM notifications WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            stmt.executeUpdate();
        }
    }

    public void deleteAllNotifications(String patientNic) throws SQLException {
        String sql = "DELETE FROM notifications WHERE patient_nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            stmt.executeUpdate();
        }
    }
}
