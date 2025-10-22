package com.example.base.controller.pharmacist;

import com.example.base.dao.ScheduleDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.MedicationSchedule;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

@WebServlet("/pharmacist/edit-schedule")
public class EditScheduleServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String idParam = request.getParameter("id");
        if (idParam == null || idParam.isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing schedule ID");
            return;
        }

        int scheduleId = Integer.parseInt(idParam);

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO dao = new ScheduleDAO(conn);
            MedicationSchedule schedule = dao.getScheduleById(scheduleId);

            if (schedule == null) {
                response.sendError(HttpServletResponse.SC_NOT_FOUND, "Schedule not found");
                return;
            }

            // ✅ Load dropdown data (same as SchedulePageServlet)
            List<String[]> dosages = new ArrayList<>();
            List<String[]> frequencies = new ArrayList<>();
            List<String[]> mealTimings = new ArrayList<>();

            try (PreparedStatement stmt1 = conn.prepareStatement("SELECT id, label FROM dosage_categories");
                 ResultSet rs1 = stmt1.executeQuery()) {
                while (rs1.next()) {
                    dosages.add(new String[]{rs1.getString("id"), rs1.getString("label")});
                }
            }

            try (PreparedStatement stmt2 = conn.prepareStatement("SELECT id, label FROM frequencies");
                 ResultSet rs2 = stmt2.executeQuery()) {
                while (rs2.next()) {
                    frequencies.add(new String[]{rs2.getString("id"), rs2.getString("label")});
                }
            }

            try (PreparedStatement stmt3 = conn.prepareStatement("SELECT id, label FROM meal_timing");
                 ResultSet rs3 = stmt3.executeQuery()) {
                while (rs3.next()) {
                    mealTimings.add(new String[]{rs3.getString("id"), rs3.getString("label")});
                }
            }

            // ✅ Attach attributes for JSP
            request.setAttribute("schedule", schedule);
            request.setAttribute("dosages", dosages);
            request.setAttribute("frequencies", frequencies);
            request.setAttribute("mealTimings", mealTimings);

            // ✅ Forward to JSP
            request.getRequestDispatcher("/WEB-INF/views/pharmacist/edit-schedule.jsp").forward(request, response);

        } catch (Exception e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to load schedule");
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO dao = new ScheduleDAO(conn);

            int id = Integer.parseInt(request.getParameter("id"));
            String dosage = request.getParameter("dosage");
            String frequency = request.getParameter("frequency");
            String mealTiming = request.getParameter("mealTiming");
            String instructions = request.getParameter("instructions");
            LocalDate startDate = LocalDate.parse(request.getParameter("startDate"));
            int durationDays = Integer.parseInt(request.getParameter("durationDays"));

            boolean updated = dao.updateMedicationSchedule(id, dosage, frequency, mealTiming, instructions, startDate, durationDays);

            if (updated) {
                // redirect to patient’s schedule view
                response.sendRedirect(request.getContextPath() + "/pharmacist/view-schedule?nic=" + request.getParameter("nic"));
            } else {
                request.setAttribute("error", "Failed to update schedule.");
                request.getRequestDispatcher("/WEB-INF/views/pharmacist/edit-schedule.jsp").forward(request, response);
            }

        } catch (Exception e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to update schedule");
        }
    }
}
