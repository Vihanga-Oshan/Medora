package com.example.base.controller;

import com.example.base.dao.MedicineDAO;
import com.example.base.dao.OrderDAO;
import com.example.base.model.Medicine;
import com.example.base.model.Order;
import com.example.base.model.OrderItem;
import com.example.base.model.patient;
import com.example.base.config.DB;

import com.example.base.dao.CategoryDAO;
import com.example.base.model.Category;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.HashMap;

import java.util.logging.Logger;
import java.util.logging.Level;

public class ShopController {
    private static final Logger LOGGER = Logger.getLogger(ShopController.class.getName());

    @SuppressWarnings("unchecked")
    public void handleCatalog(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String category = req.getParameter("category");
        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");

            // Fetch categories for sidebar
            List<Category> categoryList = CategoryDAO.getAll(conn);
            req.setAttribute("categories", categoryList);

            List<Medicine> medicines;
            if (category != null && !category.isEmpty()) {
                medicines = MedicineDAO.getByCategory(conn, category);
                req.setAttribute("currentCategory", category);
            } else {
                medicines = MedicineDAO.getAll(conn);
            }
            req.setAttribute("medicines", medicines);

            // Centralized loading of Sidebar/Ads/Suggestions
            loadCatalogMetadata(req, conn);

            req.getRequestDispatcher("/WEB-INF/views/shop/catalog.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException(e);
        }
    }

    private void loadCatalogMetadata(HttpServletRequest req, Connection conn) throws SQLException {
        // Dynamic Suggestions (Get 4 random medicines from ALL medicines)
        List<Medicine> allMeds = MedicineDAO.getAll(conn);
        List<Medicine> suggestions = new ArrayList<>();
        if (!allMeds.isEmpty()) {
            int count = Math.min(4, allMeds.size());
            java.util.Collections.shuffle(allMeds);
            for (int i = 0; i < count; i++) {
                suggestions.add(allMeds.get(i));
            }
        }
        req.setAttribute("suggestions", suggestions);

        // Recently Viewed from Session
        HttpSession session = req.getSession();
        @SuppressWarnings("unchecked")
        List<Integer> recentlyViewedIds = (List<Integer>) session.getAttribute("recentlyViewedIds");
        List<Medicine> recentlyViewed = new ArrayList<>();
        if (recentlyViewedIds != null) {
            for (Integer id : recentlyViewedIds) {
                Medicine m = MedicineDAO.getById(conn, id);
                if (m != null)
                    recentlyViewed.add(m);
            }
        }
        req.setAttribute("recentlyViewed", recentlyViewed);

        // Bento-Style Dynamic Promos
        Map<String, String> mainPromo = new HashMap<>();
        mainPromo.put("tag", "Biggest Offer Revealed");
        mainPromo.put("title", "MORE DEALS INSIDE<br>UP TO 50% OFF");
        mainPromo.put("desc", "Premium medical essentials for every household.");
        req.setAttribute("mainPromo", mainPromo);

        List<Map<String, String>> subPromos = new ArrayList<>();
        Map<String, String> p1 = new HashMap<>();
        p1.put("tag", "NEW ARRIVAL");
        p1.put("title", "First Aid Essentials");
        p1.put("desc", "Complete kits for emergencies.");
        p1.put("offer", "UP TO 30% OFF");
        p1.put("bg", "#fdf2f2");
        subPromos.add(p1);

        Map<String, String> p2 = new HashMap<>();
        p2.put("tag", "SUGGESTION");
        p2.put("title", "Vitamins & Energy");
        p2.put("desc", "Boost your daily vitality.");
        p2.put("offer", "Starting from $19");
        p2.put("bg", "#f0f7ff");
        subPromos.add(p2);
        req.setAttribute("subPromos", subPromos);
    }

    public void handleSearch(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String query = req.getParameter("q");
        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");
            List<Medicine> medicines;
            if (query != null && !query.trim().isEmpty()) {
                medicines = MedicineDAO.search(conn, query);
            } else {
                medicines = MedicineDAO.getAll(conn);
            }
            req.setAttribute("medicines", medicines);
            req.setAttribute("searchQuery", query);

            // Centralized loading of Sidebar/Ads/Suggestions
            loadCatalogMetadata(req, conn);

            req.getRequestDispatcher("/WEB-INF/views/shop/catalog.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException(e);
        }
    }

