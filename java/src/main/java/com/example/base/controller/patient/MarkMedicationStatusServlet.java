package com.example.base.controller.patient;

import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.time.LocalDate;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/mark-medication")
public class MarkMedicationStatusServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(MarkMedicationStatusServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        LOGGER.info("=== Mark Medication Request ===");

        // ✅ Get identity from JWT (set by JwtAuthFilter)
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            LOGGER.warning("Unauthorized or missing JWT");
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // ✅ Read form data
        String scheduleIdStr = req.getParameter("scheduleId");
        String status = req.getParameter("status");
        String timeSlot = req.getParameter("timeSlot");

        LOGGER.info(() -> String.format("Schedule ID=%s, Patient NIC=%s, Status=%s, TimeSlot=%s",
                scheduleIdStr, patientNic, status, timeSlot));

        // ✅ Basic validation
        if (scheduleIdStr == null || status == null || timeSlot == null ||
                scheduleIdStr.isEmpty() || status.isEmpty() || timeSlot.isEmpty()) {
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
                        LOGGER.info("Updated existing record for " + timeSlot);
                    }
                } else {
                    // ✅ Insert new record
                    try (PreparedStatement insertStmt = conn.prepareStatement(
                            "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at) " +
                                    "VALUES (?, ?, ?, ?, ?, NOW())")) {
                        insertStmt.setInt(1, scheduleId);
                        insertStmt.setString(2, patientNic);
                        insertStmt.setDate(3, Date.valueOf(today));
                        insertStmt.setString(4, status.toUpperCase());
                        insertStmt.setString(5, timeSlot);
                        insertStmt.executeUpdate();
                        LOGGER.info("Inserted new record for " + timeSlot);
                    }
                }
            }

            // ✅ Redirect back to dashboard
            resp.sendRedirect(req.getContextPath() + "/patient/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error updating medication log", e);
            throw new ServletException("Error updating medication log", e);
        }
    }
}
