package com.example.base.controller.pharmacist;

import com.example.base.dao.ScheduleDAO;
import com.example.base.dao.patientDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.MedicationSchedule;
import com.example.base.model.patient;

import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.time.LocalDate;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/view-schedule")
public class ViewScheduleServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(ViewScheduleServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ✅ Auth handled by JwtAuthFilter
        String role = (String) request.getAttribute("jwtRole");
        String pharmacistId = (String) request.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        // ✅ Get patient NIC and date from request
        String nic = request.getParameter("nic");
        if (nic == null || nic.isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing patient NIC in request");
            return;
        }

        String dateStr = request.getParameter("date");
        LocalDate selectedDate = (dateStr != null && !dateStr.isEmpty())
                ? LocalDate.parse(dateStr)
                : LocalDate.now();

        try (Connection conn = dbconnection.getConnection()) {

            // ✅ Fetch patient details
            patientDAO pdao = new patientDAO(conn);
            patient p = pdao.getPatientByNIC(nic);
            if (p == null) {
                response.sendError(HttpServletResponse.SC_NOT_FOUND, "Patient with NIC '" + nic + "' not found");
                return;
            }

            // ✅ Fetch medication schedule for date
            ScheduleDAO sdao = new ScheduleDAO(conn);
            List<MedicationSchedule> scheduleList = sdao.getMedicationByDate(nic, selectedDate);

            // ✅ Set request attributes for JSP
            request.setAttribute("pharmacistId", pharmacistId);
            request.setAttribute("patient", p);
            request.setAttribute("scheduleList", scheduleList);
            request.setAttribute("selectedDate", selectedDate);

            // ✅ Forward to JSP page
            RequestDispatcher dispatcher =
                    request.getRequestDispatcher("/WEB-INF/views/pharmacist/view-schedule.jsp");
            dispatcher.forward(request, response);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading schedule for pharmacist", e);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to load schedule");
        }
    }
}
