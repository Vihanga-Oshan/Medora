package com.example.base.controller;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.util.List;

@WebServlet("/pharmacist/validate")
public class PrescriptionValidationServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

//        HttpSession session = req.getSession(false);
//        if (session == null || session.getAttribute("pharmacist") == null) {
//            resp.sendRedirect(req.getContextPath() + "/login/pharmacist");
//            return;
//        }

        try (Connection conn = com.example.base.db.dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            List<Prescription> prescriptions = dao.getPendingPrescriptions();
            req.setAttribute("prescriptions", prescriptions);
        } catch (Exception e) {
            e.printStackTrace();
        }

        req.getRequestDispatcher("/WEB-INF/views/pharmacist/prescription-validation.jsp").forward(req, resp);
    }
}