package com.example.base.controller;

import com.example.base.db.dbconnection;
import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;

@WebServlet("/pharmacist/submitSchedule")
public class SubmitScheduleServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Ensure pharmacist is authenticated (defense-in-depth)
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
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

            // Insert master schedule (optional step)
            PreparedStatement masterStmt = conn.prepareStatement(
                    "INSERT INTO schedule_master (prescription_id, patient_nic) VALUES (?, ?)",
                    Statement.RETURN_GENERATED_KEYS
            );
            masterStmt.setInt(1, Integer.parseInt(prescriptionId));
            masterStmt.setString(2, patientNic);
            masterStmt.executeUpdate();

            int scheduleMasterId = 0;
            try (ResultSet rs = masterStmt.getGeneratedKeys()) {
                if (rs.next()) {
                    scheduleMasterId = rs.getInt(1);
                }
            }
            masterStmt.close();

            // Insert each medication row
            String sql = "INSERT INTO medication_schedule " +
                    "(schedule_master_id, medicine_id, dosage_id, frequency_id, meal_timing_id, start_date, duration_days, instructions) " +
                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(sql);

            for (int i = 0; i < medicineIds.length; i++) {
                stmt.setInt(1, scheduleMasterId);
                stmt.setInt(2, Integer.parseInt(medicineIds[i]));
                stmt.setInt(3, Integer.parseInt(dosageIds[i]));
                stmt.setInt(4, Integer.parseInt(frequencyIds[i]));

                if (mealTimingIds[i] != null && !mealTimingIds[i].isEmpty()) {
                    stmt.setInt(5, Integer.parseInt(mealTimingIds[i]));
                } else {
                    stmt.setNull(5, Types.INTEGER);
                }

                stmt.setDate(6, Date.valueOf(startDates[i]));
                stmt.setInt(7, Integer.parseInt(durationDays[i]));
                stmt.setString(8, (instructions[i] != null) ? instructions[i] : null);

                stmt.addBatch();
            }

            stmt.executeBatch();
            stmt.close();


            PreparedStatement updateStatusStmt = conn.prepareStatement(
                    "UPDATE prescriptions SET status = 'SCHEDULED' WHERE id = ?"
            );
            updateStatusStmt.setInt(1, Integer.parseInt(prescriptionId));
            updateStatusStmt.executeUpdate();
            updateStatusStmt.close();

            response.sendRedirect(request.getContextPath() + "/pharmacist/approved-prescriptions");


        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/pharmacist/schedule-error.jsp");
        }
    }
}
