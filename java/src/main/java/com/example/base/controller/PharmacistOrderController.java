package com.example.base.controller;

import com.example.base.dao.OrderDAO;
import com.example.base.model.Order;
import com.example.base.config.DB;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.List;

public class PharmacistOrderController {

    public void handleManageOrders(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");
            // Can filter by status if query param exists
            String status = req.getParameter("status");
            List<Order> orders;
            if (status != null && !status.isEmpty()) {
                orders = OrderDAO.getOrdersByStatus(conn, status);
            } else {
                orders = OrderDAO.getAllOrders(conn);
            }
            req.setAttribute("orders", orders);
            req.getRequestDispatcher("/WEB-INF/views/pharmacist/manage-orders.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException(e);
        }
    }

    public void handleUpdateStatus(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String idStr = req.getParameter("id");
        String status = req.getParameter("status");

        if (idStr != null && status != null) {
            try (Connection conn = DB.getConnection()) {
                if (conn == null)
                    throw new SQLException("Database connection failed");
                int id = Integer.parseInt(idStr);
                OrderDAO.updateStatus(conn, id, status);
            } catch (SQLException | NumberFormatException e) {
                throw new ServletException(e);
            }
        }
        resp.sendRedirect(req.getContextPath() + "/router/pharmacist/orders");
    }
}
