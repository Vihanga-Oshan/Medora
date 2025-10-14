package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;

import java.sql.*;

public class PharmacistDAO {

    private Connection conn;

    public PharmacistDAO(Connection conn) {
        this.conn = conn;
    }

    // Register new pharmacist
    public void insertPharmacist(Pharmacist pharmacist) throws SQLException {
        String sql = "INSERT INTO pharmacist (name, email, password) VALUES (?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, pharmacist.getName());
            stmt.setString(2, pharmacist.getEmail());
            stmt.setString(3, pharmacist.getPassword()); // Will hash later
            stmt.executeUpdate();
        }
    }

    // Login validation (plain text for now)
    public static Pharmacist validate(String email, String password) {
        Pharmacist pharmacist = null;
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM pharmacist WHERE email = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, email);
            stmt.setString(2, password);
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                pharmacist = new Pharmacist();
                pharmacist.setId(rs.getInt("id"));
                pharmacist.setName(rs.getString("name"));
                pharmacist.setEmail(rs.getString("email"));
                pharmacist.setPassword(rs.getString("password"));
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return pharmacist;
    }
}