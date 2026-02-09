package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.patient;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

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
                patient.setPhone(rs.getString("phone"));
                patient.setGender(rs.getString("gender"));
                patient.setEmergencyContact(rs.getString("emergency_contact"));
                patient.setEmail(rs.getString("email"));
                patient.setPassword(rs.getString("password"));
                patient.setAllergies(rs.getString("allergies"));
                patient.setChronicIssues(rs.getString("chronic_issues"));
                patient.setAddress(rs.getString("address"));
                patient.setGuardianNic(rs.getString("guardian_nic"));
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        return patient;
    }

    public void insertPatient(patient patient) throws SQLException {
        String sql = "INSERT INTO patient (nic, name, phone, gender, emergency_contact, email, password, allergies, chronic_issues, address, guardian_nic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        PreparedStatement stmt = conn.prepareStatement(sql);
        stmt.setString(1, patient.getNic());
        stmt.setString(2, patient.getName());
        stmt.setString(3, patient.getPhone());
        stmt.setString(4, patient.getGender());
        stmt.setString(5, patient.getEmergencyContact());
        stmt.setString(6, patient.getEmail());
        stmt.setString(7, patient.getPassword());
        stmt.setString(8, patient.getAllergies());
        stmt.setString(9, patient.getChronicIssues());
        stmt.setString(10, patient.getAddress());
        stmt.setString(11, patient.getGuardianNic());
        stmt.executeUpdate();
    }

    // Add this method to patientDAO.java
    public patient getPatientByNIC(String nic) throws SQLException {
        String sql = "SELECT * FROM patient WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, nic);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    patient p = new patient();
                    p.setNic(rs.getString("nic"));
                    p.setName(rs.getString("name"));
                    p.setPhone(rs.getString("phone"));
                    p.setGender(rs.getString("gender"));
                    p.setEmergencyContact(rs.getString("emergency_contact"));
                    p.setEmail(rs.getString("email"));
                    p.setAllergies(rs.getString("allergies"));
                    p.setChronicIssues(rs.getString("chronic_issues"));
                    p.setAddress(rs.getString("address"));
                    p.setGuardianNic(rs.getString("guardian_nic"));
                    return p;
                }
            }
        }
        return null;
    }

    public List<patient> getAllPatients() throws SQLException {
        List<patient> patients = new ArrayList<>();
        String sql = "SELECT * FROM patient ORDER BY name";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                patient p = new patient();
                p.setNic(rs.getString("nic"));
                p.setName(rs.getString("name"));
                p.setPhone(rs.getString("phone"));
                p.setGender(rs.getString("gender"));
                p.setEmail(rs.getString("email"));
                p.setEmergencyContact(rs.getString("emergency_contact"));
                p.setAllergies(rs.getString("allergies"));
                p.setChronicIssues(rs.getString("chronic_issues"));
                p.setAddress(rs.getString("address"));
                p.setGuardianNic(rs.getString("guardian_nic"));
                patients.add(p);
            }
        }
        return patients;
    }

    public boolean updatePatient(patient p) throws SQLException {
        String sql = "UPDATE patient SET name = ?, phone = ?, emergency_contact = ?, gender = ?, allergies = ?, chronic_issues = ?, address = ? WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, p.getName());
            stmt.setString(2, p.getPhone());
            stmt.setString(3, p.getEmergencyContact());
            stmt.setString(4, p.getGender());
            stmt.setString(5, p.getAllergies());
            stmt.setString(6, p.getChronicIssues());
            stmt.setString(7, p.getAddress());
            stmt.setString(8, p.getNic());
            return stmt.executeUpdate() > 0;
        }
    }
}
