package com.example.base.controller.patient;

import com.example.base.dao.ScheduleDAO;
import com.example.base.model.MedicationSchedule;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.time.LocalDate;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/medication-timetable")
public class PatientMedicationTimetableServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PatientMedicationTimetableServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Use JWT attributes injected by JwtAuthFilter
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // ✅ Handle selected date (default = today)
        String dateParam = req.getParameter("date");
        LocalDate selectedDate = (dateParam != null && !dateParam.isEmpty())
                ? LocalDate.parse(dateParam)
                : LocalDate.now();

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO scheduleDAO = new ScheduleDAO(conn);
            List<MedicationSchedule> meds = scheduleDAO.getMedicationByDate(patientNic, selectedDate);

            // ✅ Add attributes for JSP
            req.setAttribute("selectedDate", selectedDate);
            req.setAttribute("medications", meds);
            req.setAttribute("patientNic", patientNic);

            req.getRequestDispatcher("/WEB-INF/views/patient/medication-timetable.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load medication timetable for patient NIC: " + patientNic, e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to load medication schedule");
        }
    }
}
