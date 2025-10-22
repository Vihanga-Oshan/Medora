package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/admin/pharmacists")
public class AdminPharmacistServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AdminPharmacistServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified admin access
        String adminNic = (String) req.getAttribute("jwtSub");

        // 🔒 Extra defense — prevent access if token missing or expired
        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            List<Pharmacist> pharmacists = pharmacistDAO.getAllPharmacists();

            req.setAttribute("pharmacists", pharmacists);
            req.setAttribute("adminNic", adminNic);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading pharmacist list", e);
            req.setAttribute("error", "Failed to load pharmacist list.");
        }

        // ✅ Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/admin/admin-pharmacists.jsp").forward(req, resp);
    }
}
