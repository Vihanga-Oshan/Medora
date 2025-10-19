package com.example.base.controller;

import com.example.base.db.dbconnection;
import com.example.base.dao.PrescriptionDAO;
import com.example.base.model.Prescription;

import javax.servlet.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.*;

@WebServlet("/pharmacist/schedule")
public class SchedulePageServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Ensure pharmacist is authenticated (defense-in-depth)
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
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

            PreparedStatement stmt;
            ResultSet rs;

            // ✅ Load medicines
            stmt = conn.prepareStatement("SELECT id, name FROM medicines");
            rs = stmt.executeQuery();
            while (rs.next()) {
                medicines.add(new String[]{rs.getString("id"), rs.getString("name")});
            }
            rs.close();
            stmt.close();

            stmt = conn.prepareStatement("SELECT id, label, times_of_day FROM frequencies");
            rs = stmt.executeQuery();
            while (rs.next()) {
                frequencies.add(new String[]{
                        rs.getString("id"),
                        rs.getString("label"),
                        rs.getString("times_of_day") // e.g. "morning,day,night"
                });
            }


            // ✅ Load dosage categories
            stmt = conn.prepareStatement("SELECT id, label FROM dosage_categories");
            rs = stmt.executeQuery();
            while (rs.next()) {
                dosages.add(new String[]{rs.getString("id"), rs.getString("label")});
            }
            rs.close();
            stmt.close();

            stmt = conn.prepareStatement("SELECT id, label FROM meal_timing");
            rs = stmt.executeQuery();
            while (rs.next()) {
                mealTimings.add(new String[]{rs.getString("id"), rs.getString("label")});
            }
            rs.close();
            stmt.close();

        } catch (SQLException e) {
            e.printStackTrace();
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

        // ✅ Forward to JSP
        RequestDispatcher dispatcher =
                request.getRequestDispatcher("/WEB-INF/views/pharmacist/medication-scheduling.jsp");
        dispatcher.forward(request, response);
    }
}
