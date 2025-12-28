package com.example.base.controller.pharmacist;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.*;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/prescriptionFile/*")
public class PrescriptionFileServlet extends HttpServlet {
    private static final String UPLOAD_DIR = "uploads";
    private static final Logger LOGGER = Logger.getLogger(PrescriptionFileServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String pathInfo = req.getPathInfo(); // e.g. /{filename}
        if (pathInfo == null || pathInfo.length() <= 1) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // ✅ Sanitize filename to prevent path traversal
        String requested = new File(pathInfo).getName();
        if (requested.isEmpty()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // ✅ Fetch prescription record from DB
        Prescription pres = null;
        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            // We search by path but with robustness against quotes
            pres = dao.getPrescriptionByFilePath(requested);

            if (pres == null) {
                // If not found by exact path, try to see if it exists as a "clean" path
                LOGGER.fine("Exact path match failed for " + requested + ", trying robust search...");
            }
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "DB lookup failed for prescription file: " + requested, e);
        }

        // ✅ JWT-based authorization
        String role = (String) req.getAttribute("jwtRole");
        String subject = (String) req.getAttribute("jwtSub"); // patient NIC or pharmacist/admin ID

        LOGGER.info("Auth attempt for file: " + requested + " Role: " + role + " Sub: " + subject);

        boolean authorized = false;
        if (role != null) {
            if ("pharmacist".equals(role) || "admin".equals(role)) {
                authorized = true; // Pharmacists and admins can view all prescriptions
            } else if ("patient".equals(role) && pres != null) {
                // Patients can only see their own
                if (subject != null && subject.equals(pres.getPatientNic())) {
                    authorized = true;
                }
            }
        }

        if (!authorized) {
            LOGGER.warning("Unauthorized access attempt by " + role + " for prescription " + requested);
            resp.sendError(HttpServletResponse.SC_FORBIDDEN, "You are not allowed to access this file.");
            return;
        }

        // ✅ Resolve file location
        File file = resolveFile(req, requested);
        if (file == null || !file.exists() || !file.isFile()) {
            LOGGER.warning("Requested file not found on disk: " + requested + " Resolved path: "
                    + (file != null ? file.getAbsolutePath() : "null"));
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        // ✅ Detect MIME type
        String mime = req.getServletContext().getMimeType(file.getName());
        if (mime == null) {
            String ext = requested.substring(requested.lastIndexOf('.') + 1).toLowerCase();
            if (ext.equals("png"))
                mime = "image/png";
            else if (ext.equals("jpg") || ext.equals("jpeg"))
                mime = "image/jpeg";
            else if (ext.equals("pdf"))
                mime = "application/pdf";
            else
                mime = "application/octet-stream";
        }

        LOGGER.info("Streaming file: " + file.getAbsolutePath() + " MIME: " + mime);

        resp.setContentType(mime);
        resp.setContentLengthLong(file.length());

        // ✅ Prevent caching of protected files
        resp.setHeader("Cache-Control", "private, no-cache, no-store, must-revalidate");
        resp.setHeader("Pragma", "no-cache");
        resp.setDateHeader("Expires", 0);

        // ✅ Inline for image/pdf, otherwise download
        boolean inline = mime.startsWith("image/") || "application/pdf".equals(mime);
        resp.setHeader("Content-Disposition",
                inline ? "inline; filename=\"" + file.getName() + "\""
                        : "attachment; filename=\"" + file.getName() + "\"");

        // ✅ Stream file
        try (BufferedInputStream in = new BufferedInputStream(new FileInputStream(file));
                BufferedOutputStream out = new BufferedOutputStream(resp.getOutputStream())) {

            byte[] buffer = new byte[8192];
            int len;
            while ((len = in.read(buffer)) != -1) {
                out.write(buffer, 0, len);
            }
            out.flush();

        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Error streaming file: " + file.getAbsolutePath(), e);
            if (!resp.isCommitted())
                resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error reading file");
        }
    }

    // ✅ Helper to resolve file location
    private File resolveFile(HttpServletRequest req, String requested) {
        String configuredDir = req.getServletContext().getInitParameter("upload.dir");
        if (configuredDir != null && !configuredDir.trim().isEmpty()) {
            File cfgFile = new File(configuredDir.trim(), requested);
            if (cfgFile.exists() && cfgFile.isFile()) {
                return cfgFile;
            }
        }

        String webAppRoot = req.getServletContext().getRealPath("");
        if (webAppRoot != null) {
            return new File(webAppRoot + File.separator + UPLOAD_DIR, requested);
        } else {
            return new File(UPLOAD_DIR, requested);
        }
    }
}
