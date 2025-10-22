package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.model.Pharmacist;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLIntegrityConstraintViolationException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/addpharmacist")
public class AddPharmacistServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AddPharmacistServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified admin access
        String adminNic = (String) req.getAttribute("jwtSub");

        if (adminNic == null) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // ✅ Get form inputs
        String idStr = req.getParameter("license");
        String name = req.getParameter("fullname");
        String email = req.getParameter("email");
        String password = req.getParameter("password");

        if (idStr == null || name == null || email == null || password == null ||
                idStr.isEmpty() || name.isEmpty() || email.isEmpty() || password.isEmpty()) {
            req.setAttribute("error", "All fields are required.");
            req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            int id = Integer.parseInt(idStr);

            // ✅ Duplicate checks
            if (pharmacistDAO.idExists(id)) {
                req.setAttribute("error", "A pharmacist with this license number already exists.");
                req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
                return;
            }

            if (pharmacistDAO.emailExists(email)) {
                req.setAttribute("error", "A pharmacist with this email already exists.");
                req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
                return;
            }

            // ✅ Create & insert pharmacist
            Pharmacist pharmacist = new Pharmacist();
            pharmacist.setId(id);
            pharmacist.setName(name);
            pharmacist.setEmail(email);
            pharmacist.setPassword(password); // consider hashing in production

            pharmacistDAO.insertPharmacist(pharmacist);

            LOGGER.info("✅ Pharmacist added successfully by admin " + adminNic);

            // ✅ Redirect to pharmacist list page
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");

        } catch (SQLIntegrityConstraintViolationException e) {
            LOGGER.log(Level.WARNING, "Duplicate pharmacist entry", e);
            req.setAttribute("error", "Duplicate entry detected. Use a unique license or email.");
            req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error adding pharmacist", e);
            throw new ServletException("Error adding pharmacist", e);
        }
    }
}
