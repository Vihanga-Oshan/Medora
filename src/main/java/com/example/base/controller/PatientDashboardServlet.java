package com.example.base.controller;

import com.example.base.db.dbconnection;
import com.example.base.dao.ScheduleDAO;
import com.example.base.model.MedicationSchedule;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.time.LocalDate;
import java.util.List;
import java.util.stream.Collectors;

@WebServlet("/patient/dashboard")
public class PatientDashboardServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        if (req.getSession(false) == null || req.getSession(false).getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        com.example.base.model.patient patient =
                (com.example.base.model.patient) req.getSession(false).getAttribute("patient");
        String patientNic = patient.getNic();

        // ✅ If a date is passed, show that date; otherwise, default to today
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
            List<MedicationSchedule> selectedDateMeds = scheduleDAO.getMedicationByDate(patientNic, selectedDate);

            List<MedicationSchedule> pendingTodaysMeds = todaysMeds.stream()
                    .filter(m -> "PENDING".equalsIgnoreCase(m.getStatus()))
                    .collect(Collectors.toList());
// Set attributes for both
            req.setAttribute("pendingMedications", pendingTodaysMeds); // used in today's card
            req.setAttribute("medications", selectedDateMeds); // used in view timetable
            req.setAttribute("selectedDate", selectedDate);
            // Calculate stats
            int total = meds.size();
            int taken = (int) meds.stream().filter(m -> "TAKEN".equalsIgnoreCase(m.getStatus())).count();
            int missed = (int) meds.stream().filter(m -> "MISSED".equalsIgnoreCase(m.getStatus())).count();
            int pending = total - taken - missed;

            // ✅ Set attributes for JSP
            req.setAttribute("patient", patient);
            req.setAttribute("selectedDate", selectedDate);
            req.setAttribute("medications", meds);
            req.setAttribute("totalCount", total);
            req.setAttribute("takenCount", taken);
            req.setAttribute("missedCount", missed);
            req.setAttribute("pendingCount", pending);

            req.getRequestDispatcher("/WEB-INF/views/patient/patient-dashboard.jsp").forward(req, resp);

        } catch (Exception e) {
            e.printStackTrace();
            resp.sendRedirect(req.getContextPath() + "/error.jsp");
        }
    }
}