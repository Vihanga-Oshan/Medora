package com.example.base.controller.pharmacist;

import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.patient;

import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/patients")
public class PatientListServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PatientListServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ✅ Use JWT attributes injected by JwtAuthFilter
        String role = (String) request.getAttribute("jwtRole");
        String pharmacistId = (String) request.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            patientDAO pdao = new patientDAO(conn);
            List<patient> patientList = pdao.getAllPatients();
            request.setAttribute("patientList", patientList);
            request.setAttribute("pharmacistId", pharmacistId);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load patient list", e);
            request.setAttribute("errorMessage", "Failed to load patient list.");
            request.setAttribute("patientList", new ArrayList<>()); // fallback
        }

        // ✅ Forward to JSP
        RequestDispatcher dispatcher =
                request.getRequestDispatcher("/WEB-INF/views/pharmacist/patient-list.jsp");
        dispatcher.forward(request, response);
    }
}
