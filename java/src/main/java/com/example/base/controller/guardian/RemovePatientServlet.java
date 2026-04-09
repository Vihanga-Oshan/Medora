package com.example.base.controller.guardian;

import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

@WebServlet("/guardian/remove-patient")
public class RemovePatientServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String guardianNic = (String) req.getAttribute("jwtSub");
        String patientNic = req.getParameter("patientNic");

        if (guardianNic == null) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        if (patientNic == null || patientNic.trim().isEmpty()) {
            req.getSession().setAttribute("errorMessage", "Patient NIC is required.");
            resp.sendRedirect(req.getContextPath() + "/guardian/patients");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO dao = new patientDAO(conn);

            // Unlink the patient
            boolean success = dao.unlinkGuardian(patientNic);

            if (success) {
                req.getSession().setAttribute("successMessage", "Patient unlinked successfully.");
            } else {
                req.getSession().setAttribute("errorMessage", "Failed to unlink patient.");
            }

        } catch (SQLException e) {
            e.printStackTrace();
            req.getSession().setAttribute("errorMessage", "Database error: " + e.getMessage());
        }

        resp.sendRedirect(req.getContextPath() + "/guardian/patients");
    }
}
