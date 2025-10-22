package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/approved-prescriptions")
public class ApprovedPrescriptionsServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(ApprovedPrescriptionsServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // ✅ Verify JWT role (stateless check)
        String role = (String) request.getAttribute("jwtRole");
        String pharmacistId = (String) request.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);

            // ✅ Fetch prescriptions where status = 'Approved'
            List<Prescription> approvedPrescriptions = dao.getPrescriptionsByStatus("Approved");

            // ✅ Attach to request for JSP rendering
            request.setAttribute("approvedPrescriptions", approvedPrescriptions);
            request.setAttribute("pharmacistId", pharmacistId);

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load approved prescriptions", e);
            request.setAttribute("errorMessage", "Failed to load approved prescriptions.");
        }

        // ✅ Forward to JSP
        request.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-approved-list.jsp")
                .forward(request, response);
    }
}
