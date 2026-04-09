package com.example.base.controller.patient;

import com.example.base.dao.NotificationDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Notification;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.List;

@WebServlet({ "/patient/notifications", "/patient/notifications/action" })
public class NotificationServlet extends HttpServlet {

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

        try (Connection conn = dbconnection.getConnection()) {
            NotificationDAO dao = new NotificationDAO(conn);
            List<Notification> notifications = dao.getNotificationsByPatient(patientNic);
            req.setAttribute("notifications", notifications);
        } catch (SQLException e) {
            e.printStackTrace();
            req.setAttribute("error", "Failed to load notifications.");
        }

        // ✅ Forward to JSP (JWT is already validated)
        req.getRequestDispatcher("/WEB-INF/views/patient/notifications.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String action = req.getParameter("action");
        String idParam = req.getParameter("id");
        String patientNic = (String) req.getAttribute("jwtSub"); // From JWT Filter

        if (patientNic == null) {
            resp.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            NotificationDAO dao = new NotificationDAO(conn);

            if ("delete".equals(action) && idParam != null) {
                dao.deleteNotification(Integer.parseInt(idParam));
            } else if ("clearAll".equals(action)) {
                dao.deleteAllNotifications(patientNic);
            } else if ("markRead".equals(action) && idParam != null) {
                dao.markAsRead(Integer.parseInt(idParam));
            }

            resp.setStatus(HttpServletResponse.SC_OK);
        } catch (Exception e) {
            e.printStackTrace();
            resp.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
}
