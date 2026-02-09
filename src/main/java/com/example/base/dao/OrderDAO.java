package com.example.base.dao;

import com.example.base.model.Order;
import com.example.base.model.OrderItem;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class OrderDAO {

    public static int createOrder(Connection conn, Order order) throws SQLException {
        String sql = "INSERT INTO orders (patient_nic, total_amount, status, notes) VALUES (?, ?, ?, ?)";
        int orderId = -1;

        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, order.getPatientNic());
            stmt.setDouble(2, order.getTotalAmount());
            stmt.setString(3, order.getStatus() != null ? order.getStatus() : "PENDING");
            stmt.setString(4, order.getNotes());

            stmt.executeUpdate();

            try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    orderId = generatedKeys.getInt(1);
                    order.setId(orderId);
                }
            }
        }

        if (orderId != -1 && order.getItems() != null) {
            createOrderItems(conn, orderId, order.getItems());
        }

        return orderId;
    }

    // ... (keep createOrderItems as is, but we need to update mapOrder)

    private static void createOrderItems(Connection conn, int orderId, List<OrderItem> items) throws SQLException {
        String sql = "INSERT INTO order_items (order_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            for (OrderItem item : items) {
                stmt.setInt(1, orderId);
                stmt.setInt(2, item.getMedicineId());
                stmt.setInt(3, item.getQuantity());
                stmt.setDouble(4, item.getPrice());
                stmt.addBatch();
            }
            stmt.executeBatch();
        }
    }

    public static List<Order> getOrdersByStatus(Connection conn, String status) throws SQLException {
        // For Pharmacist
        String sql = "SELECT o.*, p.name as patient_name FROM orders o " +
                "LEFT JOIN patient p ON o.patient_nic = p.nic " +
                "WHERE status = ? ORDER BY created_at ASC";

        List<Order> list = new ArrayList<>();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, status);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    // Start of mapping - we might want to capture patient_name too in a DTO,
                    // but for now mapping to Order model
                    list.add(mapOrder(rs));
                }
            }
        }
        return list;
    }

    public static List<Order> getAllOrders(Connection conn) throws SQLException {
        List<Order> list = new ArrayList<>();
        String sql = "SELECT * FROM orders ORDER BY created_at DESC";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                list.add(mapOrder(rs));
            }
        }
        return list;
    }

    public static List<Order> getOrdersByPatient(Connection conn, String patientNic) throws SQLException {
        List<Order> list = new ArrayList<>();
        String sql = "SELECT * FROM orders WHERE patient_nic = ? ORDER BY created_at DESC";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    list.add(mapOrder(rs));
                }
            }
        }
        return list;
    }

    public static Order getOrderById(Connection conn, int orderId) throws SQLException {
        Order order = null;
        String sql = "SELECT * FROM orders WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, orderId);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    order = mapOrder(rs);
                }
            }
        }
        if (order != null) {
            order.setItems(getOrderItems(conn, orderId));
        }
        return order;
    }

    public static List<OrderItem> getOrderItems(Connection conn, int orderId) throws SQLException {
        List<OrderItem> items = new ArrayList<>();
        String sql = "SELECT oi.*, m.name as medicine_name, m.image_path FROM order_items oi " +
                "JOIN medicines m ON oi.medicine_id = m.id WHERE oi.order_id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, orderId);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    OrderItem item = new OrderItem();
                    item.setId(rs.getInt("id"));
                    item.setOrderId(rs.getInt("order_id"));
                    item.setMedicineId(rs.getInt("medicine_id"));
                    item.setQuantity(rs.getInt("quantity"));
                    item.setPrice(rs.getDouble("price"));
                    item.setMedicineName(rs.getString("medicine_name"));
                    item.setMedicineImage(rs.getString("image_path"));
                    items.add(item);
                }
            }
        }
        return items;
    }

    public static void updateStatus(Connection conn, int orderId, String status) throws SQLException {
        String sql = "UPDATE orders SET status = ? WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, status);
            stmt.setInt(2, orderId);
            stmt.executeUpdate();
        }
    }

    private static Order mapOrder(ResultSet rs) throws SQLException {
        Order order = new Order();
        order.setId(rs.getInt("id"));
        order.setPatientNic(rs.getString("patient_nic"));
        order.setTotalAmount(rs.getDouble("total_amount"));
        order.setStatus(rs.getString("status"));
        try {
            order.setNotes(rs.getString("notes"));
        } catch (SQLException e) {
            /* ignore */ }
        order.setCreatedAt(rs.getTimestamp("created_at"));
        return order;
    }
}
