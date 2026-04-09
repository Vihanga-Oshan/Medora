package com.example.base.controller.auth;

import com.example.base.dao.PharmacistDAO;
import com.example.base.model.Pharmacist;
import com.example.base.auth.JwtUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/login")
public class PharmacistLoginServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PharmacistLoginServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String pharmacistIdParam = req.getParameter("pharmacistId");
        String password = req.getParameter("password");

        Integer pharmacistId = null;
        try {
            pharmacistId = Integer.parseInt(pharmacistIdParam.trim());
        } catch (Exception e) {
            req.setAttribute("error", "Invalid Pharmacist ID.");
            req.setAttribute("pharmacistId", pharmacistIdParam);
            req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
            return;
        }

        Pharmacist pharmacist = PharmacistDAO.validateById(pharmacistId, password);

        if (pharmacist == null) {
            req.setAttribute("error", "Invalid Pharmacist ID or password.");
            req.setAttribute("pharmacistId", pharmacistIdParam);
            req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
            return;
        }

        // ✅ Generate JWT
        String secret = req.getServletContext().getInitParameter("jwt.secret");
        String expiryStr = req.getServletContext().getInitParameter("jwt.expirySeconds");
        long expiry = 3600L;
        try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}

        try {
            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    String.valueOf(pharmacist.getId()),
                    "pharmacist",
                    expiry
            );

            // ✅ Correct cookie name for pharmacist
            Cookie jwt = new Cookie("JWT_PHARMACIST", token);
            jwt.setHttpOnly(true);
            jwt.setPath("/"); // global visibility
            jwt.setMaxAge((int) expiry);
            if (req.isSecure()) jwt.setSecure(true);
            resp.addCookie(jwt);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to create JWT for pharmacist", e);
        }

        // ✅ Redirect to pharmacist dashboard
        resp.sendRedirect(req.getContextPath() + "/pharmacist/dashboard");
    }
}
