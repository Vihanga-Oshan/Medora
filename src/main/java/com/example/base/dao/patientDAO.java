package com.example.base.dao;

import com.example.base.model.patient;
import java.sql.*;

public class patientDAO {
    private Connection conn;

    public patientDAO(Connection conn) {
        this.conn = conn;
    }

    public void insertPatient(patient patient) throws SQLException {
        String sql = "INSERT INTO patient (full_name, gender, age, nic, email, emergency_contact) VALUES (?, ?, ?, ?, ?, ?)";
        PreparedStatement stmt = conn.prepareStatement(sql);
        stmt.setString(1, patient.getFullName());
        stmt.setString(2, patient.getGender());
        stmt.setInt(3, patient.getAge());
        stmt.setString(4, patient.getNic());
        stmt.setString(5, patient.getEmail());
        stmt.setString(6, patient.getEmergencyContact());
        stmt.executeUpdate();
    }
}
