package com.example.base.controller.guardian;

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

        // ✅ JwtAuthFilter already validates guardian JWT.
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: handle direct URL access without valid token
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // ✅ Simulated alert data (replace later with DB results)
        List<Alert> alerts = new ArrayList<>();
        alerts.add(new Alert("Robert Chen", "Metformin", "HIGH", "⚠️", "2 hours ago", true, true));
        alerts.add(new Alert("Margaret Wilson", "Lisinopril", "MEDIUM", "🔔", "5 hours ago", false, true));
        alerts.add(new Alert("Eleanor Rodriguez", "Atorvastatin", "LOW", "📣", "Yesterday", false, false));

        // ✅ Attach guardian info + alerts to request scope
        req.setAttribute("guardianNic", guardianNic);
        req.setAttribute("alerts", alerts);

        // ✅ Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-alerts.jsp").forward(req, resp);
    }
}
