package com.example.base.controller;

import com.example.base.dao.GuardianDAO;
import com.example.base.model.Guardian;

import javax.servlet.*;
import javax.servlet.http.*;
import java.io.IOException;

public class LoginGuardianservlet extends HttpServlet {
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String nic = request.getParameter("nic");
        String password = request.getParameter("password");

        Guardian guardian = GuardianDAO.validate(nic, password);

        if (guardian != null) {
            HttpSession session = request.getSession();
            session.setAttribute("guardian", guardian);
            response.sendRedirect("guardian-dashboard.jsp");
        } else {
            request.setAttribute("error", "Invalid NIC or password.");
            request.getRequestDispatcher("login-guardian.jsp").forward(request, response);
        }
    }
}
