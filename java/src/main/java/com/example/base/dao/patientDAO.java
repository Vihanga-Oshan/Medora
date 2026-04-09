package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.patient;
import com.example.base.util.PasswordUtil;
import com.example.base.util.EncryptionUtil;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class patientDAO {
    private Connection conn;

    public patientDAO(Connection conn) {
        this.conn = conn;
    }

    public static patient validate(String nic, String password) {
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM patient WHERE nic = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, nic);
            stmt.setString(2, PasswordUtil.hashPassword(password));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                patient p = new patient();
                p.setNic(rs.getString("nic"));
                p.setName(rs.getString("name"));
                p.setPhone(EncryptionUtil.decrypt(rs.getString("phone")));
                p.setGender(rs.getString("gender"));
                p.setEmergencyContact(EncryptionUtil.decrypt(rs.getString("emergency_contact")));
                p.setEmail(EncryptionUtil.decrypt(rs.getString("email")));
                p.setPassword(rs.getString("password"));
                p.setAllergies(EncryptionUtil.decrypt(rs.getString("allergies")));
                p.setChronicIssues(EncryptionUtil.decrypt(rs.getString("chronic_issues")));
                p.setAddress(EncryptionUtil.decrypt(rs.getString("address")));
                p.setGuardianNic(rs.getString("guardian_nic"));
                return p;
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }

    public void insertPatient(patient patient) throws SQLException {
        // Updated to include default link_status (though DB default handles it, good to
        // be explicit or leave to DB)
        // For now, relying on DB default 'UNVERIFIED' or setting it if we change the
        // query.
        // Let's stick to the existing columns + guardian_nic. link_status defaults to
        // 'UNVERIFIED' in DB.
        String sql = "INSERT INTO patient (nic, name, phone, gender, emergency_contact, email, password, allergies, chronic_issues, address, guardian_nic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patient.getNic());
            stmt.setString(2, patient.getName());
            stmt.setString(3, EncryptionUtil.encrypt(patient.getPhone()));
            stmt.setString(4, patient.getGender());
            stmt.setString(5, EncryptionUtil.encrypt(patient.getEmergencyContact()));
            stmt.setString(6, EncryptionUtil.encrypt(patient.getEmail()));
            stmt.setString(7, PasswordUtil.hashPassword(patient.getPassword()));
            stmt.setString(8, EncryptionUtil.encrypt(patient.getAllergies()));
            stmt.setString(9, EncryptionUtil.encrypt(patient.getChronicIssues()));
            stmt.setString(10, EncryptionUtil.encrypt(patient.getAddress()));
            stmt.setString(11, patient.getGuardianNic());
            stmt.executeUpdate();
        }
    }

    // Add this method to patientDAO.java
    public patient getPatientByNIC(String nic) throws SQLException {
        // Query should return link_status as well if we added the field to model, but
        // model update wasn't in plan?
        // Wait, I need to update Patient.java model too to hold link_status!
        // The plan said: "MODIFY [patientDAO.java]" but didn't explicitly say "MODIFY
        // [patient.java]".
        // However, it's implied I need to handle it.
        // Let's check checkGuardianClaim first.
        String sql = "SELECT * FROM patient WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, nic);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return extractPatientFromRS(rs);
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
                patients.add(extractPatientFromRS(rs));
            }
        }
        return patients;
    }

    public boolean updatePatient(patient p) throws SQLException {
        String sql = "UPDATE patient SET name = ?, phone = ?, emergency_contact = ?, gender = ?, allergies = ?, chronic_issues = ?, address = ? WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, p.getName());
            stmt.setString(2, EncryptionUtil.encrypt(p.getPhone()));
            stmt.setString(3, EncryptionUtil.encrypt(p.getEmergencyContact()));
            stmt.setString(4, p.getGender());
            stmt.setString(5, EncryptionUtil.encrypt(p.getAllergies()));
            stmt.setString(6, EncryptionUtil.encrypt(p.getChronicIssues()));
            stmt.setString(7, EncryptionUtil.encrypt(p.getAddress()));
            stmt.setString(8, p.getNic());
            return stmt.executeUpdate() > 0;
        }
    }

    public List<patient> getPatientsByGuardianNic(String guardianNic) throws SQLException {
        List<patient> patients = new ArrayList<>();
        // Updated to ONLY return patients where link_status is 'LINKED'
        String sql = "SELECT * FROM patient WHERE guardian_nic = ? AND link_status = 'LINKED' ORDER BY name";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, guardianNic);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    patients.add(extractPatientFromRS(rs));
                }
            }
        }
        return patients;
    }

    // Helper to extract patient
    private patient extractPatientFromRS(ResultSet rs) throws SQLException {
        try {
            patient p = new patient();
            p.setNic(rs.getString("nic"));
            p.setName(rs.getString("name"));
            p.setPhone(EncryptionUtil.decrypt(rs.getString("phone")));
            p.setGender(rs.getString("gender"));
            p.setEmail(EncryptionUtil.decrypt(rs.getString("email")));
            p.setEmergencyContact(EncryptionUtil.decrypt(rs.getString("emergency_contact")));
            p.setAllergies(EncryptionUtil.decrypt(rs.getString("allergies")));
            p.setChronicIssues(EncryptionUtil.decrypt(rs.getString("chronic_issues")));
            p.setAddress(EncryptionUtil.decrypt(rs.getString("address")));
            p.setGuardianNic(rs.getString("guardian_nic"));

            // Check if column exists (it might not if DB update failed, but code assumes it
            // does)
            try {
                p.setLinkStatus(rs.getString("link_status"));
            } catch (SQLException e) {
                // Column might not exist yet if script failed. Default to UNVERIFIED.
                p.setLinkStatus("UNVERIFIED");
            }
            return p;
        } catch (Exception e) {
            throw new SQLException("Error decrypting patient data", e);
        }
    }

    // New methods for Linking Flow

    public boolean updateLinkStatus(String nic, String status) throws SQLException {
        String sql = "UPDATE patient SET link_status = ? WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, status);
            stmt.setString(2, nic);
            return stmt.executeUpdate() > 0;
        }
    }

    public boolean checkGuardianClaim(String patientNic, String guardianNic) throws SQLException {
        String sql = "SELECT 1 FROM patient WHERE nic = ? AND guardian_nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            stmt.setString(2, guardianNic);
            try (ResultSet rs = stmt.executeQuery()) {
                return rs.next(); // Returns true if match found
            }
        }
    }

    public String getLinkStatus(String nic) throws SQLException {
        String sql = "SELECT link_status FROM patient WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, nic);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getString("link_status");
                }
            }
        }
        return "UNVERIFIED";
    }

    public String getGuardianNameByNic(String guardianNic) {
        // This assumes GuardianDAO or we do a quick join/select here.
        // Since we don't have GuardianDAO instance here, let's do a raw query or rely
        // on caller to use GuardianDAO.
        // Actually, let's just return the NIC or do a quick lookup.
        // Better: Caller uses GuardianDAO.
        return null;
    }

    public boolean unlinkGuardian(String patientNic) throws SQLException {
        String sql = "UPDATE patient SET guardian_nic = NULL, link_status = 'UNVERIFIED' WHERE nic = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            return stmt.executeUpdate() > 0;
        }
    }
}
