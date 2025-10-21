package com.example.base.controller;

import com.example.base.model.Alert;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.*;

@WebServlet("/guardian/alerts")
public class GuardianAlertsServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        List<Alert> alerts = new ArrayList<>();

        alerts.add(new Alert("Robert Chen", "Metformin", "HIGH", "⚠️", "2 hours ago", true, true));
        alerts.add(new Alert("Margaret Wilson", "Lisinopril", "MEDIUM", "🔔", "5 hours ago", false, true));
        alerts.add(new Alert("Eleanor Rodriguez", "Atorvastatin", "LOW", "📣", "Yesterday", false, false));

        req.setAttribute("alerts", alerts); // ✅ lowercase variable

        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-alerts.jsp").forward(req, resp);
    }
}
