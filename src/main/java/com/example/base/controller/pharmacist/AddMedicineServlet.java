package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/addMedicine")
public class AddMedicineServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AddMedicineServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        try (Connection conn = dbconnection.getConnection()) {
            List<Medicine> medicineList = MedicineDAO.getAll(conn);
            req.setAttribute("medicineList", medicineList);
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/add-medicine.jsp").forward(req, resp);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading medicines", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error loading medicines");
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String name = req.getParameter("name");
        String genericName = req.getParameter("generic_name");
        String category = req.getParameter("category");
        String description = req.getParameter("description");
        String dosageForm = req.getParameter("dosage_form");
        String strength = req.getParameter("strength");
        String manufacturer = req.getParameter("manufacturer");
        String expiryDateStr = req.getParameter("expiry_date");
        String qtyStr = req.getParameter("quantity_in_stock");
        int pharmacistId = Integer.parseInt((String) req.getAttribute("jwtSub"));

        try (Connection conn = dbconnection.getConnection()) {

            Medicine medicine = new Medicine();
            medicine.setName(name);
            medicine.setGenericName(genericName);
            medicine.setCategory(category);
            medicine.setDescription(description);
            medicine.setDosageForm(dosageForm);
            medicine.setStrength(strength);
            medicine.setQuantityInStock(Integer.parseInt(qtyStr));
            medicine.setManufacturer(manufacturer);
            medicine.setExpiryDate(Date.valueOf(expiryDateStr));
            medicine.setAddedBy(pharmacistId);

            MedicineDAO.insert(conn, medicine);

            // ✅ reload list and forward back to JSP
            req.setAttribute("success", "Medicine added successfully!");
            req.setAttribute("medicineList", MedicineDAO.getAll(conn));
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/add-medicine.jsp").forward(req, resp);

        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Error adding medicine", e);
            req.setAttribute("error", "Database error: " + e.getMessage());
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/add-medicine.jsp").forward(req, resp);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Unexpected error", e);
            req.setAttribute("error", "Unexpected error: " + e.getMessage());
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/add-medicine.jsp").forward(req, resp);
        }
    }
}
