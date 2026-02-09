package com.example.base.controller.pharmacist;

import com.example.base.dao.CategoryDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/add-category")
public class AddCategoryServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AddCategoryServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String name = req.getParameter("name");

        if (name == null || name.trim().isEmpty()) {
            resp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            resp.getWriter().write("{\"error\": \"Category name is required\"}");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = CategoryDAO.add(conn, name.trim());
            if (id != -1) {
                resp.setContentType("application/json");
                resp.getWriter().write("{\"success\": true, \"id\": " + id + ", \"name\": \"" + name + "\"}");
            } else {
                resp.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
                resp.getWriter().write("{\"error\": \"Failed to add category\"}");
            }
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Error adding category", e);
            resp.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            resp.getWriter().write("{\"error\": \"Database error: " + e.getMessage() + "\"}");
        }
    }
}
