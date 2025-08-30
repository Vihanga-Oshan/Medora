package com.example.base.controller;

import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.patient;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

public class registerPatientservlet extends HttpServlet {
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String name = request.getParameter("name");
        String gender = request.getParameter("gender");
        String emergencyContact = request.getParameter("emergencyContact");
        String nic = request.getParameter("nic");
        String email = request.getParameter("email");
        String password = request.getParameter("password");
        String confirmPassword = request.getParameter("confirmPassword");

        String allergies = request.getParameter("allergies");
        String chronic = request.getParameter("chronic");
        String guardianNic = request.getParameter("guardianNic");

        if (!password.equals(confirmPassword)) {
            request.setAttribute("error", "Passwords do not match!");
            request.getRequestDispatcher("register-patient.jsp").forward(request, response);
            return;
        }

        patient p = new patient();
        p.setName(name);
        p.setGender(gender);
        p.setEmergencyContact(emergencyContact);
        p.setNic(nic);
        p.setEmail(email);
        p.setPassword(password); // You should hash this later
        p.setAllergies(allergies);
        p.setChronicIssues(chronic);
        p.setGuardianNic(guardianNic);

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO dao = new patientDAO(conn);
            dao.insertPatient(p);
            response.sendRedirect("login-guardian.jsp"); // or login-patient.jsp
        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("error", "Something went wrong. Please try again.");
            request.getRequestDispatcher("register-patient.jsp").forward(request, response);
        }
    }
}
