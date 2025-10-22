// AddPharmacistServlet.java
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

@WebServlet("/admin/addpharmacist")
public class AddPharmacistServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String id = req.getParameter("license");
        String name = req.getParameter("fullname");
        String email = req.getParameter("email");
        String password = req.getParameter("password");

        try (Connection conn = dbconnection.getConnection()) {
            PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
            if (pharmacistDAO.idExists(Integer.parseInt(id))) {
                req.setAttribute("error", "A pharmacist with this license number already exists.");
                req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
                return;
            }

            if (pharmacistDAO.emailExists(email)) {
                req.setAttribute("error", "A pharmacist with this email already exists.");
                req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
                return;
            }
            Pharmacist pharmacist = new Pharmacist();
            pharmacist.setId(Integer.parseInt(id));
            pharmacist.setName(name);
            pharmacist.setEmail(email);
            pharmacist.setPassword(password);

            pharmacistDAO.insertPharmacist(pharmacist);
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists");
        } catch (SQLIntegrityConstraintViolationException e) {
            req.setAttribute("error", "Duplicate entry detected. Please use a unique license or email.");
            req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
        } catch (Exception e) {
            throw new ServletException("Error adding pharmacist", e);
        }
    }

}
