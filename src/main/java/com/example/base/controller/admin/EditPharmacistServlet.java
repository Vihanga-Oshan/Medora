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

@WebServlet("/admin/edit-pharmacist")
public class EditPharmacistServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(EditPharmacistServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already verified admin access
        String adminNic = (String) req.getAttribute("jwtSub");

        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

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

            req.setAttribute("adminNic", adminNic);
            req.setAttribute("pharmacist", pharmacist);
            req.getRequestDispatcher("/WEB-INF/views/admin/edit-pharmacist.jsp").forward(req, resp);

        } catch (NumberFormatException e) {
            LOGGER.log(Level.WARNING, "Invalid pharmacist ID format: " + idParam, e);
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?error=invalid_id");
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading pharmacist for edit", e);
            throw new ServletException("Error loading pharmacist for edit", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String adminNic = (String) req.getAttribute("jwtSub");

        if (adminNic == null || adminNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

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

            // ✅ Update editable fields
            pharmacist.setId(Integer.parseInt(license));
            pharmacist.setName(name);

            // ✅ Password change (only if confirmed)
            if (password != null && !password.isEmpty()) {
                if (!password.equals(confirmPassword)) {
                    req.setAttribute("error", "Passwords do not match.");
                    req.setAttribute("pharmacist", pharmacist);
                    req.getRequestDispatcher("/WEB-INF/views/admin/edit-pharmacist.jsp").forward(req, resp);
                    return;
                }
                pharmacist.setPassword(password);
            }

            pharmacistDAO.updatePharmacist(pharmacist);
            LOGGER.info("Admin " + adminNic + " updated pharmacist ID " + id);

            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?updated=1");

        } catch (NumberFormatException e) {
            LOGGER.log(Level.WARNING, "Invalid input while updating pharmacist", e);
            resp.sendRedirect(req.getContextPath() + "/admin/pharmacists?error=invalid_input");
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error updating pharmacist", e);
            throw new ServletException("Error updating pharmacist", e);
        }
    }
}
