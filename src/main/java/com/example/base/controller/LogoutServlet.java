package com.example.base.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/logout")
public class LogoutServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false); // prevent new session creation
        if (session != null) {
            session.invalidate(); // destroy session
        }

        // Redirect to login page
        resp.sendRedirect(req.getContextPath() + "/login");
    }
}
