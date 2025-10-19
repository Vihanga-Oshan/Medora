package com.example.base.controller;

import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.time.LocalDate;

@WebServlet("/patient/mark-medication")
public class MarkMedicationStatusServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String scheduleIdStr = req.getParameter("scheduleId");
        String patientNic = req.getParameter("patientNic");
        String status = req.getParameter("status"); // "TAKEN" or "MISSED"

        if (scheduleIdStr == null || patientNic == null || status == null) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing parameters");
            return;
        }

        if (!status.equalsIgnoreCase("TAKEN") && !status.equalsIgnoreCase("MISSED")) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid status value");
            return;
        }

        int scheduleId = Integer.parseInt(scheduleIdStr);
        LocalDate today = LocalDate.now();

        try (Connection conn = dbconnection.getConnection()) {
            String checkSql = """
                SELECT id FROM medication_log
                WHERE medication_schedule_id = ? AND patient_nic = ? AND dose_date = ?
            """;
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, scheduleId);
                checkStmt.setString(2, patientNic);
                checkStmt.setDate(3, Date.valueOf(today));

                ResultSet rs = checkStmt.executeQuery();
                if (rs.next()) {
                    // Update existing record
                    try (PreparedStatement updateStmt = conn.prepareStatement(
                            "UPDATE medication_log SET status = ?, updated_at = NOW() WHERE id = ?")) {
                        updateStmt.setString(1, status.toUpperCase());
                        updateStmt.setInt(2, rs.getInt("id"));
                        updateStmt.executeUpdate();
                    }
                } else {
                    // Insert new record
                    try (PreparedStatement insertStmt = conn.prepareStatement(
                            "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status) VALUES (?, ?, ?, ?)")) {
                        insertStmt.setInt(1, scheduleId);
                        insertStmt.setString(2, patientNic);
                        insertStmt.setDate(3, Date.valueOf(today));
                        insertStmt.setString(4, status.toUpperCase());
                        insertStmt.executeUpdate();
                    }
                }
            }

            // ✅ redirect back to dashboard
            resp.sendRedirect(req.getContextPath() + "/patient/dashboard");

        } catch (Exception e) {
            e.printStackTrace();
            resp.sendRedirect(req.getContextPath() + "/patient/dashboard?error=1");
        }
    }
}
