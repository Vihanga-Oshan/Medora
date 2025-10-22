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

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("admin") == null) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/admin/admin-settings.jsp").forward(req, resp);
    }
}
