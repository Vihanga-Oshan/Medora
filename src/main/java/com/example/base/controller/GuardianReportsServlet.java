package com.example.base.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/reports")
public class GuardianReportsServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        if (req.getSession(false) == null || req.getSession(false).getAttribute("guardian") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }
        // You can attach attributes here in future for patient reports
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-reports.jsp").forward(req, resp);
    }
}
