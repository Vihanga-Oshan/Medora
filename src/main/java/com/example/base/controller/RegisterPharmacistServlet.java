package com.example.base.controller;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLIntegrityConstraintViolationException;

@WebServlet("/register/pharmacist")
public class RegisterPharmacistServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String name = trim(req.getParameter("name"));
        String email = trim(req.getParameter("email"));
        String password = req.getParameter("password");
        String confirmPassword = req.getParameter("confirmPassword");
        String pharmacistIdParam = trim(req.getParameter("pharmacistId"));

        // Pharmacist ID is required (must be numeric positive)
        if (pharmacistIdParam == null || pharmacistIdParam.isEmpty()) {
            req.setAttribute("error", "Pharmacist ID is required.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
            return;
        }

        Integer explicitId = null;
        // only digits allowed
        if (!pharmacistIdParam.matches("\\d+")) {
            req.setAttribute("error", "Pharmacist ID must be numeric.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
            return;
        }
        try {
            explicitId = Integer.parseInt(pharmacistIdParam);
            if (explicitId <= 0) {
                req.setAttribute("error", "Pharmacist ID must be a positive number.");
                req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
                return;
            }
        } catch (NumberFormatException e) {
            req.setAttribute("error", "Invalid Pharmacist ID.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
            return;
        }

        // Validation
        if (isEmpty(name) || isEmpty(email) || isEmpty(password) || isEmpty(confirmPassword)) {
            req.setAttribute("error", "All fields are required.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
            return;
        }

        if (!password.equals(confirmPassword)) {
            req.setAttribute("error", "Passwords do not match!");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
            return;
        }

        Pharmacist p = new Pharmacist();
        if (explicitId != null) p.setId(explicitId);
        p.setName(name);
        p.setEmail(email);
        p.setPassword(password); // Will hash later

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO dao = new PharmacistDAO(conn);
            dao.insertPharmacist(p);

            resp.sendRedirect(req.getContextPath() + "/login/pharmacist?registered=1");
        } catch (SQLIntegrityConstraintViolationException e) {
            // Could be duplicate email or duplicate id
            String msg = e.getMessage();
            if (msg != null && msg.toLowerCase().contains("email")) {
                req.setAttribute("error", "Email already registered.");
            } else if (msg != null && msg.toLowerCase().contains("id")) {
                req.setAttribute("error", "Pharmacist ID is already in use.");
            } else {
                req.setAttribute("error", "Email or Pharmacist ID already registered.");
            }
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
        } catch (Exception e) {
            e.printStackTrace();
            req.setAttribute("error", "Registration failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-pharmacist.jsp").forward(req, resp);
        }
    }

    private static String trim(String s) { return s == null ? null : s.trim(); }
    private static boolean isEmpty(String s) { return s == null || s.trim().isEmpty(); }
}