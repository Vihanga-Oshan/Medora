package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/dashboard")
public class GuardianDashboardServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified guardian authentication
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: prevent access without token or role
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // Optionally attach guardian info to request
        req.setAttribute("guardianNic", guardianNic);

        // ✅ Forward to dashboard JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-dashboard.jsp").forward(req, resp);
    }
}
