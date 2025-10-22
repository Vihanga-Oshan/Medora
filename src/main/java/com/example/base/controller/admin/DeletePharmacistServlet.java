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
                req.setAttribute("error", "Unable to delete pharmacist. It may not exist.");
                req.getRequestDispatcher("/WEB-INF/views/admin/admin-pharmacists.jsp").forward(req, resp);
                return;
            }

            LOGGER.info("Pharmacist successfully deleted by admin: " + adminNic);

        } catch (NumberFormatException e) {
            LOGGER.log(Level.WARNING, "Invalid pharmacist ID format: " + idParam, e);
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?error=invalid_id");
            return;
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error deleting pharmacist ID: " + idParam, e);
            throw new ServletException("Failed to delete pharmacist", e);
        }

        // ✅ Redirect back to pharmacist list after deletion
        resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?deleted=1");
    }
}
