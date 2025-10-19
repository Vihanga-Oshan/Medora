package com.example.base.controller;

import com.example.base.dao.PharmacistDAO;
import com.example.base.model.Pharmacist;
import com.example.base.auth.JwtUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/login")
public class PharmacistLoginServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PharmacistLoginServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // forward to a pharmacist login JSP if you have one; fallback to patient login page
        req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Expect numeric pharmacistId and password
        String pharmacistIdParam = req.getParameter("pharmacistId");
        String password = req.getParameter("password");

        Integer pharmacistId = null;
        if (pharmacistIdParam != null) {
            pharmacistIdParam = pharmacistIdParam.trim();
            try {
                pharmacistId = Integer.parseInt(pharmacistIdParam);
            } catch (NumberFormatException e) {
                req.setAttribute("error", "Invalid Pharmacist ID.");
                req.setAttribute("pharmacistId", pharmacistIdParam);
                req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
                return;
            }
        }

        Pharmacist p = null;
        if (pharmacistId != null) {
            p = PharmacistDAO.validateById(pharmacistId, password);
        }

        if (p != null) {
            HttpSession session = req.getSession(true);
            session.setAttribute("pharmacist", p);
            session.setAttribute("role", "pharmacist");

            String secret = req.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = req.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}
            try {
                String token = JwtUtil.createToken(secret != null ? secret : "change_this_secret_before_production_2025", String.valueOf(p.getId()), "pharmacist", expiry);
                Cookie jwt = new Cookie("JWT", token);
                jwt.setHttpOnly(true);
                jwt.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
                if (req.isSecure()) jwt.setSecure(true);
                resp.addCookie(jwt);
            } catch (Exception e) {
                // token creation failed; proceed with session only
                LOGGER.log(Level.SEVERE, "Failed to create JWT for pharmacist", e);
            }

            resp.sendRedirect(req.getContextPath() + "/pharmacist/dashboard");
        } else {
            req.setAttribute("error", "Invalid Pharmacist ID or password.");
            // preserve entered pharmacistId
            req.setAttribute("pharmacistId", pharmacistIdParam);
            req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
        }
    }
}
