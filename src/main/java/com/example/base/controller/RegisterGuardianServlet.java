package com.example.base.controller;

import com.example.base.dao.GuardianDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Guardian;
import com.example.base.db.dbconnection;

import javax.servlet.*;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

public class RegisterGuardianServlet extends HttpServlet {
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        Guardian guardian = new Guardian();
        guardian.setNic(req.getParameter("nic"));
        guardian.setName(req.getParameter("g_name"));
        guardian.setContactNumber(req.getParameter("contact_number"));
        guardian.setEmail(req.getParameter("email"));
        guardian.setPassword(req.getParameter("password"));

        try (Connection conn = dbconnection.getConnection()) {
            GuardianDAO dao = new GuardianDAO(conn);
            dao.insertGuardian(guardian);
            resp.sendRedirect("register/success.jsp");
        } catch (Exception e) {
            e.printStackTrace();
            resp.sendRedirect("register/fail.jsp");
        }
    }
}
