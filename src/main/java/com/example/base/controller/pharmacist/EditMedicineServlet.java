package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.dao.CategoryDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.PreparedStatement;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/edit-medicine")
public class EditMedicineServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(EditMedicineServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String idStr = req.getParameter("id");
        if (idStr == null) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/medicine‑inventory");
            return;
        }

        int id = Integer.parseInt(idStr);

        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM medicines WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);
                ResultSet rs = stmt.executeQuery();
                if (rs.next()) {
                    Medicine m = new Medicine();
                    m.setId(rs.getInt("id"));
                    m.setName(rs.getString("name"));
                    m.setGenericName(rs.getString("generic_name"));
                    m.setCategory(rs.getString("category"));
                    m.setDescription(rs.getString("description"));
                    m.setDosageForm(rs.getString("dosage_form"));
                    m.setStrength(rs.getString("strength"));
                    m.setQuantityInStock(rs.getInt("quantity_in_stock"));
                    m.setManufacturer(rs.getString("manufacturer"));
                    m.setExpiryDate(rs.getDate("expiry_date"));
                    m.setAddedBy(rs.getInt("added_by"));

                    req.setAttribute("medicine", m);
                    // Fetch categories for dropdown
                    req.setAttribute("categories", CategoryDAO.getAll(conn));
                    req.setAttribute("dosageForms", com.example.base.dao.DosageFormDAO.getAll(conn));
                    req.setAttribute("sellingUnits", com.example.base.dao.SellingUnitDAO.getAll(conn));

                    req.getRequestDispatcher("/WEB-INF/views/pharmacist/edit-medicine.jsp").forward(req, resp);
                    return;
                } else {
                    resp.sendRedirect(req.getContextPath() + "/pharmacist/medicine‑inventory");
                    return;
                }
            }
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading medicine for edit", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error loading medicine");
        }
    }
}
