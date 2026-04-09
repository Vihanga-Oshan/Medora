package com.example.base.controller.admin;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/admin/settings")
public class AdminSettingsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already validated the JWT and admin role
        String adminNic = (String) req.getAttribute("jwtSub");

        // 🔒 Safety check in case token is missing or expired
        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // ✅ Pass admin NIC to JSP for personalization if needed
        req.setAttribute("adminNic", adminNic);

        // ✅ Forward to the settings JSP
        req.getRequestDispatcher("/WEB-INF/views/admin/admin-settings.jsp").forward(req, resp);
    }
}
