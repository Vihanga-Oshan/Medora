package com.example.base.controller;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/pharmacist/prescription-review")
public class PrescriptionReviewServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

//        HttpSession session = req.getSession(false);
//        if (session == null || session.getAttribute("pharmacist") == null) {
//            resp.sendRedirect(req.getContextPath() + "/login/pharmacist");
//            return;
//        }

        String idParam = req.getParameter("id");
        if (idParam == null) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Prescription ID required");
            return;
        }

        int prescriptionId = Integer.parseInt(idParam);

        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            Prescription prescription = dao.getPrescriptionById(prescriptionId);

            if (prescription == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Prescription not found");
                return;
            }

            // Get patient info (you can add a PatientDAO later)
            // For now, just show NIC and leave other fields blank
            req.setAttribute("prescription", prescription);
            req.setAttribute("patientName", "John Doe"); // Replace with real data later
            req.setAttribute("patientDOB", "15/03/1985");
            req.setAttribute("patientMRN", "MRN-789456");
            req.setAttribute("prescribedBy", "Dr. Smith");
            req.setAttribute("medication", "Amoxicillin 500mg");
            req.setAttribute("quantity", "10 tablets");

            req.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-review.jsp").forward(req, resp);
        } catch (Exception e) {
            e.printStackTrace();
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Internal error");
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
            resp.sendRedirect(req.getContextPath() + "/login/pharmacist");
            return;
        }

        String action = req.getParameter("action");
        String idParam = req.getParameter("prescriptionId");

        if (action == null || idParam == null) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid request");
            return;
        }

        int prescriptionId = Integer.parseInt(idParam);

        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            dao.updatePrescriptionStatus(prescriptionId, action); // APPROVE / REJECT
        } catch (Exception e) {
            e.printStackTrace();
        }

        resp.sendRedirect(req.getContextPath() + "/pharmacist/validate");
    }
}