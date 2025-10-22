package com.example.base.controller.auth;

import com.example.base.dao.AdminDAO;
import com.example.base.model.Admin;
import com.example.base.util.PasswordUtil;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/register")
public class AdminRegisterServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AdminRegisterServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // ✅ Always forward to the same JSP
        req.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String fullName = request.getParameter("fullName");
        String email = request.getParameter("email");
        String nic = request.getParameter("nic");
        String contact = request.getParameter("contact");
        String password = request.getParameter("password");
        String confirmPassword = request.getParameter("confirmPassword");

        // ✅ Validation: passwords match
        if (password == null || confirmPassword == null || !password.equals(confirmPassword)) {
            request.setAttribute("error", "Passwords do not match.");
            request.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(request, response);
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            Admin admin = new Admin();
            admin.setFullName(fullName);
            admin.setEmail(email);
            admin.setNic(nic);
            admin.setContactNumber(contact);
            admin.setPassword(PasswordUtil.hashPassword(password));

            AdminDAO dao = new AdminDAO(conn);
            boolean registered = dao.registerAdmin(admin);

            if (registered) {
                LOGGER.info("✅ New admin registered successfully: " + email);
                response.sendRedirect(request.getContextPath() + "/admin/login?registered=1");
            } else {
                request.setAttribute("error", "Registration failed. Please try again.");
                request.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(request, response);
            }

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Admin registration error", e);
            request.setAttribute("error", "An internal error occurred. Please try again later.");
            request.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(request, response);
        }
    }
}
