package com.example.base.controller.guardian;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/guardian/profile")
public class GuardianProfileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JwtAuthFilter already validates guardian token and injects claims
        String guardianNic = (String) req.getAttribute("jwtSub");

        // 🔒 Defense-in-depth: handle direct access without a valid JWT
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // ✅ Pass guardian info to JSP (for personalization)
        req.setAttribute("guardianNic", guardianNic);

        // ✅ Forward to profile JSP
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-profile.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Ensure the user is authenticated
        String guardianNic = (String) req.getAttribute("jwtSub");
        if (guardianNic == null || guardianNic.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/guardian/login");
            return;
        }

        // ✅ Example: handle form updates (you’ll later connect this to GuardianDAO)
        String name = req.getParameter("name");
        String contact = req.getParameter("contact");

        // In a real app → update GuardianDAO record in DB here

        req.setAttribute("guardianNic", guardianNic);
        req.setAttribute("message", "Profile updated successfully (demo only)");
        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-profile.jsp").forward(req, resp);
    }
}
