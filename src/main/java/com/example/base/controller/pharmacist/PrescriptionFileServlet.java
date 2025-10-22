package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.*;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/prescriptionFile/*")
public class PrescriptionFileServlet extends HttpServlet {
    private static final String UPLOAD_DIR = "uploads";
    private static final Logger LOGGER = Logger.getLogger(PrescriptionFileServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String pathInfo = req.getPathInfo(); // e.g. /{filename}
        if (pathInfo == null || pathInfo.length() <= 1) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // sanitize filename to prevent path traversal
        String requested = new File(pathInfo).getName();
        if (requested.isEmpty()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // Authorization: look up prescription by stored file path and check session user
        Prescription pres;
        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            pres = dao.getPrescriptionByFilePath(requested);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "DB lookup failed for prescription file: " + requested, e);
            // if DB lookup fails, treat as not found for security
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        if (pres == null) {
            LOGGER.fine("No prescription DB entry for requested file: " + requested);
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // Check session: allow if logged-in patient owns it, or a logged-in pharmacist exists in session
        HttpSession session = req.getSession(false);
        boolean authorized = false;
        if (session != null) {
            Object patientObj = session.getAttribute("patient");
            if (patientObj != null) {
                // prefer direct cast to your patient model to avoid reflection
                try {
                    if (patientObj instanceof com.example.base.model.patient) {
                        com.example.base.model.patient sessPatient = (com.example.base.model.patient) patientObj;
                        String nic = sessPatient.getNic();
                        if (nic != null && nic.equals(pres.getPatientNic())) {
                            authorized = true;
                        }
                    }
                } catch (Exception e) {
                    LOGGER.log(Level.WARNING, "Failed to read patient nic from session object", e);
                }
            }
            // allow pharmacists (staff) to view
            Object pharm = session.getAttribute("pharmacist");
            if (pharm != null) authorized = true;
            // also allow any other roles you may use (e.g., admin) by checking session attributes here
        }

        if (!authorized) {
            resp.sendError(HttpServletResponse.SC_FORBIDDEN);
            return;
        }

        // Determine base storage directory: prefer configured external folder
        String configuredDir = req.getServletContext().getInitParameter("upload.dir");
        File file = null;
        String webAppRoot = req.getServletContext().getRealPath("");
        // 1) Try configured external directory (if provided)
        if (configuredDir != null && !configuredDir.trim().isEmpty()) {
            File cfgFile = new File(configuredDir.trim(), requested);
            if (cfgFile.exists() && cfgFile.isFile()) {
                file = cfgFile;
            } else {
                LOGGER.info("Configured upload.dir '" + configuredDir + "' does not contain requested file; will try webapp uploads folder");
            }
        }

        // 2) Fallback to webapp's internal uploads directory
        if (file == null) {
            if (webAppRoot != null) {
                file = new File(webAppRoot + File.separator + UPLOAD_DIR, requested);
            } else {
                // getRealPath returned null (possible in some servlet containers). Fall back to relative path.
                file = new File(UPLOAD_DIR, requested);
            }
        }

        if (!file.exists() || !file.isFile()) {
            LOGGER.warning("Requested file not found on disk: " + file.getAbsolutePath());
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // Determine content type
        String mime = req.getServletContext().getMimeType(file.getName());
        if (mime == null) mime = "application/octet-stream";

        resp.setContentType(mime);
        resp.setContentLengthLong(file.length());

        // Prevent caching of protected files in shared caches / browsers
        resp.setHeader("Cache-Control", "private, no-cache, no-store, must-revalidate");
        resp.setHeader("Pragma", "no-cache");
        resp.setDateHeader("Expires", 0);

        // For images/PDF display inline; for others force download
        boolean inline = mime.startsWith("image/") || "application/pdf".equals(mime);
        if (inline) {
            resp.setHeader("Content-Disposition", "inline; filename=\"" + file.getName() + "\"");
        } else {
            resp.setHeader("Content-Disposition", "attachment; filename=\"" + file.getName() + "\"");
        }

        // Stream the file
        try (BufferedInputStream in = new BufferedInputStream(new FileInputStream(file));
             BufferedOutputStream out = new BufferedOutputStream(resp.getOutputStream())) {
            byte[] buffer = new byte[8192];
            int len;
            while ((len = in.read(buffer)) != -1) {
                out.write(buffer, 0, len);
            }
            out.flush();
        } catch (IOException e) {
            // If streaming fails, log and send 500
            LOGGER.log(Level.SEVERE, "Failed streaming file to client: " + file.getAbsolutePath(), e);
            if (!resp.isCommitted()) resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
}
