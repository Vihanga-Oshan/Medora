package com.example.base.dao;

import com.example.base.model.Admin;
import com.example.base.util.PasswordUtil;

import java.sql.*;

public class AdminDAO {
    private final Connection conn;

    public AdminDAO(Connection conn) {
        this.conn = conn;
    }

    public boolean registerAdmin(Admin admin) {
        try {
            String sql = "INSERT INTO admins (email, password, name, nic, contact) VALUES (?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, admin.getEmail());
            stmt.setString(2, PasswordUtil.hashPassword(admin.getPassword())); // hash password
            stmt.setString(3, admin.getFullName());
            stmt.setString(4, admin.getNic());
            stmt.setString(5, admin.getContactNumber());
            int rowsInserted = stmt.executeUpdate();
            return rowsInserted > 0;
        } catch (SQLException e) {
            e.printStackTrace();
            return false;
        }
    }

    public Admin validate(String email, String password) {
        try {
            String hashedPassword = PasswordUtil.hashPassword(password);
            String sql = "SELECT * FROM admins WHERE email = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, email);
            stmt.setString(2, hashedPassword);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                Admin admin = new Admin();
                admin.setId(rs.getInt("id"));
                admin.setEmail(rs.getString("email"));
                admin.setFullName(rs.getString("name"));
                admin.setNic(rs.getString("nic"));
                admin.setContactNumber(rs.getString("contact"));
                return admin;
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return null;
    }

    public Admin getAdminByNIC(String nic) {
        try {
            String sql = "SELECT * FROM admins WHERE nic = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, nic);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                Admin admin = new Admin();
                admin.setId(rs.getInt("id"));
                admin.setEmail(rs.getString("email"));
                admin.setFullName(rs.getString("name"));
                admin.setNic(rs.getString("nic"));
                admin.setContactNumber(rs.getString("contact"));
                return admin;
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return null;
    }
}