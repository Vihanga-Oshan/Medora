package com.example.base.controller.routing;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.*;

@WebServlet("/guardian/patients")
public class GuardianPatientsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ✅ Simulated medication list for demo purposes
        List<Map<String, String>> medications = new ArrayList<>();

        medications.add(Map.of(
                "name", "Metformin",
                "dosage", "500mg",
                "time", "08:00 AM",
                "statusLabel", "Taken",
                "statusClass", "badge-success",
                "instructions", "Take with food"
        ));

        medications.add(Map.of(
                "name", "Lisinopril",
                "dosage", "10mg",
                "time", "12:00 PM",
                "statusLabel", "Upcoming",
                "statusClass", "badge-pending",
                "instructions", "Avoid heavy meals"
        ));

        medications.add(Map.of(
                "name", "Atorvastatin",
                "dosage", "20mg",
                "time", "08:00 PM",
                "statusLabel", "Missed",
                "statusClass", "badge-danger",
                "instructions", "Take with water"
        ));

        req.setAttribute("medications", medications);

        req.getRequestDispatcher("/WEB-INF/views/guardian/guardian-patients.jsp").forward(req, resp);
    }
}

