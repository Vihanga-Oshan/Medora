package com.example.base.controller;

import com.example.base.config.DB;
import com.example.base.dao.ChatDAO;
import com.example.base.model.ChatMessage;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/chat")
public class ChatServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(ChatServlet.class.getName());

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String role = (String) req.getAttribute("jwtRole");
        String senderId = (String) req.getAttribute("jwtSub");
        String receiverId = req.getParameter("receiverId");
        String messageText = req.getParameter("message");

        // ✅ If patient is sending, it always goes to the PHARMACIST pool
        if ("patient".equals(role)) {
            receiverId = "PHARMACIST";
        }

        if (senderId == null || receiverId == null || messageText == null || messageText.trim().isEmpty()) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing parameters");
            return;
        }

        try (Connection conn = DB.getConnection()) {
            ChatDAO chatDAO = new ChatDAO(conn);
            ChatMessage msg = new ChatMessage(role, senderId, receiverId, messageText.trim());
            if (chatDAO.addMessage(msg)) {
                resp.setStatus(HttpServletResponse.SC_OK);
            } else {
                resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Failed to send message");
            }
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Error sending message", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String senderId = (String) req.getAttribute("jwtSub");
        String receiverId = req.getParameter("receiverId");
        String lastIdStr = req.getParameter("lastId");
        int lastId = (lastIdStr != null) ? Integer.parseInt(lastIdStr) : 0;

        if (senderId == null || receiverId == null) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing parameters");
            return;
        }

        resp.setContentType("application/json");
        resp.setCharacterEncoding("UTF-8");

        try (Connection conn = DB.getConnection(); PrintWriter out = resp.getWriter()) {
            ChatDAO chatDAO = new ChatDAO(conn);
            List<ChatMessage> messages = chatDAO.getMessagesBetween(senderId, receiverId, lastId);
            chatDAO.markAsRead(senderId, receiverId); // Mark messages received from others as read

            // Manual JSON construction
            out.print("[");
            for (int i = 0; i < messages.size(); i++) {
                ChatMessage m = messages.get(i);
                out.print("{");
                out.print("\"id\":" + m.getId() + ",");
                out.print("\"senderType\":\"" + m.getSenderType() + "\",");
                out.print("\"senderId\":\"" + m.getSenderId() + "\",");
                out.print("\"receiverId\":\"" + m.getReceiverId() + "\",");
                out.print("\"message\":\"" + escapeJson(m.getMessageText()) + "\",");
                out.print("\"sentAt\":\"" + m.getSentAt() + "\",");
                out.print("\"isRead\":" + m.isRead());
                out.print("}");
                if (i < messages.size() - 1)
                    out.print(",");
            }
            out.print("]");
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Error fetching messages", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }

    private String escapeJson(String text) {
        if (text == null)
            return "";
        return text.replace("\\", "\\\\")
                .replace("\"", "\\\"")
                .replace("\b", "\\b")
                .replace("\f", "\\f")
                .replace("\n", "\\n")
                .replace("\r", "\\r")
                .replace("\t", "\\t");
    }
}
