package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/reports")
public class GuardianReportsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified JWT and role = guardian
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth check
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // ✅ Fetch Data
        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.patientDAO patientDAO = new com.example.base.dao.patientDAO(conn);
            com.example.base.dao.ScheduleDAO scheduleDAO = new com.example.base.dao.ScheduleDAO(conn);

            // 1. Fetch linked patients
            java.util.List<com.example.base.model.patient> patients = patientDAO.getPatientsByGuardianNic(guardianNic);

            // 2. Select patient
            String selectedNic = req.getParameter("nic");
            com.example.base.model.patient selectedPatient = null;

            if (selectedNic != null && !selectedNic.isEmpty()) {
                selectedPatient = patients.stream().filter(p -> p.getNic().equals(selectedNic)).findFirst().orElse(null);
            }
            if (selectedPatient == null && !patients.isEmpty()) {
                selectedPatient = patients.get(0);
            }

            if (selectedPatient != null) {
                // 3. Fetch Stats
                int overallAdherence = scheduleDAO.getOverallAdherence(selectedPatient.getNic());
                java.util.Map<String, Integer> adherenceStats = scheduleDAO.getAdherenceStats(selectedPatient.getNic());
                java.util.List<java.util.Map<String, Object>> weeklyAdherence = scheduleDAO.getWeeklyAdherence(selectedPatient.getNic());

                req.setAttribute("overallAdherence", overallAdherence);
                req.setAttribute("adherenceStats", adherenceStats);
                req.setAttribute("weeklyAdherence", weeklyAdherence); // For chart if needed
            }

            req.setAttribute("guardianNic", guardianNic);
            req.setAttribute("patients", patients);
            req.setAttribute("selectedPatient", selectedPatient);

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load reports.");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }

        // ✅ Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-reports.jsp").forward(req, resp);
    }
}
