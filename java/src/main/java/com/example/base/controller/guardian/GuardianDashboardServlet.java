package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/dashboard")
public class GuardianDashboardServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified guardian authentication
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: prevent access without token or role
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // Fetch guardian details to get the name
        com.example.base.model.Guardian guardian = com.example.base.dao.GuardianDAO.getGuardianByNic(guardianNic);
        String guardianName = (guardian != null) ? guardian.getName() : guardianNic;

        // Fetch Data
        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.patientDAO patientDAO = new com.example.base.dao.patientDAO(conn);
            com.example.base.dao.NotificationDAO notificationDAO = new com.example.base.dao.NotificationDAO(conn);
            com.example.base.dao.ScheduleDAO scheduleDAO = new com.example.base.dao.ScheduleDAO(conn);

            // 1. Linked Patients
            java.util.List<com.example.base.model.patient> patients = patientDAO.getPatientsByGuardianNic(guardianNic);
            int totalPatients = patients.size();

            // 2. Notifications
            java.util.List<com.example.base.model.Notification> notifications = notificationDAO.getNotificationsByGuardianNic(guardianNic);
            long activeAlertsCount = notifications.stream().filter(n -> !n.isRead()).count();
            
            // Limit recent alerts for dashboard (top 5)
            java.util.List<com.example.base.model.Notification> recentAlerts = notifications.stream().limit(5).collect(java.util.stream.Collectors.toList());

            // 3. Average Adherence (Calculate from all patients)
            int totalAdherenceSum = 0;
            int patientsWithAdherence = 0;
            for (com.example.base.model.patient p : patients) {
                int adherence = scheduleDAO.getOverallAdherence(p.getNic());
                if (adherence > 0) {
                    totalAdherenceSum += adherence;
                    patientsWithAdherence++;
                }
            }
            int avgAdherence = (patientsWithAdherence > 0) ? (totalAdherenceSum / patientsWithAdherence) : 0;

            // Set Attributes
            req.setAttribute("guardianNic", guardianNic);
            req.setAttribute("guardianName", guardianName);
            req.setAttribute("patients", patients);
            req.setAttribute("recentAlerts", recentAlerts);
            req.setAttribute("totalPatients", totalPatients);
            req.setAttribute("activeAlertsCount", activeAlertsCount);
            req.setAttribute("avgAdherence", avgAdherence);

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load dashboard data.");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }

        // ✅ Forward to dashboard JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-dashboard.jsp").forward(req, resp);
    }
}
