package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/add-pharmacist")
public class AdminAddPharmacistServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AdminAddPharmacistServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // JwtAuthFilter already validated JWT and role
        req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String idStr = req.getParameter("license");
        String name = req.getParameter("fullname");
        String email = req.getParameter("email");
        String password = req.getParameter("password");

        if (idStr == null || name == null || email == null || password == null ||
                idStr.isEmpty() || name.isEmpty() || email.isEmpty() || password.isEmpty()) {
            req.setAttribute("error", "All fields are required.");
            doGet(req, resp);
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            int id = Integer.parseInt(idStr);

            if (pharmacistDAO.idExists(id)) {
                req.setAttribute("error", "A pharmacist with this license number already exists.");
                doGet(req, resp);
                return;
            }

            if (pharmacistDAO.emailExists(email)) {
                req.setAttribute("error", "A pharmacist with this email already exists.");
                doGet(req, resp);
                return;
            }

            Pharmacist pharmacist = new Pharmacist();
            pharmacist.setId(id);
            pharmacist.setName(name);
            pharmacist.setEmail(email);
            pharmacist.setPassword(password);

            pharmacistDAO.insertPharmacist(pharmacist);
            LOGGER.info("✅ Pharmacist added successfully");
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error adding pharmacist", e);
            req.setAttribute("error", "An internal error occurred.");
            doGet(req, resp);
        }
    }
}
