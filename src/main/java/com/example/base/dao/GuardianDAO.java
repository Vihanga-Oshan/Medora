package com.example.base.dao;

import com.example.base.model.Guardian;
import java.sql.*;

public class GuardianDAO {
    private Connection conn;

    public GuardianDAO(Connection conn) {
        this.conn = conn;
    }

    public void insertGuardian(Guardian guardian) throws SQLException {
        String sql = "INSERT INTO guardian (nic, g_name, contact_number, email, password) VALUES (?, ?, ?, ?, ?)";
        PreparedStatement stmt = conn.prepareStatement(sql);
        stmt.setString(1, guardian.getNic());
        stmt.setString(2, guardian.getName());
        stmt.setString(3, guardian.getContactNumber());
        stmt.setString(4, guardian.getEmail());
        stmt.setString(5, guardian.getPassword());
        stmt.executeUpdate();
    }
}
