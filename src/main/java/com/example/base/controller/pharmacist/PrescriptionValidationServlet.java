package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
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

        // Server-side check to ensure only authenticated pharmacists can access this page.
        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
            // Redirect to pharmacist login (use context path)
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            List<Prescription> prescriptions = dao.getPendingPrescriptions();
            req.setAttribute("prescriptions", prescriptions);
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to load pending prescriptions", e);
        }

        req.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-validation.jsp").forward(req, resp);
    }
}