package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/pharmacists/delete")
public class DeletePharmacistServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(DeletePharmacistServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified admin authentication
        String adminNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: ensure only logged-in admins can delete
        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // ✅ Validate parameter
        String idParam = req.getParameter("id");
        if (idParam == null || idParam.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(idParam);
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            boolean deleted = pharmacistDAO.deletePharmacist(id);

            if (!deleted) {
                LOGGER.warning("Pharmacist delete failed for ID: " + id);
                resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?error=not_found");
                return;
            }

            LOGGER.info("Pharmacist successfully deleted by admin: " + adminNic);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error deleting pharmacist ID: " + idParam, e);
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?error=delete_failed");
            return;
        }

        resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?deleted=1");

    }
}
