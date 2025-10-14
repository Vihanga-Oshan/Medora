package com.example.base.controller;

import com.example.base.dao.PharmacistDAO;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/login/pharmacist")
public class PharmacistLoginServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Show success message if redirected from registration
        if ("1".equals(req.getParameter("registered"))) {
            req.setAttribute("message", "Registration successful! Please log in.");
        }
        req.getRequestDispatcher("/WEB-INF/views/auth/login-pharmacist.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String email = req.getParameter("email");
        String password = req.getParameter("password");

        Pharmacist pharmacist = PharmacistDAO.validate(email, password);

        if (pharmacist != null) {
            HttpSession session = req.getSession(true);
            session.setAttribute("pharmacist", pharmacist);
            resp.sendRedirect(req.getContextPath() + "/pharmacist/dashboard");
        } else {
            req.setAttribute("error", "Invalid email or password.");
            req.getRequestDispatcher("/WEB-INF/views/auth/login-pharmacist.jsp").forward(req, resp);
        }
    }
}