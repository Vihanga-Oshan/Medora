package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.dao.CategoryDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/addMedicine")
@MultipartConfig(fileSizeThreshold = 1024 * 1024 * 2, // 2MB
        maxFileSize = 1024 * 1024 * 10, // 10MB
        maxRequestSize = 1024 * 1024 * 50 // 50MB
)
public class AddMedicineServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AddMedicineServlet.class.getName());
    private static final String UPLOAD_DIR = "images/medicines";

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        try (Connection conn = dbconnection.getConnection()) {
            List<Medicine> medicineList = MedicineDAO.getAll(conn);
            req.setAttribute("medicineList", medicineList);
            req.setAttribute("categories", CategoryDAO.getAll(conn));
            req.setAttribute("dosageForms", com.example.base.dao.DosageFormDAO.getAll(conn));
            req.setAttribute("sellingUnits", com.example.base.dao.SellingUnitDAO.getAll(conn));
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
        int categoryId = Integer.parseInt(req.getParameter("category_id"));
        String description = req.getParameter("description");
        String dosageForm = req.getParameter("dosage_form");
        String strength = req.getParameter("strength");
        String manufacturer = req.getParameter("manufacturer");
        String expiryDateStr = req.getParameter("expiry_date");
        String qtyStr = req.getParameter("quantity_in_stock");
        String priceStr = req.getParameter("price");
        String sellingUnit = req.getParameter("selling_unit");
        String unitQtyStr = req.getParameter("unit_quantity");

        // Handle File Upload
        String imagePath = "";
        Part filePart = req.getPart("imageFile"); // Matches input name in JSP
        if (filePart != null && filePart.getSize() > 0) {
            String fileName = java.util.UUID.randomUUID().toString() + "_" + getSubmittedFileName(filePart);

            // 1. Define save paths
            String appPath = req.getServletContext().getRealPath("");
            String savePath = appPath + java.io.File.separator + UPLOAD_DIR;

            // Create directory if not exists
            java.io.File fileSaveDir = new java.io.File(savePath);
            if (!fileSaveDir.exists()) {
                fileSaveDir.mkdirs();
            }

            // Save to webapp deployment directory
            filePart.write(savePath + java.io.File.separator + fileName);

            // 2. OPTIONAL: Try to save to source directory for persistence across rebuilds
            // (Local Dev Helper)
            // This assumes a standard Maven structure relative to the deployment root or a
            // hardcoded path based on project knowledge
            try {
                // Hardcoded source path based on user context
                String sourcePath = "c:\\Users\\User\\IdeaProjects\\Medora\\src\\main\\webapp\\images\\medicines";
                java.io.File sourceDir = new java.io.File(sourcePath);
                if (sourceDir.exists()) {
                    filePart.write(sourcePath + java.io.File.separator + fileName);
                }
            } catch (Exception e) {
                // Ignore errors saving to source, it's just a dev convenience
                LOGGER.warning("Could not save to source directory: " + e.getMessage());
            }

            // Set the relative path for DB
            imagePath = req.getContextPath() + "/" + UPLOAD_DIR + "/" + fileName;
        } else {
            // Fallback or keep empty
            // If user pasted a URL in a fallback text field? (Not planned, assuming file
            // upload only)
            // Check if 'image_path' parameter exists (legacy/fallback)
            String legacyUrl = req.getParameter("image_path");
            if (legacyUrl != null && !legacyUrl.isEmpty()) {
                imagePath = legacyUrl;
            }
        }

        // --- Validation Section ---
        String error = null;
        if (name == null || name.trim().length() < 3)
            error = "Brand Name must be at least 3 characters.";
        else if (genericName == null || genericName.trim().length() < 3)
            error = "Generic Name must be at least 3 characters.";
        else if (dosageForm == null || dosageForm.trim().isEmpty())
            error = "Dosage Form is required.";
        else if (strength == null || strength.trim().isEmpty())
            error = "Strength is required.";
        else if (qtyStr == null || qtyStr.isEmpty())
            error = "Quantity is required.";
        else if (priceStr == null || priceStr.isEmpty())
            error = "Price is required.";
        else if (expiryDateStr == null || expiryDateStr.isEmpty())
            error = "Expiry Date is required.";
        else if (unitQtyStr == null || unitQtyStr.isEmpty())
            error = "Unit Quantity is required.";

        if (error == null) {
            try {
                if (Integer.parseInt(qtyStr) < 0)
                    error = "Quantity cannot be negative.";
                else if (Double.parseDouble(priceStr) <= 0)
                    error = "Price must be greater than zero.";
                else if (Integer.parseInt(unitQtyStr) <= 0)
                    error = "Unit Quantity must be at least 1.";

                java.sql.Date expiryDate = java.sql.Date.valueOf(expiryDateStr);
                if (expiryDate.before(new java.util.Date())) {
                    error = "Expiry Date must be in the future.";
                }
            } catch (NumberFormatException e) {
                error = "Invalid numeric format for quantity, price, or unit quantity.";
            } catch (IllegalArgumentException e) { // For Date.valueOf
                error = "Invalid date format for expiry date.";
            } catch (Exception e) {
                error = "An unexpected error occurred during validation.";
            }
        }

        if (error != null) {
            try (Connection conn = dbconnection.getConnection()) {
                req.setAttribute("error", error);
                req.setAttribute("categories", CategoryDAO.getAll(conn));
                req.setAttribute("dosageForms", com.example.base.dao.DosageFormDAO.getAll(conn));
                req.setAttribute("sellingUnits", com.example.base.dao.SellingUnitDAO.getAll(conn));
                req.getRequestDispatcher("/WEB-INF/views/pharmacist/add-medicine.jsp").forward(req, resp);
                return;
            } catch (Exception e) {
                LOGGER.log(Level.SEVERE, "Error reloading categories for validation failure", e);
                resp.sendError(500);
                return;
            }
        }
        // --- End Validation ---

        int pharmacistId = Integer.parseInt((String) req.getAttribute("jwtSub"));

        try (Connection conn = dbconnection.getConnection()) {

            Medicine medicine = new Medicine();
            medicine.setName(name);
            medicine.setGenericName(genericName);
            medicine.setCategoryId(categoryId);
            medicine.setDescription(description);
            medicine.setDosageForm(dosageForm);
            medicine.setStrength(strength);
            medicine.setQuantityInStock(Integer.parseInt(qtyStr));
            medicine.setManufacturer(manufacturer);
            medicine.setExpiryDate(Date.valueOf(expiryDateStr));
            medicine.setAddedBy(pharmacistId);

            medicine.setPrice(Double.parseDouble(priceStr));
            medicine.setImagePath(imagePath);

            // Set units and measurements
            medicine.setSellingUnit(sellingUnit != null && !sellingUnit.isEmpty() ? sellingUnit : "Item");
            medicine.setUnitQuantity(unitQtyStr != null && !unitQtyStr.isEmpty() ? Integer.parseInt(unitQtyStr) : 1);

            MedicineDAO.insert(conn, medicine);

            // reload list and forward back to JSP
            req.setAttribute("success", "Medicine added successfully!");
            req.setAttribute("medicineList", MedicineDAO.getAll(conn));
            req.setAttribute("categories", CategoryDAO.getAll(conn));
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

    // Utility method to get filename from Part
    private String getSubmittedFileName(Part part) {
        for (String cd : part.getHeader("content-disposition").split(";")) {
            if (cd.trim().startsWith("filename")) {
                return cd.substring(cd.indexOf('=') + 1).trim().replace("\"", "");
            }
        }
        return "unknown.jpg";
    }
}
