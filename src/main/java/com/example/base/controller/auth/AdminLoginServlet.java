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

@WebServlet("/admin/login")
public class AdminLoginServlet extends HttpServlet {

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
        System.out.println("AdminLoginServlet: doPost entered with email=" + email);
        System.out.println("AdminLoginServlet: password=" + password);

        try (Connection conn = dbconnection.getConnection()) {
            AdminDAO dao = new AdminDAO(conn);
            Admin admin = dao.validate(email, password);

            if (admin != null) {
                HttpSession session = request.getSession(true);
                session.setAttribute("admin", admin);

                // JWT creation
                String secret = request.getServletContext().getInitParameter("jwt.secret");
                String expiryStr = request.getServletContext().getInitParameter("jwt.expirySeconds");
                long expiry = 3600L;
                try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}

                String token = JwtUtil.createToken(secret != null ? secret : "change_this_secret_before_production_2025",
                        String.valueOf(admin.getId()), "admin", expiry);
                Cookie jwt = new Cookie("JWT", token);
                jwt.setHttpOnly(true);
                jwt.setPath(request.getContextPath().isEmpty() ? "/" : request.getContextPath());
                if (request.isSecure()) jwt.setSecure(true);
                response.addCookie(jwt);

                response.sendRedirect(request.getContextPath() + "/admin/dashboard");
            } else {
                System.out.println("AdminLoginServlet: authentication failed");
                request.setAttribute("error", "Invalid credentials.");
                request.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(request, response);
            }

        } catch (Exception e) {
            e.printStackTrace();
            response.sendRedirect(request.getContextPath() + "/error.jsp");
        }
    }
}
