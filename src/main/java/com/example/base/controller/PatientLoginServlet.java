// com/example/base/controller/PatientLoginServlet.java
package com.example.base.controller;

import com.example.base.dao.patientDAO;
import com.example.base.model.patient;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;

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
            HttpSession session = request.getSession();
            session.setAttribute("patient", p);

            // Always include context path on redirects
            response.sendRedirect(request.getContextPath() + "/patient/dashboard");
        } else {
            request.setAttribute("error", "Invalid NIC or password.");
            // Forward back to the correct JSP path (absolute from context root)
            request.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(request, response);
        }
    }
}
