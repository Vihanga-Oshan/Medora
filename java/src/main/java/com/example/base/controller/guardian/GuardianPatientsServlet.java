package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.*;

@WebServlet("/guardian/patients")
public class GuardianPatientsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter should already verify guardian token.
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: if no JWT or invalid token, redirect to login
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        java.sql.Connection conn = null;
        try {
            conn = com.example.base.db.dbconnection.getConnection();
            com.example.base.dao.patientDAO patientDAO = new com.example.base.dao.patientDAO(conn);
            com.example.base.dao.ScheduleDAO scheduleDAO = new com.example.base.dao.ScheduleDAO(conn);

            // 1. Fetch linked patients
            List<com.example.base.model.patient> patients = patientDAO.getPatientsByGuardianNic(guardianNic);
            
            // 2. Determine selected patient
            String selectedNic = req.getParameter("nic");
            com.example.base.model.patient selectedPatient = null;

            if (selectedNic != null && !selectedNic.isEmpty()) {
                // Validate that this patient belongs to the guardian
                selectedPatient = patients.stream()
                        .filter(p -> p.getNic().equals(selectedNic))
                        .findFirst()
                        .orElse(null);
            }

            // Default to first patient if no selection or invalid selection
            if (selectedPatient == null && !patients.isEmpty()) {
                selectedPatient = patients.get(0);
            }

            // 3. Fetch schedule for selected patient (Today)
            List<com.example.base.model.MedicationSchedule> medications = new ArrayList<>();
            if (selectedPatient != null) {
                medications = scheduleDAO.getMedicationByDate(selectedPatient.getNic(), java.time.LocalDate.now());
            }

            req.setAttribute("patients", patients);
            req.setAttribute("selectedPatient", selectedPatient);
            req.setAttribute("medications", medications);
            req.setAttribute("guardianNic", guardianNic);

        } catch (java.sql.SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load patient data.");
        } finally {
            if (conn != null) {
                try { conn.close(); } catch (java.sql.SQLException e) { e.printStackTrace(); }
            }
        }

        // ✅ Forward to JSP view
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-patients.jsp").forward(req, resp);
    }
}
