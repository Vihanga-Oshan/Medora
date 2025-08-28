package com.example.base.controller;

import com.example.base.model.patient;
import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/registerPatientservlet")

public class registerPatientservlet extends HttpServlet {
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        patient patient = new patient();
        patient.setFullName(req.getParameter("fullName"));
        patient.setGender(req.getParameter("gender"));
        patient.setAge(Integer.parseInt(req.getParameter("age")));
        patient.setNic(req.getParameter("nic"));
        patient.setEmail(req.getParameter("email"));
        patient.setEmergencyContact(req.getParameter("emergencyContact"));

        try (Connection conn = dbconnection.getConnection()) {
            new patientDAO(conn).insertPatient(patient);
            resp.sendRedirect("register/success.jsp");
        } catch (Exception e) {
            e.printStackTrace();
            resp.sendRedirect("register/fail.jsp");
        }
    }
}
