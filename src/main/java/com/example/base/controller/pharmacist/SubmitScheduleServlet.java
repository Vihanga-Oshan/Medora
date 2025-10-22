package com.example.base.controller.pharmacist;

import com.example.base.db.dbconnection;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/submitSchedule")
public class SubmitScheduleServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(SubmitScheduleServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ✅ Auth handled by JwtAuthFilter — verify role
        String role = (String) request.getAttribute("jwtRole");
        String pharmacistId = (String) request.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        String prescriptionId = request.getParameter("prescriptionId");
        String patientNic = request.getParameter("patientNic");

        String[] medicineIds = request.getParameterValues("medicineId");
        String[] dosageIds = request.getParameterValues("dosageId");
        String[] frequencyIds = request.getParameterValues("frequencyId");
        String[] mealTimingIds = request.getParameterValues("mealTimingId");
        String[] startDates = request.getParameterValues("startDate");
        String[] durationDays = request.getParameterValues("durationDays");
        String[] instructions = request.getParameterValues("instructions");

        if (medicineIds == null || medicineIds.length == 0) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "No medicines submitted.");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            if (conn == null) throw new SQLException("Database connection failed.");

            conn.setAutoCommit(false); // ✅ Prevent partial inserts

            // ✅ Insert into schedule_master
            int scheduleMasterId;
            try (PreparedStatement masterStmt = conn.prepareStatement(
                    "INSERT INTO schedule_master (prescription_id, patient_nic, created_by) VALUES (?, ?, ?)",
                    Statement.RETURN_GENERATED_KEYS)) {

                masterStmt.setInt(1, Integer.parseInt(prescriptionId));
                masterStmt.setString(2, patientNic);
                masterStmt.setString(3, pharmacistId); // pharmacist ID from JWT
                masterStmt.executeUpdate();

                try (ResultSet rs = masterStmt.getGeneratedKeys()) {
                    if (!rs.next()) throw new SQLException("Failed to create schedule master record.");
                    scheduleMasterId = rs.getInt(1);
                }
            }

            // ✅ Insert individual medication schedules
            try (PreparedStatement stmt = conn.prepareStatement(
                    "INSERT INTO medication_schedule " +
                            "(schedule_master_id, medicine_id, dosage_id, frequency_id, meal_timing_id, start_date, duration_days, instructions) " +
                            "VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {

                for (int i = 0; i < medicineIds.length; i++) {
                    stmt.setInt(1, scheduleMasterId);
                    stmt.setInt(2, Integer.parseInt(medicineIds[i]));
                    stmt.setInt(3, Integer.parseInt(dosageIds[i]));
                    stmt.setInt(4, Integer.parseInt(frequencyIds[i]));

                    if (mealTimingIds != null && mealTimingIds[i] != null && !mealTimingIds[i].isEmpty()) {
                        stmt.setInt(5, Integer.parseInt(mealTimingIds[i]));
                    } else {
                        stmt.setNull(5, Types.INTEGER);
                    }

                    stmt.setDate(6, Date.valueOf(startDates[i]));
                    stmt.setInt(7, Integer.parseInt(durationDays[i]));
                    stmt.setString(8, (instructions != null && instructions[i] != null) ? instructions[i] : null);
                    stmt.addBatch();
                }

                stmt.executeBatch();
            }

            // ✅ Update prescription status
            try (PreparedStatement updateStmt = conn.prepareStatement(
                    "UPDATE prescriptions SET status = 'SCHEDULED' WHERE id = ?")) {
                updateStmt.setInt(1, Integer.parseInt(prescriptionId));
                updateStmt.executeUpdate();
            }

            conn.commit(); // ✅ Commit transaction
            response.sendRedirect(request.getContextPath() + "/pharmacist/approved-prescriptions");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error submitting medication schedule", e);
            response.sendRedirect(request.getContextPath() + "/pharmacist/schedule-error.jsp");
        }
    }
}
