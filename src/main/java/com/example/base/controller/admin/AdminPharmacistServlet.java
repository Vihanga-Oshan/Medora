package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Admin;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;

@WebServlet("/admin/pharmacists")
public class AdminPharmacistServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("admin") == null) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            List<Pharmacist> pharmacists = pharmacistDAO.getAllPharmacists();
            req.setAttribute("pharmacists", pharmacists);
        } catch (Exception e) {
            throw new ServletException("Error loading pharmacist list", e);
        }

        Admin admin = (Admin) session.getAttribute("admin");
        req.setAttribute("admin", admin);

        req.getRequestDispatcher("/WEB-INF/views/admin/admin-pharmacists.jsp").forward(req, resp);
    }
}
