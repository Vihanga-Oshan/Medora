package com.example.base.controller.pharmacist;

import com.example.base.util.EncryptionUtil;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/medicineImage/*")
public class MedicineImageServlet extends HttpServlet {
    private static final String UPLOAD_DIR = "images/medicines";
    private static final Logger LOGGER = Logger.getLogger(MedicineImageServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String pathInfo = req.getPathInfo();
        if (pathInfo == null || pathInfo.length() <= 1) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        String fileName = new File(pathInfo).getName();
        String appPath = req.getServletContext().getRealPath("");
        File file = new File(appPath + File.separator + UPLOAD_DIR, fileName);

        if (!file.exists() || !file.isFile()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND);
            return;
        }

        try {
            byte[] encryptedData = Files.readAllBytes(file.toPath());
            byte[] decryptedData = EncryptionUtil.decryptBytes(encryptedData);

            if (decryptedData == null) {
                // If decryption fails, maybe it's an old unencrypted file?
                // For a school project, we can fallback to serving as-is
                decryptedData = encryptedData;
            }

            String mimeType = req.getServletContext().getMimeType(file.getName());
            if (mimeType == null) {
                mimeType = "image/jpeg";
            }

            resp.setContentType(mimeType);
            resp.setContentLength(decryptedData.length);
            resp.getOutputStream().write(decryptedData);
            resp.getOutputStream().flush();

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error serving medicine image", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
}
