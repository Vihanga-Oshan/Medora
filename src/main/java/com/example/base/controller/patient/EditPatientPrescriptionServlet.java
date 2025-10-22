package com.example.base.controller.patient;

import com.example.base.dao.PrescriptionDAO;
import com.example.base.db.dbconnection;
import com.example.base.model.Prescription;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.Connection;

@WebServlet("/patient/edit-prescription")
public class EditPatientPrescriptionServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String idStr = req.getParameter("id");
        if (idStr == null) {
            resp.sendRedirect(req.getContextPath() + "/patient/prescriptions");
            return;
        }

        try (Connection conn = dbconnection.getConnection()) {
            int id = Integer.parseInt(idStr);
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            Prescription p = dao.getPrescriptionById(id);

            if (p == null) {
                resp.sendRedirect(req.getContextPath() + "/patient/prescriptions");
                return;
            }

            req.setAttribute("prescription", p);
            req.getRequestDispatcher("/WEB-INF/views/patient/edit-prescription.jsp").forward(req, resp);
        } catch (Exception e) {
            throw new ServletException(e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        int id = Integer.parseInt(req.getParameter("id"));
        String fileName = req.getParameter("fileName");

        try (Connection conn = dbconnection.getConnection()) {
            PrescriptionDAO dao = new PrescriptionDAO(conn);
            dao.updatePrescriptionName(id, fileName);
        } catch (Exception e) {
            throw new ServletException(e);
        }

        resp.sendRedirect(req.getContextPath() + "/patient/upload-prescription");

    }
}
