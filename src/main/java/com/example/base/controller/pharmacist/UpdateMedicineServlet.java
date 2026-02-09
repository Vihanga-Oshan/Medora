package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/update-medicine")
@MultipartConfig(fileSizeThreshold = 1024 * 1024 * 2, // 2MB
        maxFileSize = 1024 * 1024 * 10, // 10MB
        maxRequestSize = 1024 * 1024 * 50 // 50MB
)
public class UpdateMedicineServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(UpdateMedicineServlet.class.getName());
    private static final String UPLOAD_DIR = "images/medicines";

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String idStr = req.getParameter("id");
        String name = req.getParameter("name");
        String genericName = req.getParameter("generic_name");
        int categoryId = Integer.parseInt(req.getParameter("category_id"));
        String description = req.getParameter("description");
        String dosageForm = req.getParameter("dosage_form");
        String strength = req.getParameter("strength");
        String qtyStr = req.getParameter("quantity_in_stock");
        String priceStr = req.getParameter("price");
        // String imagePath = req.getParameter("image_path"); // Legacy
        String manufacturer = req.getParameter("manufacturer");
        String expiryDateStr = req.getParameter("expiry_date");
        String sellingUnit = req.getParameter("selling_unit");
        String unitQtyStr = req.getParameter("unit_quantity");
        int id = Integer.parseInt(idStr);

        try (Connection conn = dbconnection.getConnection()) {
            Medicine m = new Medicine();
            m.setId(id);
            m.setName(name);
            m.setGenericName(genericName);
            m.setCategoryId(categoryId);
            m.setDescription(description);
            m.setDosageForm(dosageForm);
            m.setStrength(strength);
            m.setQuantityInStock(Integer.parseInt(qtyStr));
            m.setManufacturer(manufacturer);

            if (priceStr != null && !priceStr.isEmpty()) {
                m.setPrice(Double.parseDouble(priceStr));
            }

            m.setSellingUnit(sellingUnit != null && !sellingUnit.isEmpty() ? sellingUnit : "Item");
            m.setUnitQuantity(unitQtyStr != null && !unitQtyStr.isEmpty() ? Integer.parseInt(unitQtyStr) : 1);

            // --- Server-Side Validation ---
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
                    error = "Invalid numeric format.";
                } catch (IllegalArgumentException e) {
                    error = "Invalid date format.";
                }
            }

            if (error != null) {
                req.setAttribute("error", error);
                req.setAttribute("medicine", m); // Keep user's input
                // We need categories for the dropdown
                try {
                    req.setAttribute("categories", com.example.base.dao.CategoryDAO.getAll(conn));
                    req.setAttribute("dosageForms", com.example.base.dao.DosageFormDAO.getAll(conn));
                    req.setAttribute("sellingUnits", com.example.base.dao.SellingUnitDAO.getAll(conn));
                } catch (Exception ex) {
                    /* handle if needed */ }
                req.getRequestDispatcher("/WEB-INF/views/pharmacist/edit-medicine.jsp").forward(req, resp);
                return;
            }
            // --- End Validation ---

            // Handle File Upload
            String newImagePath = null;
            Part filePart = req.getPart("imageFile");
            if (filePart != null && filePart.getSize() > 0) {
                String fileName = java.util.UUID.randomUUID().toString() + "_" + getSubmittedFileName(filePart);
                String appPath = req.getServletContext().getRealPath("");
                String savePath = appPath + java.io.File.separator + UPLOAD_DIR;

                java.io.File fileSaveDir = new java.io.File(savePath);
                if (!fileSaveDir.exists())
                    fileSaveDir.mkdirs();

                filePart.write(savePath + java.io.File.separator + fileName);

                // Persist to source code as well
                try {
                    String sourcePath = "c:\\Users\\User\\IdeaProjects\\Medora\\src\\main\\webapp\\images\\medicines";
                    java.io.File sourceDir = new java.io.File(sourcePath);
                    if (sourceDir.exists()) {
                        filePart.write(sourcePath + java.io.File.separator + fileName);
                    }
                } catch (Exception e) {
                    /* ignore */ }

                newImagePath = req.getContextPath() + "/" + UPLOAD_DIR + "/" + fileName;
            }

            // If new image uploaded, set it. Else check if legacy URL provided, else keep
            // existing?
            // Actually, for update, we usually want to keep existing if no new one
            // provided.
            // But we don't have the existing one here easily without querying.
            // MedicineDAO.update updates ALL fields. So we MUST provide the image path.
            // Strategy:
            // 1. If new upload -> use it.
            // 2. If no new upload ->
            // a. Check "image_path" param (could be hidden input with old value).
            // b. Or query DB to get current value.

            if (newImagePath != null) {
                m.setImagePath(newImagePath);
            } else {
                // Check if there's a hidden field with existing path
                String oldPath = req.getParameter("existing_image_path");
                String fallbackUrl = req.getParameter("image_path");

                if (oldPath != null && !oldPath.isEmpty()) {
                    m.setImagePath(oldPath);
                } else if (fallbackUrl != null && !fallbackUrl.isEmpty()) {
                    m.setImagePath(fallbackUrl);
                } else {
                    // Query DB as last resort or if simple update
                    Medicine existing = MedicineDAO.getById(conn, id);
                    if (existing != null) {
                        m.setImagePath(existing.getImagePath());
                    }
                }
            }

            // Only set expiry date if provided
            if (expiryDateStr != null && !expiryDateStr.trim().isEmpty()) {
                m.setExpiryDate(java.sql.Date.valueOf(expiryDateStr));
            } else {
                // keep the old date from DB (or query if not set above)
                if (m.getImagePath() == null) { // optimization to avoid double query if we already queried for image
                    // actually we might need to query anyway if we didn't query for image
                    // Let's just use the DAO helper for expiry
                    java.sql.Date currentExpiry = MedicineDAO.getExpiryDateById(conn, id);
                    m.setExpiryDate(currentExpiry);
                } else {
                    java.sql.Date currentExpiry = MedicineDAO.getExpiryDateById(conn, id);
                    m.setExpiryDate(currentExpiry);
                }
            }

            MedicineDAO.update(conn, m);

            resp.sendRedirect(
                    req.getContextPath() + "/pharmacist/medicine-inventory?success=Medicine+updated+successfully");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error updating medicine", e);
            resp.sendRedirect(req.getContextPath() + "/pharmacist/medicine-inventory?error=Update+failed");
        }
    }

    private String getSubmittedFileName(Part part) {
        for (String cd : part.getHeader("content-disposition").split(";")) {
            if (cd.trim().startsWith("filename")) {
                return cd.substring(cd.indexOf('=') + 1).trim().replace("\"", "");
            }
        }
        return "unknown.jpg";
    }
}