    public void handleItemDetails(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String idStr = req.getParameter("id");
        if (idStr == null) {
            resp.sendRedirect(req.getContextPath() + "/router/shop");
            return;
        }
        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");
            int id = Integer.parseInt(idStr);
            Medicine medicine = MedicineDAO.getById(conn, id);
            if (medicine == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Medicine not found");
                return;
            }

            // Track Recently Viewed
            HttpSession session = req.getSession();
            List<Integer> recentlyViewedIds = (List<Integer>) session.getAttribute("recentlyViewedIds");
            if (recentlyViewedIds == null) {
                recentlyViewedIds = new ArrayList<>();
            }
            if (!recentlyViewedIds.contains(id)) {
                recentlyViewedIds.add(0, id); // Add to beginning
                if (recentlyViewedIds.size() > 4) {
                    recentlyViewedIds.remove(4); // Keep only 4
                }
                session.setAttribute("recentlyViewedIds", recentlyViewedIds);
            }

            req.setAttribute("medicine", medicine);
            req.getRequestDispatcher("/WEB-INF/views/shop/item_details.jsp").forward(req, resp);
        } catch (SQLException | NumberFormatException e) {
            throw new ServletException(e);
        }
    }

    @SuppressWarnings("unchecked")
    public void handleCart(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        HttpSession session = req.getSession();
        Map<Object, Object> cart = getCartFromSession(session);
        List<Map<String, Object>> cartDetails = new ArrayList<>();
        double total = 0;

        if (cart != null && !cart.isEmpty()) {
            try (Connection conn = DB.getConnection()) {
                if (conn == null)
                    throw new SQLException("Database connection failed");
                for (Map.Entry<Object, Object> entry : cart.entrySet()) {
                    try {
                        int id = Integer.parseInt(entry.getKey().toString());
                        int qty = Integer.parseInt(entry.getValue().toString());
                        Medicine m = MedicineDAO.getById(conn, id);
                        if (m != null) {
                            Map<String, Object> detail = new HashMap<>();
                            detail.put("medicine", m);
                            detail.put("quantity", qty);
                            detail.put("subtotal", m.getPrice() * qty);
                            cartDetails.add(detail);
                            total += m.getPrice() * qty;
                        }
                    } catch (NumberFormatException e) {
                        LOGGER.warning("Invalid cart key/value type: " + entry.getKey() + "=" + entry.getValue());
                    }
                }
            } catch (SQLException e) {
                LOGGER.log(Level.SEVERE, "Database error in handleCart", e);
                throw new ServletException(e);
            }
        }

        req.setAttribute("cartDetails", cartDetails);
        req.setAttribute("cartTotal", total);
        req.getRequestDispatcher("/WEB-INF/views/shop/cart.jsp").forward(req, resp);
    }

    @SuppressWarnings("unchecked")
    public void handleAddToCart(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String idStr = req.getParameter("id");
        String qtyStr = req.getParameter("quantity");
        LOGGER.info("Add to cart: id=" + idStr + ", qty=" + qtyStr);

        if (idStr != null && qtyStr != null) {
            try {
                int id = Integer.parseInt(idStr);
                int qty = Integer.parseInt(qtyStr);

                HttpSession session = req.getSession();
                Map<Object, Object> cart = getCartFromSession(session);
                if (cart == null) {
                    cart = new HashMap<>();
                    session.setAttribute("cart", cart);
                }

                String key = String.valueOf(id); // Use String keys for consistency across environments
                int currentQty = 0;
                Object existing = cart.get(key);
                if (existing != null) {
                    currentQty = Integer.parseInt(existing.toString());
                }

                cart.put(key, currentQty + qty);
                LOGGER.info("Cart updated. Total unique items: " + cart.size());
                LOGGER.info("Cart updated. Total items in map: " + cart.size());
            } catch (NumberFormatException e) {
                LOGGER.warning("Invalid number format for add to cart: " + e.getMessage());
            }
        }
        resp.sendRedirect(req.getContextPath() + "/router/shop/cart");
    }

    @SuppressWarnings("unchecked")
    public void handleRemoveFromCart(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String idStr = req.getParameter("id");
        if (idStr != null) {
            HttpSession session = req.getSession();
            Map<Object, Object> cart = getCartFromSession(session);
            if (cart != null) {
                cart.remove(idStr); // Use String key to match handleAddToCart
                session.setAttribute("cart", cart);
            }
        }
        resp.sendRedirect(req.getContextPath() + "/router/shop/cart");
    }

    @SuppressWarnings("unchecked")
    public void handleCheckout(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        HttpSession session = req.getSession();
        patient user = (patient) session.getAttribute("patient");

        if (user == null) {
            resp.sendRedirect(req.getContextPath() + "/router/patient/login?redirect=/router/shop/cart");
            return;
        }

        Map<Object, Object> cart = getCartFromSession(session);
        if (cart == null || cart.isEmpty()) {
            LOGGER.warning("Attempted checkout with empty cart");
            resp.sendRedirect(req.getContextPath() + "/router/shop");
            return;
        }

        LOGGER.info("Processing checkout for patient: " + user.getNic() + " with " + cart.size() + " unique items");

        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");
            Order order = new Order();
            order.setPatientNic(user.getNic());

            List<OrderItem> items = new ArrayList<>();
            double total = 0;

            for (Map.Entry<Object, Object> entry : cart.entrySet()) {
                try {
                    int id = Integer.parseInt(entry.getKey().toString());
                    int qty = Integer.parseInt(entry.getValue().toString());
                    Medicine m = MedicineDAO.getById(conn, id);
                    if (m != null) {
                        OrderItem item = new OrderItem();
                        item.setMedicineId(m.getId());
                        item.setQuantity(qty);
                        item.setPrice(m.getPrice());
                        items.add(item);
                        total += m.getPrice() * qty;
                    }
                } catch (NumberFormatException e) {
                    LOGGER.warning("Invalid key in checkout: " + entry.getKey());
                }
            }

            order.setTotalAmount(total);
            order.setItems(items);

            OrderDAO.createOrder(conn, order);

            // Clear cart
            session.removeAttribute("cart");

            // Redirect to orders page
            resp.sendRedirect(req.getContextPath() + "/router/shop/orders");

        } catch (SQLException e) {
            throw new ServletException(e);
        }
    }

    public void handleCompleteOrder(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        HttpSession session = req.getSession();
        patient user = (patient) session.getAttribute("patient");

        if (user == null) {
            resp.sendRedirect(req.getContextPath() + "/patient/login");
            return;
        }

        String idStr = req.getParameter("id");
        if (idStr == null) {
            resp.sendRedirect(req.getContextPath() + "/router/shop/orders");
            return;
        }

        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");

            int orderId = Integer.parseInt(idStr);
            Order order = OrderDAO.getOrderById(conn, orderId);

            // Validate order belongs to user
            if (order == null || !order.getPatientNic().equals(user.getNic())) {
                resp.sendError(HttpServletResponse.SC_FORBIDDEN);
                return;
            }

            // Validate status
            if (!"APPROVED".equals(order.getStatus())) {
                resp.sendRedirect(req.getContextPath() + "/router/shop/orders?error=Order+not+approved");
                return;
            }

            // 1. Update stock
            // Loop through items and reduce stock
            // Ideally should be in a transaction
            try {
                conn.setAutoCommit(false);

                for (OrderItem item : order.getItems()) {
                    MedicineDAO.reduceStock(conn, item.getMedicineId(), item.getQuantity());
                }

                // 2. Update status
                OrderDAO.updateStatus(conn, orderId, "COMPLETED");

                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e; // Rethrow to handle in outer catch
            } finally {
                conn.setAutoCommit(true);
            }

            resp.sendRedirect(req.getContextPath() + "/router/shop/orders?success=Order+completed");

        } catch (SQLException | NumberFormatException e) {
            throw new ServletException(e);
        }
    }

    public void handleMyOrders(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        HttpSession session = req.getSession();
        patient user = (patient) session.getAttribute("patient");

        if (user == null) {
            resp.sendRedirect(req.getContextPath() + "/router/patient/login");
            return;
        }

        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");
            List<Order> orders = OrderDAO.getOrdersByPatient(conn, user.getNic());
            req.setAttribute("orders", orders);
            req.getRequestDispatcher("/WEB-INF/views/shop/orders.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException(e);
        }
    }

    public void handleOrderDetail(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        HttpSession session = req.getSession();
        patient user = (patient) session.getAttribute("patient");

        if (user == null) {
            resp.sendRedirect(req.getContextPath() + "/router/patient/login");
            return;
        }

        String idStr = req.getParameter("id");
        if (idStr == null) {
            resp.sendRedirect(req.getContextPath() + "/router/shop/orders");
            return;
        }

        try (Connection conn = DB.getConnection()) {
            if (conn == null)
                throw new SQLException("Database connection failed");

            int orderId = Integer.parseInt(idStr);
            Order order = OrderDAO.getOrderById(conn, orderId);

            if (order == null || !order.getPatientNic().equals(user.getNic())) {
                resp.sendError(HttpServletResponse.SC_FORBIDDEN);
                return;
            }

            req.setAttribute("order", order);
            req.getRequestDispatcher("/WEB-INF/views/shop/order_details.jsp").forward(req, resp);
        } catch (SQLException | NumberFormatException e) {
            throw new ServletException(e);
        }
    }

    @SuppressWarnings("unchecked")
    private Map<Object, Object> getCartFromSession(HttpSession session) {
        return (Map<Object, Object>) session.getAttribute("cart");
    }
}
