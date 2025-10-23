package com.example.base.dao;

import com.example.base.model.Medicine;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MedicineDAO {

    public static List<Medicine> getAll(Connection conn) throws SQLException {
        List<Medicine> list = new ArrayList<>();
        String sql = "SELECT * FROM medicines ORDER BY created_at DESC";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                Medicine m = new Medicine();
                m.setId(rs.getInt("id")); // ✅ Include ID
                m.setName(rs.getString("name"));
                m.setGenericName(rs.getString("generic_name"));
                m.setCategory(rs.getString("category"));
                m.setDescription(rs.getString("description"));
                m.setDosageForm(rs.getString("dosage_form"));
                m.setStrength(rs.getString("strength"));
                m.setQuantityInStock(rs.getInt("quantity_in_stock"));
                m.setManufacturer(rs.getString("manufacturer"));
                m.setExpiryDate(rs.getDate("expiry_date"));
                m.setAddedBy(rs.getInt("added_by"));
                list.add(m);
            }
        }
        return list;
    }

    public static void insert(Connection conn, Medicine medicine) throws SQLException {
        String sql = "INSERT INTO medicines (name, generic_name, category, description, dosage_form, strength, quantity_in_stock, manufacturer, expiry_date, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, medicine.getName());
            stmt.setString(2, medicine.getGenericName());
            stmt.setString(3, medicine.getCategory());
            stmt.setString(4, medicine.getDescription());
            stmt.setString(5, medicine.getDosageForm());
            stmt.setString(6, medicine.getStrength());
            stmt.setInt(7, medicine.getQuantityInStock());
            stmt.setString(8, medicine.getManufacturer());
            stmt.setDate(9, medicine.getExpiryDate());
            stmt.setInt(10, medicine.getAddedBy());
            stmt.executeUpdate();
        }
    }
    public static void update(Connection conn, Medicine medicine) throws SQLException {
        String sql = "UPDATE medicines SET name=?, generic_name=?, category=?, description=?, dosage_form=?, strength=?, quantity_in_stock=?, manufacturer=?, expiry_date=? WHERE id=?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, medicine.getName());
            stmt.setString(2, medicine.getGenericName());
            stmt.setString(3, medicine.getCategory());
            stmt.setString(4, medicine.getDescription());
            stmt.setString(5, medicine.getDosageForm());
            stmt.setString(6, medicine.getStrength());
            stmt.setInt(7, medicine.getQuantityInStock());
            stmt.setString(8, medicine.getManufacturer());
            stmt.setDate(9, medicine.getExpiryDate());
            stmt.setInt(10, medicine.getId());
            stmt.executeUpdate();
        }
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

    public static void delete(Connection conn, int id) throws SQLException {
        String sql = "DELETE FROM medicines WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            stmt.executeUpdate();
        }
    }
}
