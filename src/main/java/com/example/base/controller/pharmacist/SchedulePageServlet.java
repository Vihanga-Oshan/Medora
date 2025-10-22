package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.*;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/schedule")
public class SchedulePageServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(SchedulePageServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ✅ Auth handled by JwtAuthFilter — verify role only
        String role = (String) request.getAttribute("jwtRole");
        String pharmacistId = (String) request.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        String prescriptionId = request.getParameter("id");
        String patientNic = request.getParameter("nic");

        if (prescriptionId == null || prescriptionId.isEmpty() ||
                patientNic == null || patientNic.isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing prescription ID or patient NIC");
            return;
        }

        List<String[]> medicines = new ArrayList<>();
        List<String[]> frequencies = new ArrayList<>();
        List<String[]> dosages = new ArrayList<>();
        List<String[]> mealTimings = new ArrayList<>();
        Prescription prescription = null;

        try (Connection conn = dbconnection.getConnection()) {
            if (conn == null) {
                throw new SQLException("Database connection failed.");
            }

            // ✅ Load prescription details
            PrescriptionDAO prescriptionDAO = new PrescriptionDAO(conn);
            prescription = prescriptionDAO.getPrescriptionById(Integer.parseInt(prescriptionId));

            if (prescription == null) {
                response.sendError(HttpServletResponse.SC_NOT_FOUND, "Prescription not found");
                return;
            }

            // ✅ Load medicines
            try (PreparedStatement stmt = conn.prepareStatement("SELECT id, name FROM medicines");
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    medicines.add(new String[]{rs.getString("id"), rs.getString("name")});
                }
            }

            // ✅ Load frequencies
            try (PreparedStatement stmt = conn.prepareStatement("SELECT id, label, times_of_day FROM frequencies");
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    frequencies.add(new String[]{
                            rs.getString("id"),
                            rs.getString("label"),
                            rs.getString("times_of_day")
                    });
                }
            }

            // ✅ Load dosage categories
            try (PreparedStatement stmt = conn.prepareStatement("SELECT id, label FROM dosage_categories");
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    dosages.add(new String[]{rs.getString("id"), rs.getString("label")});
                }
            }

            // ✅ Load meal timings
            try (PreparedStatement stmt = conn.prepareStatement("SELECT id, label FROM meal_timing");
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    mealTimings.add(new String[]{rs.getString("id"), rs.getString("label")});
                }
            }

        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Database error in SchedulePageServlet", e);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database error: " + e.getMessage());
            return;
        }

        // ✅ Attach data to request
        request.setAttribute("prescription", prescription);
        request.setAttribute("prescriptionId", prescriptionId);
        request.setAttribute("patientNic", patientNic);
        request.setAttribute("medicines", medicines);
        request.setAttribute("frequencies", frequencies);
        request.setAttribute("dosages", dosages);
        request.setAttribute("mealTimings", mealTimings);
        request.setAttribute("pharmacistId", pharmacistId);

        // ✅ Forward to JSP
        RequestDispatcher dispatcher =
                request.getRequestDispatcher("/WEB-INF/views/pharmacist/medication-scheduling.jsp");
        dispatcher.forward(request, response);
    }
}
