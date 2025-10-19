package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;
import com.example.base.util.PasswordUtil;

import java.sql.*;
import java.util.logging.Level;
import java.util.logging.Logger;

public class PharmacistDAO {
    private static final Logger LOGGER = Logger.getLogger(PharmacistDAO.class.getName());

    private final Connection conn;

    public PharmacistDAO(Connection conn) {
        this.conn = conn;
    }

    // Register new pharmacist (store hashed password)
    public void insertPharmacist(Pharmacist pharmacist) throws SQLException {
        // If the caller supplied an explicit ID (>0) insert it into the id column.
        if (pharmacist.getId() > 0) {
            String sql = "INSERT INTO pharmacist (id, name, email, password) VALUES (?, ?, ?, ?)";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, pharmacist.getId());
                stmt.setString(2, pharmacist.getName());
                stmt.setString(3, pharmacist.getEmail());
                // Hash password before storing
                stmt.setString(4, PasswordUtil.hashPassword(pharmacist.getPassword()));
                stmt.executeUpdate();
            }
        } else {
            String sql = "INSERT INTO pharmacist (name, email, password) VALUES (?, ?, ?)";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, pharmacist.getName());
                stmt.setString(2, pharmacist.getEmail());
                // Hash password before storing
                stmt.setString(3, PasswordUtil.hashPassword(pharmacist.getPassword()));
                stmt.executeUpdate();
            }
        }
    }

    // Login validation using hashed password comparison
    public static Pharmacist validate(String email, String password) {
        Pharmacist pharmacist = null;
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM pharmacist WHERE email = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, email);
            stmt.setString(2, PasswordUtil.hashPassword(password));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                pharmacist = new Pharmacist();
                pharmacist.setId(rs.getInt("id"));
                pharmacist.setName(rs.getString("name"));
                pharmacist.setEmail(rs.getString("email"));
                pharmacist.setPassword(rs.getString("password"));
            }
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Error validating pharmacist credentials", e);
        }
        return pharmacist;
    }

    // Login validation by pharmacist id and password
    public static Pharmacist validateById(int id, String password) {
        Pharmacist pharmacist = null;
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM pharmacist WHERE id = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setInt(1, id);
            stmt.setString(2, PasswordUtil.hashPassword(password));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                pharmacist = new Pharmacist();
                pharmacist.setId(rs.getInt("id"));
                pharmacist.setName(rs.getString("name"));
                pharmacist.setEmail(rs.getString("email"));
                pharmacist.setPassword(rs.getString("password"));
            }
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Error validating pharmacist credentials by id", e);
        }
        return pharmacist;
    }

    // Get pharmacist by id
    public Pharmacist getPharmacistById(int id) throws SQLException {
        String sql = "SELECT * FROM pharmacist WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    Pharmacist pharmacist = new Pharmacist();
                    pharmacist.setId(rs.getInt("id"));
                    pharmacist.setName(rs.getString("name"));
                    pharmacist.setEmail(rs.getString("email"));
                    pharmacist.setPassword(rs.getString("password"));
                    return pharmacist;
                }
            }
        }
        return null;
    }

}