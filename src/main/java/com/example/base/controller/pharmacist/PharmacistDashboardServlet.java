package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.*;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/dashboard")
public class PharmacistDashboardServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PharmacistDashboardServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String role = (String) req.getAttribute("jwtRole");
        String pharmacistId = (String) req.getAttribute("jwtSub");

        if (!"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO prescriptionDAO = new PrescriptionDAO(conn);

            // 1. Fetch counts for metric cards
            int pendingCount = prescriptionDAO.getPendingPrescriptionCount();
            int approvedCount = getApprovedPrescriptionCount(conn);
            int newPatientCount = getNewPatientCount(conn);

            // 2. Fetch pharmacist name
            String pharmacistName = getPharmacistName(conn, pharmacistId);
            if (pharmacistName == null) {
                pharmacistName = "Pharmacist";
            }

            // 3. Fetch patients needing checks (with pending prescriptions)
            List<Map<String, String>> patientsNeedingCheck = getPatientsNeedingCheck(conn);

            // 4. Fetch patients needing schedules (with approved prescriptions but no
            // schedule)
            List<Map<String, String>> patientsNeedingSchedule = getPatientsNeedingSchedule(conn);

            // 5. Set current date and time
            LocalDateTime now = LocalDateTime.now();
            DateTimeFormatter dateFormatter = DateTimeFormatter.ofPattern("dd MMMM yyyy");
            DateTimeFormatter timeFormatter = DateTimeFormatter.ofPattern("HH:mm:ss");
            String currentDate = now.format(dateFormatter);
            String currentTime = now.format(timeFormatter);
            String greeting = getGreeting(now.getHour());

            // Set all attributes
            req.setAttribute("pendingCount", pendingCount);
            req.setAttribute("approvedCount", approvedCount);
            req.setAttribute("newPatientCount", newPatientCount);
            req.setAttribute("pharmacistName", pharmacistName);
            req.setAttribute("patientsNeedingCheck", patientsNeedingCheck);
            req.setAttribute("patientsNeedingSchedule", patientsNeedingSchedule);
            req.setAttribute("currentDate", currentDate);
            req.setAttribute("currentTime", currentTime);
            req.setAttribute("greeting", greeting);

        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Failed to load pharmacist dashboard data", e);
        }

        req.getRequestDispatcher("/WEB-INF/views/pharmacist/pharmacist-dashboard.jsp").forward(req, resp);
    }

    private int getApprovedPrescriptionCount(Connection conn) throws SQLException {
        String sql = "SELECT COUNT(*) FROM prescriptions WHERE status = 'APPROVED'";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt(1);
            }
        }
        return 0;
    }

    private int getNewPatientCount(Connection conn) throws SQLException {
        String sql = "SELECT COUNT(*) FROM patient WHERE created_at >= NOW() - INTERVAL 1 DAY";
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt(1);
            }
        }
        return 0;
    }

    private List<Map<String, String>> getPatientsNeedingCheck(Connection conn) throws SQLException {
        List<Map<String, String>> patients = new ArrayList<>();
        String sql = "SELECT DISTINCT p.name, p.chronic_issues, p.nic " +
                "FROM patient p " +
                "INNER JOIN prescriptions pr ON p.nic = pr.patient_nic " +
                "WHERE pr.status = 'PENDING' " +
                "LIMIT 5";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Map<String, String> patient = new HashMap<>();
                patient.put("name", rs.getString("name"));
                patient.put("condition",
                        rs.getString("chronic_issues") != null ? rs.getString("chronic_issues") : "N/A");
                patient.put("nic", rs.getString("nic"));
                patients.add(patient);
            }
        }
        return patients;
    }

    private List<Map<String, String>> getPatientsNeedingSchedule(Connection conn) throws SQLException {
        List<Map<String, String>> patients = new ArrayList<>();
        String sql = "SELECT DISTINCT p.name, p.chronic_issues, p.nic " +
                "FROM patient p " +
                "INNER JOIN prescriptions pr ON p.nic = pr.patient_nic " +
                "WHERE pr.status = 'APPROVED' " +
                "LIMIT 5";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Map<String, String> patient = new HashMap<>();
                patient.put("name", rs.getString("name"));
                patient.put("condition",
                        rs.getString("chronic_issues") != null ? rs.getString("chronic_issues") : "N/A");
                patient.put("nic", rs.getString("nic"));
                patients.add(patient);
            }
        }
        return patients;
    }

    private String getGreeting(int hour) {
        if (hour < 12) {
            return "Good Morning";
        } else if (hour < 18) {
            return "Good Afternoon";
        } else {
            return "Good Evening";
        }
    }

    private String getPharmacistName(Connection conn, String pharmacistId) throws SQLException {
        if (pharmacistId == null)
            return null;
        String sql = "SELECT name FROM pharmacist WHERE id = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            try {
                stmt.setInt(1, Integer.parseInt(pharmacistId));
            } catch (NumberFormatException e) {
                return null;
            }
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getString("name");
                }
            }
        }
        return null;
    }
}
