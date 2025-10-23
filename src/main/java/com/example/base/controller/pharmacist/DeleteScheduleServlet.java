package com.example.base.controller.pharmacist;

import com.example.base.dao.ScheduleDAO;
import com.example.base.db.dbconnection;
import com.mysql.cj.jdbc.JdbcConnection;

import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

@WebServlet("/pharmacist/delete-schedule")
public class DeleteScheduleServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws IOException {
        String idParam = request.getParameter("id");
        String nic = request.getParameter("nic");

        if (idParam == null || nic == null) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing schedule ID or NIC.");
            return;
        }

        int id = Integer.parseInt(idParam);

        try (Connection conn = dbconnection.getConnection()) {
            ScheduleDAO scheduleDAO = new ScheduleDAO(conn);
            boolean deleted = scheduleDAO.deleteScheduleById(id);

            if (deleted) {
                response.sendRedirect(request.getContextPath() + "/pharmacist/view-schedule?nic=" + nic + "&delete=success");
            } else {
                response.sendRedirect(request.getContextPath() + "/pharmacist/view-schedule?nic=" + nic + "&delete=failure");
            }
        } catch (SQLException e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Database error.");
        }
    }
}
