package com.example.base.controller.patient;

import com.example.base.model.patient;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/patient/profile")
public class PatientProfileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // Retrieve patient object from session
        patient patient = (patient) session.getAttribute("patient");
        req.setAttribute("patient", patient);

        req.getRequestDispatcher("/WEB-INF/views/patient/profile.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // Get updated form data
        String firstName = req.getParameter("firstName");
        String phone = req.getParameter("phone");

        // Update session object (You should also update DB in real use case)
        patient patient = (patient) session.getAttribute("patient");
        patient.setName(firstName);
        patient.setEmergencyContact(phone);

        // Save updated patient back into session
        session.setAttribute("patient", patient);
        req.setAttribute("message", "Profile updated successfully");
        req.setAttribute("patient", patient);
        req.getRequestDispatcher("/WEB-INF/views/patient/profile.jsp").forward(req, resp);
    }
}
