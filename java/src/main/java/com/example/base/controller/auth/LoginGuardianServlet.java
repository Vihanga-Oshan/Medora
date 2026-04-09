package com.example.base.controller.auth;

import com.example.base.dao.GuardianDAO;
import com.example.base.model.Guardian;
import com.example.base.auth.JwtUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/guardian/login")
public class LoginGuardianServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(LoginGuardianServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/WEB-INF/views/auth/login-guardian.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String nic = request.getParameter("nic");
        String password = request.getParameter("password");
        LOGGER.info("Guardian login attempt for NIC=" + nic);

        try {
            Guardian guardian = GuardianDAO.validate(nic, password);

            if (guardian == null) {
                request.setAttribute("error", "Invalid NIC or password.");
                request.getRequestDispatcher("/WEB-INF/views/auth/login-guardian.jsp")
                        .forward(request, response);
                return;
            }

            // ✅ Generate JWT for guardian
            String secret = request.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = request.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}

            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    guardian.getNic(),   // sub = guardian NIC
                    "guardian",          // role = guardian
                    expiry
            );

            // ✅ Store under role-specific cookie
            Cookie jwt = new Cookie("JWT_GUARDIAN", token);
            jwt.setHttpOnly(true);
            jwt.setPath(request.getContextPath().isEmpty() ? "/" : request.getContextPath());
            jwt.setMaxAge((int) expiry);
            if (request.isSecure()) jwt.setSecure(true);
            response.addCookie(jwt);

            LOGGER.info("✅ Guardian JWT created successfully for NIC=" + nic);

            // ✅ Redirect to guardian dashboard
            response.sendRedirect(request.getContextPath() + "/guardian/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Guardian login error", e);
            request.setAttribute("error", "Error processing login. Please try again.");
            request.getRequestDispatcher("/WEB-INF/views/auth/login-guardian.jsp")
                    .forward(request, response);
        }
    }
}
