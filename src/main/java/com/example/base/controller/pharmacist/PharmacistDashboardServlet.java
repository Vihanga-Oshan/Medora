package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;

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


        String role = (String) req.getAttribute("jwtRole");
        String pharmacistId = (String) req.getAttribute("jwtSub");


        if (!"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }


        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            int pendingCount = dao.getPendingPrescriptionCount();

            req.setAttribute("pendingCount", pendingCount);
            req.setAttribute("pharmacistId", pharmacistId);

        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to load pharmacist dashboard data", e);
        }


        req.getRequestDispatcher("/WEB-INF/views/pharmacist/pharmacist-dashboard.jsp").forward(req, resp);
    }
}
