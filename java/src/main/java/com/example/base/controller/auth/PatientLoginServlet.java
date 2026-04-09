package com.example.base.controller.auth;

import com.example.base.dao.patientDAO;
import com.example.base.model.patient;
import com.example.base.auth.JwtUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/login")
public class PatientLoginServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(PatientLoginServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String nic = request.getParameter("nic");
        String password = request.getParameter("password");
        LOGGER.info("Patient login attempt for NIC=" + nic);

        try {
            patient p = patientDAO.validate(nic, password);

            if (p == null) {
                request.setAttribute("error", "Invalid NIC or password.");
                request.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(request, response);
                return;
            }

            // ✅ Create JWT
            String secret = request.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = request.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}

            try {
                String token = JwtUtil.createToken(
                        secret != null ? secret : "change_this_secret_before_production_2025",
                        p.getNic(),
                        "patient",
                        expiry
                );

                // ✅ Store under role-specific cookie
                Cookie jwt = new Cookie("JWT_PATIENT", token);
                jwt.setHttpOnly(true);
                jwt.setPath(request.getContextPath().isEmpty() ? "/" : request.getContextPath());
                jwt.setMaxAge((int) expiry);
                if (request.isSecure()) jwt.setSecure(true);
                response.addCookie(jwt);

                LOGGER.info("✅ JWT_PATIENT cookie set successfully for NIC=" + nic);

            } catch (Exception e) {
                LOGGER.log(Level.SEVERE, "Failed to create JWT for patient NIC=" + nic, e);
                request.setAttribute("error", "Error creating token. Please try again.");
                request.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(request, response);
                return;
            }

            // ✅ Optional: Keep session for compatibility (not required for JWT)
            HttpSession session = request.getSession(true);
            session.setAttribute("patient", p);

            // ✅ Redirect to dashboard
            response.sendRedirect(request.getContextPath() + "/patient/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Unexpected error during patient login", e);
            response.sendRedirect(request.getContextPath() + "/error.jsp");
        }
    }
}
