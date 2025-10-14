package com.example.base.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/dashboard")
public class guardiandashboardservlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("guardian") == null) {
            resp.sendRedirect(req.getContextPath() + "/loginguardian");
            return;
        }

        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-dashboard.jsp").forward(req, resp);
    }
}
