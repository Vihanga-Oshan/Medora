package com.example.base.controller.admin;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/admin/dashboard")
public class AdminDashboardServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already validated JWT and role
        String adminNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: handle missing or invalid tokens
        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // ✅ Pass admin NIC to JSP for personalized display
        req.setAttribute("adminNic", adminNic);

        // ✅ Forward to admin dashboard page
        req.getRequestDispatcher("/WEB-INF/views/admin/admin-dashboard.jsp").forward(req, resp);
    }
}
