package com.example.base.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

@WebServlet("/logout")
public class LogoutServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);
        String role = null;
        if (session != null) {
            role = (String) session.getAttribute("role");
            session.invalidate();
        }

        // Remove JWT cookie
        Cookie remove = new Cookie("JWT", "");
        remove.setPath("/");
        remove.setHttpOnly(true);
        remove.setMaxAge(0);
        if (req.isSecure()) remove.setSecure(true);
        resp.addCookie(remove);

        // Redirect by role
        String redirectTo = req.getContextPath() + "/login";
        if ("pharmacist".equalsIgnoreCase(role)) {
            redirectTo = req.getContextPath() + "/pharmacist/login";
        } else if ("patient".equalsIgnoreCase(role)) {
            redirectTo = req.getContextPath() + "/patient/login";
        }

        resp.sendRedirect(redirectTo);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        doGet(req, resp);
    }
}


