package com.example.base.controller.admin;

import com.example.base.dao.PharmacistDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/admin/edit-pharmacist")
public class EditPharmacistServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String idParam = req.getParameter("id");
        if (idParam == null || idParam.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(idParam);
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            Pharmacist pharmacist = pharmacistDAO.getPharmacistById(id);

            if (pharmacist == null) {
                resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
                return;
            }

            req.setAttribute("pharmacist", pharmacist);
            req.getRequestDispatcher("/WEB-INF/views/admin/edit-pharmacist.jsp").forward(req, resp);
        } catch (Exception e) {
            throw new ServletException("Error loading pharmacist for edit", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(req.getParameter("id"));
            String license = req.getParameter("licenseNumber");
            String name = req.getParameter("fullName");
            String password = req.getParameter("password");
            String confirmPassword = req.getParameter("confirmPassword");

            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            Pharmacist pharmacist = pharmacistDAO.getPharmacistById(id);

            if (pharmacist == null) {
                resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
                return;
            }

            pharmacist.setId(Integer.parseInt(license)); // update license
            pharmacist.setName(name);
            pharmacist.setEmail(pharmacist.getEmail());


            if (password != null && !password.isEmpty() && password.equals(confirmPassword)) {
                pharmacist.setPassword(password);
            } else {
                // keep the old password if not changed
                pharmacist.setPassword(pharmacist.getPassword());
            }

            pharmacistDAO.updatePharmacist(pharmacist);

            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");

        } catch (Exception e) {
            throw new ServletException("Error updating pharmacist", e);
        }
    }
}
