package com.example.base.controller.patient;

import com.example.base.dao.ScheduleDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.MedicationSchedule;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.time.LocalDate;
import java.util.List;
import java.util.stream.Collectors;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/dashboard")
public class PatientDashboardServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PatientDashboardServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Rely on JwtAuthFilter for authentication
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // ✅ Handle date selection
        LocalDate today = LocalDate.now();
        LocalDate selectedDate = today;
        String dateParam = req.getParameter("date");
        if (dateParam != null && !dateParam.isEmpty()) {
            selectedDate = LocalDate.parse(dateParam);
        }

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO scheduleDAO = new ScheduleDAO(conn);

            List<MedicationSchedule> meds = scheduleDAO.getMedicationByDate(patientNic, selectedDate);
            List<MedicationSchedule> todaysMeds = scheduleDAO.getMedicationByDate(patientNic, today);

            // ✅ Separate by status
            List<MedicationSchedule> pendingTodaysMeds = todaysMeds.stream()
                    .filter(m -> "PENDING".equalsIgnoreCase(m.getStatus()))
                    .collect(Collectors.toList());

            int total = meds.size();
            int taken = (int) meds.stream().filter(m -> "TAKEN".equalsIgnoreCase(m.getStatus())).count();
            int missed = (int) meds.stream().filter(m -> "MISSED".equalsIgnoreCase(m.getStatus())).count();
            int pending = total - taken - missed;

            // ✅ Add data to request scope
            req.setAttribute("patientNic", patientNic);
            req.setAttribute("pendingMedications", pendingTodaysMeds);
            req.setAttribute("medications", meds);
            req.setAttribute("selectedDate", selectedDate);
            req.setAttribute("totalCount", total);
            req.setAttribute("takenCount", taken);
            req.setAttribute("missedCount", missed);
            req.setAttribute("pendingCount", pending);

            // ✅ Forward to JSP
            req.getRequestDispatcher("/WEB-INF/views/patient/patient-dashboard.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading patient dashboard for NIC: " + patientNic, e);
            resp.sendRedirect(req.getContextPath() + "/error.jsp");
        }
    }
}
