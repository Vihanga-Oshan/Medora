package com.example.base.controller.patient;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.File;
import java.io.IOException;
import java.nio.file.Paths;
import java.sql.Connection;
import java.util.List;
import java.util.UUID;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/patient/upload-prescription")
@MultipartConfig(
        fileSizeThreshold = 1024 * 1024, // 1 MB
        maxFileSize = 5 * 1024 * 1024,   // 5 MB
        maxRequestSize = 10 * 1024 * 1024 // 10 MB
)
public class UploadPrescriptionServlet extends HttpServlet {

    private static final String UPLOAD_DIR = "uploads";
    private static final Logger LOGGER = Logger.getLogger(UploadPrescriptionServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Use JWT attributes injected by JwtAuthFilter
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // ✅ Load patient’s prescriptions from DB
        List<Prescription> prescriptions = new java.util.ArrayList<>();
        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            prescriptions = dao.getPrescriptionsByPatient(patientNic);
            LOGGER.info("Loaded " + prescriptions.size() + " prescriptions for patientNic=" + patientNic);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load prescriptions for patientNic=" + patientNic, e);
        }

        req.setAttribute("prescriptions", prescriptions);
        req.getRequestDispatcher("/WEB-INF/views/patient/upload-prescription.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        Part filePart = req.getPart("prescriptionFile");
        String fileName = Paths.get(filePart.getSubmittedFileName()).getFileName().toString();

        if (fileName.isEmpty()) {
            req.setAttribute("error", "Please select a file.");
            req.getRequestDispatcher("/WEB-INF/views/patient/upload-prescription.jsp").forward(req, resp);
            return;
        }

        // ✅ Determine save path
        String configuredDir = req.getServletContext().getInitParameter("upload.dir");
        String savePath = (configuredDir != null && !configuredDir.trim().isEmpty())
                ? configuredDir.trim()
                : req.getServletContext().getRealPath("") + File.separator + UPLOAD_DIR;

        File fileSaveDir = new File(savePath);
        if (!fileSaveDir.exists() && !fileSaveDir.mkdirs()) {
            LOGGER.warning("Failed to create upload directory: " + fileSaveDir.getAbsolutePath());
        }

        // ✅ Generate unique filename
        String uniqueFileName = UUID.randomUUID() + "_" + fileName;
        String filePath = Paths.get(savePath, uniqueFileName).toString();

        // ✅ Save the uploaded file
        filePart.write(filePath);
        LOGGER.info("📁 Saved prescription file: " + filePath);

        // ✅ Save record to DB
        try (Connection conn = dbconnection.getConnection()) {
            Prescription prescription = new Prescription();
            prescription.setPatientNic(patientNic);
            prescription.setFileName(fileName);
            prescription.setFilePath(uniqueFileName);
            prescription.setStatus("PENDING");

            PrescriptionDAO dao = new PrescriptionDAO(conn);
            dao.insertPrescription(prescription);

            resp.sendRedirect(req.getContextPath() + "/patient/dashboard?upload=success");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to save prescription to DB", e);
            req.setAttribute("error", "Upload failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/patient/upload-prescription.jsp").forward(req, resp);
        }
    }
}
