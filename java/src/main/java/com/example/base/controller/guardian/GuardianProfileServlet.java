package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/profile")
public class GuardianProfileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already validates guardian token and injects claims
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: handle direct access without a valid JWT
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.GuardianDAO guardianDAO = new com.example.base.dao.GuardianDAO(conn);
            com.example.base.dao.patientDAO patientDAO = new com.example.base.dao.patientDAO(conn);

            // 1. Fetch Guardian Details
            com.example.base.model.Guardian guardian = guardianDAO.getGuardianByNic(guardianNic);
            req.setAttribute("guardian", guardian);

            // 2. Fetch Linked Patients
            java.util.List<com.example.base.model.patient> patients = patientDAO.getPatientsByGuardianNic(guardianNic);
            req.setAttribute("patients", patients);

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load profile.");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }

        // ✅ Forward to profile JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-profile.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Ensure the user is authenticated
        String guardianNic = (String) req.getAttribute("jwtSub");
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        String name = req.getParameter("name"); // Combined name in form
        String email = req.getParameter("email");
        String contact = req.getParameter("contact");

        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.GuardianDAO guardianDAO = new com.example.base.dao.GuardianDAO(conn);

            com.example.base.model.Guardian guardian = new com.example.base.model.Guardian();
            guardian.setNic(guardianNic);
            guardian.setName(name);
            guardian.setEmail(email);
            guardian.setContactNumber(contact);

            guardianDAO.updateGuardian(guardian);
            req.setAttribute("message", "Profile updated successfully!");
            req.setAttribute("messageType", "success");

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("message", "Failed to update profile.");
            req.setAttribute("messageType", "error");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }
        
        // Reload data for view
        doGet(req, resp);
    }
}
