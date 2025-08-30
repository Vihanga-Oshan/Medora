package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.patient;
import java.sql.*;

public class patientDAO {
    private Connection conn;

    public patientDAO(Connection conn) {
        this.conn = conn;
    }

    public static patient validate(String nic, String password) {
        patient patient = null;

        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM patient WHERE nic = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, nic);
            stmt.setString(2, password);
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                patient = new patient();
                patient.setNic(rs.getString("nic"));
                patient.setName(rs.getString("name"));
                patient.setGender(rs.getString("gender"));
                patient.setEmergencyContact(rs.getString("emergency_contact"));
                patient.setEmail(rs.getString("email"));
                patient.setPassword(rs.getString("password"));
                patient.setAllergies(rs.getString("allergies"));
                patient.setChronicIssues(rs.getString("chronic_issues"));
                patient.setGuardianNic(rs.getString("guardian_nic"));
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        return patient;
    }


    public void insertPatient(patient patient) throws SQLException {
        String sql = "INSERT INTO patient (nic, name, gender, emergency_contact, email, password, allergies, chronic_issues, guardian_nic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        PreparedStatement stmt = conn.prepareStatement(sql);
        stmt.setString(1, patient.getNic());
        stmt.setString(2, patient.getName());
        stmt.setString(3, patient.getGender());
        stmt.setString(4, patient.getEmergencyContact());
        stmt.setString(5, patient.getEmail());
        stmt.setString(6, patient.getPassword());
        stmt.setString(7, patient.getAllergies());
        stmt.setString(8, patient.getChronicIssues());
        stmt.setString(9, patient.getGuardianNic());
        stmt.executeUpdate();
    }
}
