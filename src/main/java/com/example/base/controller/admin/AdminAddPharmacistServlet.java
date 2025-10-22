package com.example.base.controller.admin;

import com.example.base.model.Admin;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/admin/add-pharmacist")

public class AdminAddPharmacistServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        HttpSession session = req.getSession(false);
        if (session == null || session.getAttribute("admin") == null) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        Admin admin = (Admin) session.getAttribute("admin");
        req.setAttribute("admin", admin);

        // Forward to the add pharmacist JSP
        req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
    }
}
