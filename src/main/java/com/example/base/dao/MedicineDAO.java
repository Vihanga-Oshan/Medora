package com.example.base.dao;

import com.example.base.model.Medicine;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MedicineDAO {

    public static List<Medicine> getAll(Connection conn) throws SQLException {
        List<Medicine> list = new ArrayList<>();
        String sql = "SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.created_at DESC";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                list.add(mapResultSetToMedicine(rs));
            }
        }
        return list;
    }

    public static List<Medicine> search(Connection conn, String query) throws SQLException {
        List<Medicine> list = new ArrayList<>();
        // Search by medicine name, generic name, or category name
        String sql = "SELECT m.*, c.name as category_name FROM medicines m " +
                "LEFT JOIN categories c ON m.category_id = c.id " +
                "WHERE m.name LIKE ? OR m.generic_name LIKE ? OR c.name LIKE ? OR m.description LIKE ? " +
                "ORDER BY m.name ASC";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            String searchPattern = "%" + query + "%";
            stmt.setString(1, searchPattern);
            stmt.setString(2, searchPattern);
            stmt.setString(3, searchPattern);
            stmt.setString(4, searchPattern);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    list.add(mapResultSetToMedicine(rs));
                }
            }
        }
        return list;
    }

    public static Medicine getById(Connection conn, int id) throws SQLException {
        String sql = "SELECT m.*, c.name as category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id WHERE m.id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToMedicine(rs);
                }
            }
        }
        return null;
    }

    public static void insert(Connection conn, Medicine medicine) throws SQLException {
        String sql = "INSERT INTO medicines (name, generic_name, category_id, description, dosage_form, strength, quantity_in_stock, manufacturer, expiry_date, added_by, price, image_path, selling_unit, unit_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            populateStatement(stmt, medicine);
            stmt.executeUpdate();
        }
    }

    public static void update(Connection conn, Medicine medicine) throws SQLException {
        String sql = "UPDATE medicines SET name=?, generic_name=?, category_id=?, description=?, dosage_form=?, strength=?, quantity_in_stock=?, manufacturer=?, expiry_date=?, price=?, image_path=?, selling_unit=?, unit_quantity=? WHERE id=?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, medicine.getName());
            stmt.setString(2, medicine.getGenericName());
            stmt.setInt(3, medicine.getCategoryId());
            stmt.setString(4, medicine.getDescription());
            stmt.setString(5, medicine.getDosageForm());
            stmt.setString(6, medicine.getStrength());
            stmt.setInt(7, medicine.getQuantityInStock());
            stmt.setString(8, medicine.getManufacturer());
            stmt.setDate(9, medicine.getExpiryDate());
            stmt.setDouble(10, medicine.getPrice());
            stmt.setString(11, medicine.getImagePath());
            stmt.setString(12, medicine.getSellingUnit());
            stmt.setInt(13, medicine.getUnitQuantity());
            stmt.setInt(14, medicine.getId());
            stmt.executeUpdate();
        }
    }

    // Helper to map ResultSet to Medicine
    private static Medicine mapResultSetToMedicine(ResultSet rs) throws SQLException {
        Medicine m = new Medicine();
        m.setId(rs.getInt("id"));
        m.setName(rs.getString("name"));
        m.setGenericName(rs.getString("generic_name"));

        // Map category_id and joined category_name
        m.setCategoryId(rs.getInt("category_id"));
        try {
            m.setCategory(rs.getString("category_name"));
        } catch (SQLException e) {
            // If column alias missing or not joined
            m.setCategory("Unknown");
        }

        m.setDescription(rs.getString("description"));
        m.setDosageForm(rs.getString("dosage_form"));
        m.setStrength(rs.getString("strength"));
        m.setQuantityInStock(rs.getInt("quantity_in_stock"));
        m.setManufacturer(rs.getString("manufacturer"));
        m.setExpiryDate(rs.getDate("expiry_date"));
        m.setAddedBy(rs.getInt("added_by"));
        m.setPrice(rs.getDouble("price"));
        m.setImagePath(rs.getString("image_path"));
        m.setSellingUnit(rs.getString("selling_unit"));
        m.setUnitQuantity(rs.getInt("unit_quantity"));
        return m;
    }

    private static void populateStatement(PreparedStatement stmt, Medicine medicine) throws SQLException {
        stmt.setString(1, medicine.getName());
        stmt.setString(2, medicine.getGenericName());
        stmt.setInt(3, medicine.getCategoryId());
        stmt.setString(4, medicine.getDescription());
        stmt.setString(5, medicine.getDosageForm());
        stmt.setString(6, medicine.getStrength());
        stmt.setInt(7, medicine.getQuantityInStock());
        stmt.setString(8, medicine.getManufacturer());
        stmt.setDate(9, medicine.getExpiryDate());
        stmt.setInt(10, medicine.getAddedBy());
        stmt.setDouble(11, medicine.getPrice());
        stmt.setString(12, medicine.getImagePath());
        stmt.setString(13, medicine.getSellingUnit());
        stmt.setInt(14, medicine.getUnitQuantity());
    }

    public static Date getExpiryDateById(Connection conn, int id) throws SQLException {
        String sql = "SELECT expiry_date FROM medicines WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getDate("expiry_date");
            }
        }
        return null;
    }

    public static void reduceStock(Connection conn, int medicineId, int quantity) throws SQLException {
        String sql = "UPDATE medicines SET quantity_in_stock = quantity_in_stock - ? WHERE id = ? AND quantity_in_stock >= ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, quantity);
            stmt.setInt(2, medicineId);
            stmt.setInt(3, quantity);
            int rows = stmt.executeUpdate();
            if (rows == 0) {
                throw new SQLException("Insufficient stock for medicine ID: " + medicineId);
            }
        }
    }

    public static List<Medicine> getByCategory(Connection conn, String category) throws SQLException {
        List<Medicine> list = new ArrayList<>();
        // Filter by category NAME via JOIN
        String sql = "SELECT m.*, c.name as category_name FROM medicines m " +
                "JOIN categories c ON m.category_id = c.id " +
                "WHERE c.name = ? ORDER BY m.name ASC";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, category);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    list.add(mapResultSetToMedicine(rs));
                }
            }
        }
        return list;
    }

    public static void delete(Connection conn, int id) throws SQLException {
        String sql = "DELETE FROM medicines WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            stmt.executeUpdate();
        }
    }
}
