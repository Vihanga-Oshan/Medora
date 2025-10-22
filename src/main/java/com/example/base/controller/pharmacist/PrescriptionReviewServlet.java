package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.dao.patientDAO;
import com.example.base.model.Prescription;
import com.example.base.model.patient;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/prescription-review")
public class PrescriptionReviewServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PrescriptionReviewServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Auth handled by JwtAuthFilter — just verify role for safety
        String role = (String) req.getAttribute("jwtRole");
        String pharmacistId = (String) req.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        String idParam = req.getParameter("id");
        if (idParam == null || idParam.trim().isEmpty()) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Prescription ID required");
            return;
        }

        int prescriptionId;
        try {
            prescriptionId = Integer.parseInt(idParam);
        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid Prescription ID");
            return;
        }

        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO prescriptionDAO = new PrescriptionDAO(conn);
            Prescription prescription = prescriptionDAO.getPrescriptionById(prescriptionId);

            if (prescription == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Prescription not found");
                return;
            }

            // ✅ Fetch patient details using NIC
            String patientNic = prescription.getPatientNic();
            patientDAO patientDao = new patientDAO(conn);
            patient patient = patientDao.getPatientByNIC(patientNic);

            if (patient == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Patient not found");
                return;
            }

            // Pass data to JSP
            req.setAttribute("prescription", prescription);
            req.setAttribute("patient", patient);

            req.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-review.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error in PrescriptionReviewServlet", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Internal error");
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Auth handled by JwtAuthFilter
        String role = (String) req.getAttribute("jwtRole");
        if (role == null || !"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        String action = req.getParameter("action");
        String idParam = req.getParameter("prescriptionId");

        if (action == null || idParam == null) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid request");
            return;
        }

        try {
            int prescriptionId = Integer.parseInt(idParam);
            try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
                PrescriptionDAO dao = new PrescriptionDAO(conn);
                dao.updatePrescriptionStatus(prescriptionId, action);
            }
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to update prescription status", e);
        }

        resp.sendRedirect(req.getContextPath() + "/pharmacist/validate");
    }
}
