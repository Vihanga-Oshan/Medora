// com/example/base/controller/PatientLoginServlet.java
package com.example.base.controller;

import com.example.base.dao.patientDAO;
import com.example.base.model.patient;
import com.example.base.auth.JwtUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.Map;

@WebServlet("/login")
public class PatientLoginServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // show the login page
        req.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(req, resp);
        // (Recommended: move to /WEB-INF/views/auth/login.jsp for security, see note below)
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String nic = request.getParameter("nic");
        String password = request.getParameter("password");

        patient p = patientDAO.validate(nic, password);

        if (p != null) {
            // create session for backward compatibility
            HttpSession session = request.getSession(true);
            session.setAttribute("patient", p);

            // create JWT and set as HttpOnly cookie
            String secret = request.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = request.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try { if (expiryStr != null) expiry = Long.parseLong(expiryStr); } catch (Exception ignored) {}
            try {
                String token = JwtUtil.createToken(secret != null ? secret : "change_this_secret_before_production_2025", p.getNic(), "patient", expiry);
                Cookie jwt = new Cookie("JWT", token);
                jwt.setHttpOnly(true);
                jwt.setPath(request.getContextPath().isEmpty() ? "/" : request.getContextPath());
                // Mark secure only if request is secure
                if (request.isSecure()) jwt.setSecure(true);
                // session cookie by default; if you want persistent token setMaxAge
                response.addCookie(jwt);
            } catch (Exception e) {
                // token creation failed; proceed with session only (still works)
                e.printStackTrace();
            }

            // Always include context path on redirects
            response.sendRedirect(request.getContextPath() + "/patient/dashboard");
        } else {
            request.setAttribute("error", "Invalid NIC or password.");
            // Forward back to the correct JSP path (absolute from context root)
            request.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(request, response);
        }
    }
}
