package com.example.base.controller;

import com.example.base.dao.PrescriptionDAO;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/dashboard")
public class PharmacistDashboardServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PharmacistDashboardServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        // Get count of pending prescriptions
        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            int pendingCount = dao.getPendingPrescriptionCount();
            req.setAttribute("pendingCount", pendingCount);
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to load dashboard data", e);
        }

        req.getRequestDispatcher("/WEB-INF/views/pharmacist/pharmacist-dashboard.jsp").forward(req, resp);
    }
}