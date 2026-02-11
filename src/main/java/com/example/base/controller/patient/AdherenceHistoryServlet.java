package com.example.base.controller.patient;

import com.example.base.dao.ScheduleDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/adherence-history")
public class AdherenceHistoryServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AdherenceHistoryServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JWT-based authentication check (Roles set by JwtAuthFilter)
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO scheduleDAO = new ScheduleDAO(conn);

            // 1. Overall Adherence Percentage
            int overallAdherence = scheduleDAO.getOverallAdherence(patientNic);

            // 2. Weekly Adherence (last 7 days)
            List<Map<String, Object>> weeklyAdherence = scheduleDAO.getWeeklyAdherence(patientNic);

            // 3. Complete Medication History
            List<Map<String, String>> medicationHistory = scheduleDAO.getMedicationHistory(patientNic);

            // Set all data as attributes for JSP
            req.setAttribute("overallAdherence", overallAdherence);
            req.setAttribute("weeklyAdherence", weeklyAdherence);
            req.setAttribute("medicationHistory", medicationHistory);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load adherence history for NIC: " + patientNic, e);
            req.setAttribute("error", "Failed to load history. Please try again later.");
        }

        // ✅ Forward to the JSP
        req.getRequestDispatcher("/WEB-INF/views/patient/adherence-history.jsp").forward(req, resp);
    }
}
