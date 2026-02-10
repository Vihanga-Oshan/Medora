package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.Guardian;
import com.example.base.util.PasswordUtil;
import java.sql.*;

public class GuardianDAO {
    private Connection conn;

    public GuardianDAO(Connection conn) {
        this.conn = conn;
    }

    public void insertGuardian(Guardian guardian) throws SQLException {
        String sql = "INSERT INTO guardian (nic, g_name, contact_number, email, password) VALUES (?, ?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, guardian.getNic());
            stmt.setString(2, guardian.getName());
            stmt.setString(3, guardian.getContactNumber());
            stmt.setString(4, guardian.getEmail());
            stmt.setString(5, PasswordUtil.hashPassword(guardian.getPassword()));
            stmt.executeUpdate();
        }
    }

    public static Guardian validate(String nic, String password) {
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM guardian WHERE nic = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, nic);
            stmt.setString(2, PasswordUtil.hashPassword(password));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                Guardian guardian = new Guardian();
                guardian.setNic(rs.getString("nic"));
                guardian.setName(rs.getString("g_name"));
                guardian.setContactNumber(rs.getString("contact_number"));
                guardian.setEmail(rs.getString("email"));
                guardian.setPassword(rs.getString("password"));
                return guardian;
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }

}
