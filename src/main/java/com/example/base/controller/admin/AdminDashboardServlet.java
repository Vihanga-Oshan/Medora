package com.example.base.controller.admin;

import com.example.base.model.Admin;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;


@WebServlet("/admin/dashboard")
public class AdminDashboardServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // By this point, JwtAuthFilter has already validated the session
        Admin admin = (Admin) req.getSession().getAttribute("admin");
        req.setAttribute("admin", admin);

        req.getRequestDispatcher("/WEB-INF/views/admin/admin-dashboard.jsp").forward(req, resp);
    }
}
