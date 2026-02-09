package com.example.base.controller;

import com.example.base.config.DB;

import com.example.base.dao.ChatDAO;
import com.example.base.dao.PharmacistDAO;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet({ "/patient/messages", "/pharmacist/messages" })
public class ChatPageServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(ChatPageServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String role = (String) req.getAttribute("jwtRole");
        String userId = (String) req.getAttribute("jwtSub");

        // ✅ Robust Path-based Role Detection (Overrides jwtRole if there's a mismatch)
        String path = req.getRequestURI();
        if (path.contains("/pharmacist"))
            role = "pharmacist";
        else if (path.contains("/patient"))
            role = "patient";

        req.setAttribute("role", role);
        req.setAttribute("roleText", "pharmacist".equals(role) ? "Patient" : "Pharmacist");
        req.setAttribute("userId", userId);

        // ✅ For patients, always chat with the "PHARMACIST" pool
        String type = req.getParameter("type");
        if (type == null || type.isEmpty()) {
            type = "patients"; // Default
        }
        req.setAttribute("chatType", type);

        try (Connection conn = DB.getConnection()) {
            ChatDAO chatDAO = new ChatDAO(conn);
            if ("patient".equals(role)) {
                PharmacistDAO pharmacistDAO = new PharmacistDAO(conn);
                req.setAttribute("contacts", pharmacistDAO.getAllPharmacists());
            } else if ("pharmacist".equals(role)) {
                if ("suppliers".equals(type)) {
                    req.setAttribute("contacts", chatDAO.getPharmacistSupplierConversations());
                } else {
                    req.setAttribute("contacts", chatDAO.getPharmacistConversations());
                }
                req.setAttribute("unreadCounts", chatDAO.getUnreadCountsForPharmacist()); // This might need update for
                                                                                          // suppliers too but sticking
                                                                                          // to simple count for now
            }
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error loading chat contacts", e);
        }

        req.getRequestDispatcher("/WEB-INF/views/common/messages.jsp").forward(req, resp);
    }
}
