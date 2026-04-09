package com.example.base.controller.pharmacist;

import com.example.base.dao.MedicineDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Medicine;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;
import java.util.logging.*;

@WebServlet("/pharmacist/medicine-inventory")
public class MedicineInventoryServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(MedicineInventoryServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        try (Connection conn = dbconnection.getConnection()) {
            List<Medicine> medicineList = MedicineDAO.getAll(conn);
            req.setAttribute("medicineList", medicineList);
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/medicine-inventory.jsp").forward(req, resp);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading medicines", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Could not load medicine inventory.");
        }
    }
}
