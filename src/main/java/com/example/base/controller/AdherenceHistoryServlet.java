package com.example.base.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.ArrayList;

@WebServlet("/patient/adherence-history")
public class AdherenceHistoryServlet extends HttpServlet {
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)   throws ServletException, IOException {
        // Optionally check that a patient is logged in
        if (req.getSession(false) == null || req.getSession(false).getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }
        req.getRequestDispatcher("/WEB-INF/views/patient/adherence-history.jsp").forward(req, resp);
    }
}
