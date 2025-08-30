package com.example.base.controller;

import com.example.base.dao.GuardianDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLIntegrityConstraintViolationException;

@WebServlet("/register/guardian")
public class RegisterGuardianServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Show the form (JSP is under WEB-INF)
        req.getRequestDispatcher("/WEB-INF/views/auth/register-guardian.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String name    = trim(req.getParameter("g_name"));
        String nic     = trim(req.getParameter("nic"));
        String phone   = trim(req.getParameter("contact_number"));
        String email   = trim(req.getParameter("email"));
        String pw      = req.getParameter("password");
        String agree   = req.getParameter("agree"); // checkbox

        // Basic validation
        if (isEmpty(name) || isEmpty(nic) || isEmpty(phone) || isEmpty(pw) || isEmpty(agree)) {
            req.setAttribute("error", "Please fill all required fields and accept the privacy policy.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-guardian.jsp").forward(req, resp);
            return;
        }

        Guardian g = new Guardian();
        g.setName(name);
        g.setNic(nic);
        g.setContactNumber(phone);
        g.setEmail(email);
        g.setPassword(pw); // (store plain for now; add hashing later)

        try (Connection conn = dbconnection.getConnection()) {
            GuardianDAO dao = new GuardianDAO(conn);
            dao.insertGuardian(g);

            // Success: redirect to login controller (NOT to a JSP file)
            resp.sendRedirect(req.getContextPath() + "/login");
        } catch (SQLIntegrityConstraintViolationException dup) {
            // Likely duplicate NIC or email
            req.setAttribute("error", "This NIC or Email is already registered.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-guardian.jsp").forward(req, resp);
        } catch (Exception e) {
            e.printStackTrace();
            req.setAttribute("error", "Something went wrong. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-guardian.jsp").forward(req, resp);
        }
    }

    private static String trim(String s) { return s == null ? null : s.trim(); }
    private static boolean isEmpty(String s) { return s == null || s.trim().isEmpty(); }
}
