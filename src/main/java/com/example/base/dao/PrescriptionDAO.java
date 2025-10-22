package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class PrescriptionDAO {
    private Connection conn;

    public PrescriptionDAO(Connection conn) {
        this.conn = conn;
    }

    // ✅ Insert new prescription
    public void insertPrescription(Prescription p) throws SQLException {
        String sql = "INSERT INTO prescriptions (patient_nic, file_name, file_path, status) VALUES (?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, p.getPatientNic());
            stmt.setString(2, p.getFileName());
            stmt.setString(3, p.getFilePath());
            stmt.setString(4, p.getStatus());
            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    p.setId(rs.getInt(1));
                }
            }
        }
    }

    // ✅ Get all prescriptions for a patient
    public List<Prescription> getPrescriptionsByPatient(String patientNic) throws SQLException {
        List<Prescription> prescriptions = new ArrayList<>();
        String sql = "SELECT * FROM prescriptions WHERE patient_nic = ? ORDER BY upload_date DESC";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, patientNic);
            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    Prescription p = new Prescription();
                    p.setId(rs.getInt("id"));
                    p.setPatientNic(rs.getString("patient_nic"));
                    p.setFileName(rs.getString("file_name"));
                    p.setFilePath(rs.getString("file_path"));
                    p.setUploadDate(rs.getTimestamp("upload_date").toLocalDateTime());
                    p.setStatus(rs.getString("status"));
                    prescriptions.add(p);
                }
            }
        }
        return prescriptions;
    }

    // ✅ Get count of pending prescriptions (for pharmacist dashboard)
    public int getPendingPrescriptionCount() throws SQLException {
        String sql = "SELECT COUNT(*) FROM prescriptions WHERE status = 'PENDING'";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt(1);
            }
        }
        return 0;
    }

    // ✅ Get all pending prescriptions (for validation page)
    public List<Prescription> getPendingPrescriptions() throws SQLException {
        List<Prescription> prescriptions = new ArrayList<>();
        String sql = "SELECT * FROM prescriptions WHERE status = 'PENDING' ORDER BY upload_date DESC";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Prescription p = new Prescription();
                p.setId(rs.getInt("id"));
                p.setPatientNic(rs.getString("patient_nic"));
                p.setFileName(rs.getString("file_name"));
                p.setFilePath(rs.getString("file_path"));
                p.setUploadDate(rs.getTimestamp("upload_date").toLocalDateTime());
                p.setStatus(rs.getString("status"));
                prescriptions.add(p);
            }
        }
        return prescriptions;
    }

    // ✅ Update prescription status (APPROVE / REJECT)
    public void updatePrescriptionStatus(int prescriptionId, String status) {
        String sql = "UPDATE prescriptions SET status = ? WHERE id = ?"; // Use correct table name

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            // build debug SQL for logging (replaces ? with parameter literals safely)
            String debugSql = sql;
            Object[] params = new Object[] { status, prescriptionId };
            for (Object p : params) {
                String literal;
                if (p == null) {
                    literal = "NULL";
                } else if (p instanceof String) {
                    literal = "'" + ((String) p).replace("'", "''") + "'";
                } else {
                    literal = p.toString();
                }
                debugSql = debugSql.replaceFirst("\\?", java.util.regex.Matcher.quoteReplacement(literal));
            }
            System.out.println("DEBUG SQL (updatePrescriptionStatus): " + debugSql);

            stmt.setString(1, status);
            stmt.setInt(2, prescriptionId);
            stmt.executeUpdate();

        } catch (SQLException e) {
            e.printStackTrace(); // Or use logging
        }
    }



    public void updateStatus(int prescriptionId, String status) {
        String sql = "UPDATE prescriptions SET status = ? WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, status);
            stmt.setInt(2, prescriptionId);
            stmt.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace(); // Log error for debugging
        }


    }
    public Prescription getPrescriptionById(int id) throws SQLException {
        String sql = "SELECT * FROM prescriptions WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    Prescription p = new Prescription();
                    p.setId(rs.getInt("id"));
                    p.setPatientNic(rs.getString("patient_nic"));
                    p.setFilePath(rs.getString("file_path"));
                    p.setFileName(rs.getString("file_name"));
                    p.setUploadDate(rs.getTimestamp("upload_date").toLocalDateTime());  // or the correct column name
                    // set other fields as needed
                    return p;
                }
            }
        }
        return null;
    }

    // ✅ Get a prescription by stored file path (used for authorization checks)
    public Prescription getPrescriptionByFilePath(String filePath) throws SQLException {
        String sql = "SELECT * FROM prescriptions WHERE file_path = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, filePath);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    Prescription p = new Prescription();
                    p.setId(rs.getInt("id"));
                    p.setPatientNic(rs.getString("patient_nic"));
                    p.setFileName(rs.getString("file_name"));
                    p.setFilePath(rs.getString("file_path"));
                    p.setUploadDate(rs.getTimestamp("upload_date").toLocalDateTime());
                    p.setStatus(rs.getString("status"));
                    return p;
                }
            }
        }
        return null;
    }

    public List<Prescription> getPrescriptionsByStatus(String status) throws SQLException {
        List<Prescription> prescriptions = new ArrayList<>();
        String sql = "SELECT * FROM prescriptions WHERE status = ?";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, status);
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                Prescription p = new Prescription();
                p.setId(rs.getInt("id"));
                p.setPatientNic(rs.getString("patient_nic"));
                p.setFilePath(rs.getString("file_path"));
                p.setFileName(rs.getString("file_name"));
                p.setUploadDate(rs.getTimestamp("upload_date").toLocalDateTime());
                p.setStatus(rs.getString("status"));
                prescriptions.add(p);
            }
        }
        return prescriptions;
    }

    public void updatePrescriptionName(int id, String newName) throws SQLException {
        String sql = "UPDATE prescriptions SET file_name = ? WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, newName);
            stmt.setInt(2, id);
            stmt.executeUpdate();
        }
    }




}
