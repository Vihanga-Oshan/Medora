package com.example.base.dao;

import com.example.base.model.Supplier;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class SupplierDAO {
    private Connection conn;

    public SupplierDAO(Connection conn) {
        this.conn = conn;
    }

    public List<Supplier> getAllSuppliers() throws SQLException {
        List<Supplier> suppliers = new ArrayList<>();
        String sql = "SELECT * FROM supplier ORDER BY name";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Supplier s = new Supplier();
                s.setId(rs.getInt("id"));
                s.setName(rs.getString("name"));
                s.setContactNumber(rs.getString("contact_number"));
                s.setEmail(rs.getString("email"));
                s.setCreatedAt(rs.getTimestamp("created_at"));
                suppliers.add(s);
            }
        }
        return suppliers;
    }

    public Supplier getSupplierById(int id) throws SQLException {
        String sql = "SELECT * FROM supplier WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    Supplier s = new Supplier();
                    s.setId(rs.getInt("id"));
                    s.setName(rs.getString("name"));
                    s.setContactNumber(rs.getString("contact_number"));
                    s.setEmail(rs.getString("email"));
                    s.setCreatedAt(rs.getTimestamp("created_at"));
                    return s;
                }
            }
        }
        return null;
    }
}
