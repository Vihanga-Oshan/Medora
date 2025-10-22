package com.example.base.controller.patient;

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

        System.out.println("\n===== DEBUG: Mark Medication =====");
        System.out.println("Schedule ID: " + req.getParameter("scheduleId"));
        System.out.println("NIC: " + req.getParameter("patientNic"));
        System.out.println("Status: " + req.getParameter("status"));
        System.out.println("TimeSlot: " + req.getParameter("timeSlot"));
        System.out.println("==================================\n");

        // ✅ Read form data
        String scheduleIdStr = req.getParameter("scheduleId");
        String patientNic = req.getParameter("patientNic");
        String status = req.getParameter("status");
        String timeSlot = req.getParameter("timeSlot"); // ✅ new field

        // ✅ Debug (remove later)
        System.out.println("=== Mark Medication Request ===");
        System.out.println("Schedule ID: " + scheduleIdStr);
        System.out.println("Patient NIC: " + patientNic);
        System.out.println("Status: " + status);
        System.out.println("Time Slot: " + timeSlot);

        // ✅ Basic validation
        if (scheduleIdStr == null || patientNic == null || status == null || timeSlot == null ||
                scheduleIdStr.isEmpty() || patientNic.isEmpty() || status.isEmpty() || timeSlot.isEmpty()) {
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

            // ✅ Check if a record already exists for this schedule + date + timeSlot
            String checkSql = """
                SELECT id FROM medication_log
                WHERE medication_schedule_id = ? 
                  AND patient_nic = ? 
                  AND dose_date = ?
                  AND time_slot = ?
            """;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, scheduleId);
                checkStmt.setString(2, patientNic);
                checkStmt.setDate(3, Date.valueOf(today));
                checkStmt.setString(4, timeSlot);

                ResultSet rs = checkStmt.executeQuery();
                if (rs.next()) {
                    // ✅ Update existing record
                    try (PreparedStatement updateStmt = conn.prepareStatement(
                            "UPDATE medication_log SET status = ?, updated_at = NOW() WHERE id = ?")) {
                        updateStmt.setString(1, status.toUpperCase());
                        updateStmt.setInt(2, rs.getInt("id"));
                        updateStmt.executeUpdate();
                        System.out.println("Updated existing record for " + timeSlot);
                    }
                } else {
                    // ✅ Insert new record
                    try (PreparedStatement insertStmt = conn.prepareStatement(
                            "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at) VALUES (?, ?, ?, ?, ?, NOW())")) {
                        insertStmt.setInt(1, scheduleId);
                        insertStmt.setString(2, patientNic);
                        insertStmt.setDate(3, Date.valueOf(today));
                        insertStmt.setString(4, status.toUpperCase());
                        insertStmt.setString(5, timeSlot);
                        insertStmt.executeUpdate();
                        System.out.println("Inserted new record for " + timeSlot);
                    }
                }
            }

            // ✅ Redirect back to dashboard
            resp.sendRedirect(req.getContextPath() + "/patient/dashboard");

        } catch (Exception e) {
            e.printStackTrace();
            // optional for debugging
            throw new ServletException("Error updating medication log", e);
        }
    }
}
