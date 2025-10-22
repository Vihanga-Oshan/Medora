package com.example.base.controller.pharmacist;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.*;
import java.nio.file.Paths;

@WebServlet("/view-prescription")
public class ViewPrescriptionServlet extends HttpServlet {

    private static final String UPLOAD_DIR = "uploads";

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String filePath = req.getParameter("filePath");
        if (filePath == null || filePath.trim().isEmpty()) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "File path required");
            return;
        }

        // Get web app root (where WEB-INF/ lives)
        String webAppRoot = req.getServletContext().getRealPath("");
        String fullPath = Paths.get(webAppRoot, UPLOAD_DIR, filePath).toString();
        System.out.println("Requested filePath: " + filePath);
        System.out.println("Resolved fullPath: " + fullPath);

        File file = new File(fullPath);
        if (!file.exists()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found: " + filePath);
            return;
        }

        // Set correct content type (e.g., image/jpeg, application/pdf)
        String mimeType = req.getServletContext().getMimeType(fullPath);
        if (mimeType == null) {
            mimeType = "application/octet-stream"; // fallback
        }
        resp.setContentType(mimeType);
        resp.setContentLength((int) file.length());

        // Stream file to browser
        try (InputStream in = new FileInputStream(file);
             OutputStream out = resp.getOutputStream()) {
            byte[] buffer = new byte[4096];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        }
    }
}