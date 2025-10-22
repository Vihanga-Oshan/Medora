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

@WebServlet("/patient/medication-timetable")
public class PatientMedicationTimetableServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Get NIC from session or query param
        String patientNic = (String) req.getSession().getAttribute("patientNic");
        if (patientNic == null) {
            patientNic = req.getParameter("nic");
        }

        if (patientNic == null || patientNic.isEmpty()) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing patient NIC");
            return;
        }

        // ✅ Date selection
        String dateParam = req.getParameter("date");
        LocalDate selectedDate = (dateParam != null && !dateParam.isEmpty())
                ? LocalDate.parse(dateParam)
                : LocalDate.now();

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO scheduleDAO = new ScheduleDAO(conn);
            List<MedicationSchedule> meds = scheduleDAO.getMedicationByDate(patientNic, selectedDate);

            req.setAttribute("selectedDate", selectedDate);
            req.setAttribute("medications", meds);
            req.getRequestDispatcher("/WEB-INF/views/patient/medication-timetable.jsp").forward(req, resp);

        } catch (Exception e) {
            e.printStackTrace();
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to load medication schedule");
        }
    }
}