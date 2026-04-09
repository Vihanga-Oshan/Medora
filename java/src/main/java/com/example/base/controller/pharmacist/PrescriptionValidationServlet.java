package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/validate")
public class PrescriptionValidationServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PrescriptionValidationServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Auth already handled by JwtAuthFilter — check jwtRole just for safety
        String role = (String) req.getAttribute("jwtRole");
        String pharmacistId = (String) req.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            List<Prescription> prescriptions = dao.getPendingPrescriptions();
            req.setAttribute("prescriptions", prescriptions);
            req.setAttribute("pharmacistId", pharmacistId);
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to load pending prescriptions", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error loading prescriptions");
            return;
        }

        // ✅ Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-validation.jsp").forward(req, resp);
    }
}
