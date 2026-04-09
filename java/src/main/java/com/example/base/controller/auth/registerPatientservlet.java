package com.example.base.controller.auth;

import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.patient;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLIntegrityConstraintViolationException;

@WebServlet("/patient/register")
public class registerPatientservlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Show the form (JSP must be under WEB-INF)
        req.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");

        String name             = request.getParameter("name");
        String gender           = request.getParameter("gender");
        String emergencyContact = request.getParameter("emergencyContact");
        String nic              = request.getParameter("nic");
        String email            = request.getParameter("email");
        String password         = request.getParameter("password");
        String confirmPassword  = request.getParameter("confirmPassword");
        String allergies        = request.getParameter("allergies");
        String chronic          = request.getParameter("chronic");
        String guardianNic      = request.getParameter("guardianNic");

        // Basic validation
        if (name == null || name.isBlank()
                || nic == null || nic.isBlank()
                || password == null || password.isBlank()
                || confirmPassword == null || confirmPassword.isBlank()) {
            request.setAttribute("error", "Please fill all required fields.");
            request.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(request, response);
            return;
        }
        if (!password.equals(confirmPassword)) {
            request.setAttribute("error", "Passwords do not match!");
            request.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(request, response);
            return;
        }

        // Build model
        patient p = new patient();
        p.setName(name);
        p.setGender(gender);
        p.setEmergencyContact(emergencyContact);
        p.setNic(nic);
        p.setEmail(email);
        p.setPassword(password); // (You can hash later; focus now is fixing flow)
        p.setAllergies(allergies);
        p.setChronicIssues(chronic);
        p.setGuardianNic(guardianNic);

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO dao = new patientDAO(conn);
            dao.insertPatient(p);

            // Success → redirect to the login controller route (NOT a JSP file)
            response.sendRedirect(request.getContextPath() + "/login");
        } catch (SQLIntegrityConstraintViolationException dup) {
            // Likely duplicate NIC/email
            request.setAttribute("error", "This NIC is already registered.");
            request.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(request, response);
        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("error", "Something went wrong. Please try again.");
            request.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(request, response);
        }
    }
}
