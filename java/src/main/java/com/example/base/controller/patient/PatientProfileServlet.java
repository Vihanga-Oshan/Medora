package com.example.base.controller.patient;

import com.example.base.dao.patientDAO;
import com.example.base.model.patient;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/profile")
public class PatientProfileServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PatientProfileServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Use JWT attributes set by JwtAuthFilter
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO dao = new patientDAO(conn);
            patient p = dao.getPatientByNIC(patientNic);

            if (p == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Patient not found");
                return;
            }

            req.setAttribute("patient", p);
            req.getRequestDispatcher("/WEB-INF/views/patient/profile.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading patient profile for NIC: " + patientNic, e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to load profile");
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        HttpSession session = req.getSession();

        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        String firstName = req.getParameter("firstName");
        String lastName = req.getParameter("lastName"); // Combined in model as name? Or split? Model only has "name"
        String phone = req.getParameter("phone");
        String address = req.getParameter("address");

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO dao = new patientDAO(conn);
            patient p = dao.getPatientByNIC(patientNic);

            if (p == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Patient not found");
                return;
            }

            // ✅ Update the model
            if (firstName != null && lastName != null) {
                p.setName(firstName + " " + lastName);
            } else if (firstName != null) {
                p.setName(firstName);
            }

            p.setPhone(phone);
            p.setAddress(address);

            // ✅ Persist to DB
            dao.updatePatient(p);

            req.setAttribute("patient", p);
            session.setAttribute("patient", p); // Update global session patient
            req.setAttribute("message", "Profile updated successfully");
            req.getRequestDispatcher("/WEB-INF/views/patient/profile.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error updating patient profile for NIC: " + patientNic, e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to update profile");
        }
    }
}
