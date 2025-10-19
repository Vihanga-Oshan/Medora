package com.example.base.controller;

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
        maxFileSize = 5 * 1024 * 1024,    // 5 MB
        maxRequestSize = 10 * 1024 * 1024 // 10 MB
)
public class UploadPrescriptionServlet extends HttpServlet {

    // Folder to save uploaded files (create this in your project root)
    private static final String UPLOAD_DIR = "uploads";
    private static final Logger LOGGER = Logger.getLogger(UploadPrescriptionServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Ensure user is logged in (patient)
        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        com.example.base.model.patient patient =
                (com.example.base.model.patient) session.getAttribute("patient");
        String patientNic = patient.getNic();

        // Load patient's prescriptions from DB and attach to request (always set attribute)
        List<Prescription> prescriptions = new java.util.ArrayList<>();
        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            prescriptions = dao.getPrescriptionsByPatient(patientNic);
            LOGGER.info("Loaded " + prescriptions.size() + " prescriptions for patientNic=" + patientNic);
        } catch (Exception e) {
            // Log and continue; JSP will handle empty list
            LOGGER.log(Level.SEVERE, "Failed to load prescriptions for patientNic=" + patientNic + ": " + e.getMessage(), e);
        }
        req.setAttribute("prescriptions", prescriptions);

        // Show upload form (with prescriptions attached)
        req.getRequestDispatcher("/WEB-INF/views/patient/upload-prescription.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("patient") == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        com.example.base.model.patient patient =
                (com.example.base.model.patient) session.getAttribute("patient");
        String patientNic = patient.getNic();

        Part filePart = req.getPart("prescriptionFile");
        String fileName = Paths.get(filePart.getSubmittedFileName()).getFileName().toString();

        if (fileName.isEmpty()) {
            req.setAttribute("error", "Please select a file.");
            req.getRequestDispatcher("/WEB-INF/views/patient/upload-prescription.jsp").forward(req, resp);
            return;
        }

        // ✅ Get web app root (where WEB-INF/ is)
        String configuredDir = req.getServletContext().getInitParameter("upload.dir");
        String savePath;
        if (configuredDir != null && !configuredDir.trim().isEmpty()) {
            savePath = configuredDir.trim();
        } else {
            String webAppRoot = req.getServletContext().getRealPath("");
            savePath = webAppRoot + File.separator + UPLOAD_DIR;
        }
        File fileSaveDir = new File(savePath);
        if (!fileSaveDir.exists()) {
            boolean created = fileSaveDir.mkdirs();
            if (!created) {
                LOGGER.warning("Warning: failed to create upload directory: " + fileSaveDir.getAbsolutePath());
            }
        }

 // ✅ Generate unique file name
         String uniqueFileName = UUID.randomUUID() + "_" + fileName;
         String filePath = Paths.get(savePath, uniqueFileName).toString();

 // ✅ Save file
         filePart.write(filePath);

 // ✅ Debug: Print actual path
        LOGGER.info("📁 Saved to: " + filePath);

         // Save to DB
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
