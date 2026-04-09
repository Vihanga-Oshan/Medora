package com.example.base.controller.patient;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.File;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/delete-prescription")
public class DeletePrescriptionServlet extends HttpServlet {

    private static final Logger LOGGER = Logger.getLogger(DeletePrescriptionServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");
        String idParam = req.getParameter("id");

        if (role == null || !"patient".equals(role) || patientNic == null || idParam == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            int id = Integer.parseInt(idParam);
            Prescription prescription = dao.getPrescriptionById(id);

            if (prescription == null || !patientNic.equals(prescription.getPatientNic())) {
                resp.sendError(HttpServletResponse.SC_FORBIDDEN);
                return;
            }

            // Delete the file
            String uploadDir = getServletContext().getInitParameter("upload.dir");
            if (uploadDir == null || uploadDir.trim().isEmpty()) {
                uploadDir = getServletContext().getRealPath("/uploads");
            }
            File file = new File(uploadDir, prescription.getFilePath());
            if (file.exists() && file.delete()) {
                LOGGER.info("Deleted file: " + file.getAbsolutePath());
            } else {
                LOGGER.warning("Failed to delete file: " + file.getAbsolutePath());
            }

            // Delete from database
            try (var stmt = conn.prepareStatement("DELETE FROM prescriptions WHERE id = ?")) {
                stmt.setInt(1, id);
                stmt.executeUpdate();
            }

            resp.sendRedirect(req.getContextPath() + "/patient/upload-prescription?delete=success");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to delete prescription", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
}
