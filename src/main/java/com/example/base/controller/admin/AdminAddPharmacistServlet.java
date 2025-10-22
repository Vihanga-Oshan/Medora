package com.example.base.controller.admin;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/admin/add-pharmacist")
public class AdminAddPharmacistServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already validated JWT and role
        String adminNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: handle edge case if filter skipped
        if (adminNic == null) {
            resp.sendRedirect(req.getContextPath() + "/admin/login");
            return;
        }

        // ✅ Pass admin NIC to JSP (for display or logs)
        req.setAttribute("adminNic", adminNic);

        // ✅ Forward to JSP
        req.getRequestDispatcher("/WEB-INF/views/admin/add-pharmacist.jsp").forward(req, resp);
    }
}
