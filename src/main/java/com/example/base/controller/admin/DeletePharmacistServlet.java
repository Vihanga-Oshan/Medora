package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/admin/pharmacists/delete")
public class DeletePharmacistServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String idParam = req.getParameter("id");

        if (idParam != null) {
            int id = Integer.parseInt(idParam);

            try (Connection conn = dbconnection.getConnection()) {
                PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
                pharmacistDAO.deletePharmacist(id); // this method should delete by ID
            } catch (Exception e) {
                throw new ServletException("Failed to delete pharmacist", e);
            }
        }

        resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
    }
}
