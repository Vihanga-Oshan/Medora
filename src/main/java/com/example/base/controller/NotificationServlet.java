package com.example.base.controller;

import javax.management.Notification;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.List;

@WebServlet("/patient/notifications")
public class NotificationServlet extends HttpServlet {
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)   throws ServletException, IOException {
        // Optionally check that a patient is logged in
        if (req.getSession(false) == null || req.getSession(false).getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }
        req.getRequestDispatcher("/WEB-INF/views/patient/notifications.jsp").forward(req, resp);
    }
}
