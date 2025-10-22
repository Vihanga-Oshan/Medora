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

@WebServlet("/admin/register")
public class AdminRegisterServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
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

        if (!password.equals(confirmPassword)) {
            request.setAttribute("error", "Passwords do not match.");
            request.getRequestDispatcher("/WEB-INF/views/admin/register-admin.jsp").forward(request, response);
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
                response.sendRedirect(request.getContextPath() + "/admin/login?success=1");
            } else {
                request.setAttribute("error", "Registration failed.");
                request.getRequestDispatcher("/WEB-INF/views/admin/register-admin.jsp").forward(request, response);
            }

        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("error", "An error occurred.");
            request.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(request, response);
        }
    }
}
