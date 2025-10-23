package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/update-medicine")
public class UpdateMedicineServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(UpdateMedicineServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String idStr = req.getParameter("id");
        String name = req.getParameter("name");
        String genericName = req.getParameter("generic_name");
        String category = req.getParameter("category");
        String description = req.getParameter("description");
        String dosageForm = req.getParameter("dosage_form");
        String strength = req.getParameter("strength");
        String qtyStr = req.getParameter("quantity_in_stock");
        String manufacturer = req.getParameter("manufacturer");
        String expiryDateStr = req.getParameter("expiry_date");
        int id = Integer.parseInt(idStr);

        try (Connection conn = dbconnection.getConnection()) {
            Medicine m = new Medicine();
            m.setId(id);
            m.setName(name);
            m.setGenericName(genericName);
            m.setCategory(category);
            m.setDescription(description);
            m.setDosageForm(dosageForm);
            m.setStrength(strength);
            m.setQuantityInStock(Integer.parseInt(qtyStr));
            m.setManufacturer(manufacturer);

            // ✅ Only set expiry date if provided
            if (expiryDateStr != null && !expiryDateStr.trim().isEmpty()) {
                m.setExpiryDate(java.sql.Date.valueOf(expiryDateStr));
            } else {
                // keep the old date from DB
                java.sql.Date currentExpiry = MedicineDAO.getExpiryDateById(conn, id);
                m.setExpiryDate(currentExpiry);
            }

            MedicineDAO.update(conn, m);

            resp.sendRedirect(req.getContextPath() + "/pharmacist/medicine-inventory?success=Medicine+updated+successfully");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error updating medicine", e);
            resp.sendRedirect(req.getContextPath() + "/pharmacist/medicine-inventory?error=Update+failed");
        }
    }
}
