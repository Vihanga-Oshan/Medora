package com.example.base.controller.patient;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

@WebServlet("/patient/adherence-history")
public class AdherenceHistoryServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ JWT-based authentication check
        String role = (String) req.getAttribute("jwtRole");
        String patientNic = (String) req.getAttribute("jwtSub");

        if (role == null || !"patient".equals(role) || patientNic == null) {
            resp.sendRedirect(req.getContextPath() + "/login");
            return;
        }

        // ✅ Optionally attach NIC for use in JSP (e.g., personalized data)
        req.setAttribute("patientNic", patientNic);

        // ✅ Forward to the JSP
        req.getRequestDispatcher("/WEB-INF/views/patient/adherence-history.jsp").forward(req, resp);
    }
}
