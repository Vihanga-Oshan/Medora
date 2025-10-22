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

@WebServlet("/pharmacist/approved-prescriptions")
public class ApprovedPrescriptionsServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Ensure pharmacist is authenticated (defense-in-depth)
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("pharmacist") == null) {
            response.sendRedirect(request.getContextPath() + "/pharmacist/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);

            // Fetch prescriptions where status = 'Approved'
            List<Prescription> approvedPrescriptions = dao.getPrescriptionsByStatus("Approved");

            // ✅ Correct attribute name and variable
            request.setAttribute("approvedPrescriptions", approvedPrescriptions);

        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "Failed to load approved prescriptions.");
        }

        // Forward to JSP
        request.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-approved-list.jsp")
                .forward(request, response);
    }
}
