package com.example.base.controller.guardian;

import com.example.base.model.Alert;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.*;

@WebServlet("/guardian/alerts")
public class GuardianAlertsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String guardianNic = (String) req.getAttribute("jwtSub");
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.NotificationDAO notificationDAO = new com.example.base.dao.NotificationDAO(conn);
            com.example.base.dao.patientDAO patientDAO = new com.example.base.dao.patientDAO(conn);

            // 1. Fetch Patients and map NIC -> Patient (for names/phones)
            List<com.example.base.model.patient> patients = patientDAO.getPatientsByGuardianNic(guardianNic);
            Map<String, com.example.base.model.patient> patientMap = new HashMap<>();
            for (com.example.base.model.patient p : patients) {
                patientMap.put(p.getNic(), p);
            }

            // 2. Fetch Notifications
            List<com.example.base.model.Notification> notifications = notificationDAO.getNotificationsByGuardianNic(guardianNic);

            // 3. Calculate Stats
            int totalAlerts = notifications.size();
            long unreadAlerts = notifications.stream().filter(n -> !n.isRead()).count();
            long resolvedAlerts = totalAlerts - unreadAlerts;

            req.setAttribute("guardianNic", guardianNic);
            req.setAttribute("notifications", notifications);
            req.setAttribute("patientMap", patientMap);
            req.setAttribute("totalAlerts", totalAlerts);
            req.setAttribute("unreadAlerts", unreadAlerts);
            req.setAttribute("resolvedAlerts", resolvedAlerts);

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load alerts.");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }

        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-alerts.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String guardianNic = (String) req.getAttribute("jwtSub");
        if (guardianNic == null) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        String action = req.getParameter("action");
        
        try (java.sql.Connection conn = com.example.base.db.dbconnection.getConnection()) {
            com.example.base.dao.NotificationDAO notificationDAO = new com.example.base.dao.NotificationDAO(conn);

            if ("markAsRead".equals(action)) {
                int id = Integer.parseInt(req.getParameter("id"));
                notificationDAO.markAsRead(id);
            } else if ("markAllRead".equals(action)) {
                // Need a method to mark all for guardian's patients
                // For now, iterate or add new DAO method. Let's start with iterating or implementing later.
                // Assuming we just refresh for now as the button exists.
                // Or implementing a loop here:
                List<com.example.base.model.Notification> notifications = notificationDAO.getNotificationsByGuardianNic(guardianNic);
                for(com.example.base.model.Notification n : notifications) {
                    if(!n.isRead()) notificationDAO.markAsRead(n.getId());
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        resp.sendRedirect(req.getContextPath() + "/guardian/alerts");
    }
}
