package com.example.base.controller.patient;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/patient/edit-prescription")
public class EditPatientPrescriptionServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Verify JWT authentication (set by JwtAuthFilter)
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        String idStr = req.getParameter("id");
        if (idStr == null || idStr.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/patient/upload-prescription");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(idStr);
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            Prescription p = dao.getPrescriptionById(id);

            // ✅ Security check: verify this prescription belongs to the logged-in patient
            if (p == null || !patientNic.equals(p.getPatientNic())) {
                resp.sendError(HttpServletResponse.SC_FORBIDDEN, "Access denied");
                return;
            }

            req.setAttribute("prescription", p);
            req.getRequestDispatcher("/WEB-INF/views/patient/edit-prescription.jsp").forward(req, resp);

        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid prescription ID");
        } catch (Exception e) {
            throw new ServletException("Error loading prescription", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Verify JWT authentication again
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(req.getParameter("id"));
            String newFileName = req.getParameter("fileName");

            PrescriptionDAO dao = new PrescriptionDAO(conn);
            Prescription p = dao.getPrescriptionById(id);

            // ✅ Ownership validation
            if (p == null || !patientNic.equals(p.getPatientNic())) {
                resp.sendError(HttpServletResponse.SC_FORBIDDEN, "You cannot modify this prescription");
                return;
            }

            dao.updatePrescriptionName(id, newFileName);
            resp.sendRedirect(req.getContextPath() + "/patient/upload-prescription");

        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid prescription ID");
        } catch (Exception e) {
            throw new ServletException("Failed to update prescription", e);
        }
    }
}
