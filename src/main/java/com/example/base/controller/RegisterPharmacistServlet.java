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
        p.setName(name);
        p.setEmail(email);
        p.setPassword(password); // Will hash later

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO dao = new PharmacistDAO(conn);
            dao.insertPharmacist(p);

            resp.sendRedirect(req.getContextPath() + "/login/pharmacist?registered=1");
        } catch (SQLIntegrityConstraintViolationException e) {
            req.setAttribute("error", "Email already registered.");
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