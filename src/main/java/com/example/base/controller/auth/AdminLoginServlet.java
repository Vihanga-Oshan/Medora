package com.example.base.controller.auth;

import com.example.base.dao.AdminDAO;
import com.example.base.model.Admin;
import com.example.base.auth.JwtUtil;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/login")
public class AdminLoginServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AdminLoginServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String email = request.getParameter("email");
        String password = request.getParameter("password");

        LOGGER.info("Admin login attempt for email=" + email);

        try (Connection conn = dbconnection.getConnection()) {
            AdminDAO dao = new AdminDAO(conn);
            Admin admin = dao.validate(email, password);

            if (admin == null) {
                request.setAttribute("error", "Invalid email or password.");
                request.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(request, response);
                return;
            }

            // ✅ Generate JWT for admin
            String secret = request.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = request.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}

            try {
                String token = JwtUtil.createToken(
                        secret != null ? secret : "change_this_secret_before_production_2025",
                        admin.getNic() != null ? admin.getNic() : String.valueOf(admin.getId()), // sub = unique ID
                        "admin", // role claim
                        expiry
                );

                Cookie jwt = new Cookie("JWT_ADMIN", token);
                jwt.setHttpOnly(true);
                jwt.setPath(request.getContextPath().isEmpty() ? "/" : request.getContextPath());
                jwt.setMaxAge((int) expiry);
                if (request.isSecure()) jwt.setSecure(true);
                response.addCookie(jwt);

                LOGGER.info("✅ Admin JWT created successfully for " + email);

            } catch (Exception e) {
                LOGGER.log(Level.SEVERE, "Failed to create JWT for admin " + email, e);
                request.setAttribute("error", "Token creation failed.");
                request.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(request, response);
                return;
            }

            // ✅ Redirect to admin dashboard
            response.sendRedirect(request.getContextPath() + "/admin/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during admin login", e);
            response.sendRedirect(request.getContextPath() + "/error.jsp");
        }
    }
}
