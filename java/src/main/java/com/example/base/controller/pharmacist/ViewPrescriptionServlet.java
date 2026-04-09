package com.example.base.controller.pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.*;
import java.nio.file.Paths;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/pharmacist/view-prescription")
public class ViewPrescriptionServlet extends HttpServlet {

    private static final Logger LOGGER = Logger.getLogger(ViewPrescriptionServlet.class.getName());
    private static final String UPLOAD_DIR = "uploads";

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Auth check via JWT claims
        String role = (String) req.getAttribute("jwtRole");
        String pharmacistId = (String) req.getAttribute("jwtSub");

        if (role == null || !"pharmacist".equals(role)) {
            resp.sendRedirect(req.getContextPath() + "/pharmacist/login");
            return;
        }

        String filePath = req.getParameter("filePath");
        if (filePath == null || filePath.trim().isEmpty()) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "File path required");
            return;
        }

        // ✅ Resolve file path (safe and portable)
        String webAppRoot = req.getServletContext().getRealPath("");
        String fullPath = Paths.get(webAppRoot, UPLOAD_DIR, filePath).normalize().toString();

        File file = new File(fullPath);
        if (!file.exists() || !file.isFile()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found");
            LOGGER.warning("File not found: " + fullPath);
            return;
        }

        // ✅ Determine MIME type
        String mimeType = req.getServletContext().getMimeType(fullPath);
        if (mimeType == null) mimeType = "application/octet-stream";
        resp.setContentType(mimeType);
        resp.setContentLengthLong(file.length());

        // ✅ Stream file
        try (InputStream in = new FileInputStream(file);
             OutputStream out = resp.getOutputStream()) {
            byte[] buffer = new byte[4096];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Error streaming file: " + fullPath, e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error reading file");
        }
    }
}
