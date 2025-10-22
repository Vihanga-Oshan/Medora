package com.example.base.controller.auth;

import com.example.base.dao.GuardianDAO;
import com.example.base.model.Guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/loginguardian")
public class LoginGuardianservlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Show the guardian login page
        req.getRequestDispatcher("/WEB-INF/views/auth/login-guardian.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String nic = request.getParameter("nic");
        String password = request.getParameter("password");

        Guardian guardian = GuardianDAO.validate(nic, password);

        if (guardian != null) {
            HttpSession session = request.getSession(true);
            session.setAttribute("guardian", guardian);
            response.sendRedirect(request.getContextPath() + "/guardian/dashboard");
        } else {
            request.setAttribute("error", "Invalid NIC or password.");
            request.getRequestDispatcher("/WEB-INF/views/auth/login-guardian.jsp")
                    .forward(request, response);
        }
    }
}
