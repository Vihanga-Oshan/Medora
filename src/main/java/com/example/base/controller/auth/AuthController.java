package com.example.base.controller.auth;

import com.example.base.auth.JwtUtil;
import com.example.base.config.DB;
import com.example.base.dao.AdminDAO;
import com.example.base.dao.GuardianDAO;
import com.example.base.dao.PharmacistDAO;
import com.example.base.dao.patientDAO;
import com.example.base.model.Admin;
import com.example.base.model.Guardian;
import com.example.base.model.Pharmacist;
import com.example.base.model.patient;

import javax.servlet.ServletException;
import javax.servlet.http.*;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLIntegrityConstraintViolationException;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Authentication Controller - handles login/register for all roles.
 * This is a POJO controller (not a Servlet) called by RequestRouter.
 */
public class AuthController {
    private static final Logger LOGGER = Logger.getLogger(AuthController.class.getName());

    // ========================================================================
    // PATIENT AUTH
    // ========================================================================

    public void handlePatientLogin(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String nic = req.getParameter("nic");
        String password = req.getParameter("password");
        LOGGER.info("Patient login attempt for NIC=" + nic);

        try {
            patient p = patientDAO.validate(nic, password);

            if (p == null) {
                req.setAttribute("error", "Invalid NIC or password.");
                req.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(req, resp);
                return;
            }

            // Create JWT
            String secret = req.getServletContext().getInitParameter("jwt.secret");
            String expiryStr = req.getServletContext().getInitParameter("jwt.expirySeconds");
            long expiry = 3600L;
            try {
                if (expiryStr != null)
                    expiry = Long.parseLong(expiryStr);
            } catch (Exception ignored) {
            }

            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    p.getNic(),
                    "patient",
                    expiry);

            // Set JWT cookie
            Cookie jwt = new Cookie("JWT_PATIENT", token);
            jwt.setHttpOnly(true);
            jwt.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
            jwt.setMaxAge((int) expiry);
            if (req.isSecure())
                jwt.setSecure(true);
            resp.addCookie(jwt);

            LOGGER.info("✅ JWT_PATIENT cookie set for NIC=" + nic);

            // Optional: session for compatibility
            HttpSession session = req.getSession(true);
            session.setAttribute("patient", p);
            session.setAttribute("currentUser", p);
            session.setAttribute("userRole", "patient");

            String redirect = req.getParameter("redirect");
            if (redirect != null && !redirect.isEmpty()) {
                resp.sendRedirect(req.getContextPath() + redirect);
            } else {
                resp.sendRedirect(req.getContextPath() + "/patient/dashboard");
            }

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during patient login", e);
            req.setAttribute("error", "Login failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/login.jsp").forward(req, resp);
        }
    }

    public void handlePatientRegister(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        req.setCharacterEncoding("UTF-8");

        String name = req.getParameter("name");
        String gender = req.getParameter("gender");
        String emergencyContact = req.getParameter("emergencyContact");
        String nic = req.getParameter("nic");
        String email = req.getParameter("email");
        String password = req.getParameter("password");
        String confirmPassword = req.getParameter("confirmPassword");
        String allergies = req.getParameter("allergies");
        String chronic = req.getParameter("chronic");
        String guardianNic = req.getParameter("guardianNic");

        // Validation
        if (name == null || name.isBlank() || nic == null || nic.isBlank() ||
                password == null || password.isBlank() || confirmPassword == null || confirmPassword.isBlank()) {
            req.setAttribute("error", "Please fill all required fields.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(req, resp);
            return;
        }
        if (!password.equals(confirmPassword)) {
            req.setAttribute("error", "Passwords do not match!");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(req, resp);
            return;
        }

        // Build model
        patient p = new patient();
        p.setName(name);
        p.setGender(gender);
        p.setEmergencyContact(emergencyContact);
        p.setNic(nic);
        p.setEmail(email);
        p.setPassword(password);
        p.setAllergies(allergies);
        p.setChronicIssues(chronic);
        p.setGuardianNic(guardianNic);

        try (Connection conn = DB.getConnection()) {
            patientDAO dao = new patientDAO(conn);
            dao.insertPatient(p);
            resp.sendRedirect(req.getContextPath() + "/login");
        } catch (SQLIntegrityConstraintViolationException dup) {
            req.setAttribute("error", "This NIC is already registered.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(req, resp);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during patient registration", e);
            req.setAttribute("error", "Something went wrong. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/register-patient.jsp").forward(req, resp);
        }
    }

    // ========================================================================
    // PHARMACIST AUTH
    // ========================================================================

    public void handlePharmacistLogin(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String email = req.getParameter("email");
        String password = req.getParameter("password");
        LOGGER.info("Pharmacist login attempt for email=" + email);

        try {
            // PharmacistDAO.validate is a static method
            Pharmacist pharmacist = PharmacistDAO.validate(email, password);

            if (pharmacist == null) {
                req.setAttribute("error", "Invalid email or password.");
                req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
                return;
            }

            // Create JWT
            String secret = req.getServletContext().getInitParameter("jwt.secret");
            long expiry = 3600L;

            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    String.valueOf(pharmacist.getId()),
                    "pharmacist",
                    expiry);

            Cookie jwt = new Cookie("JWT_PHARMACIST", token);
            jwt.setHttpOnly(true);
            jwt.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
            jwt.setMaxAge((int) expiry);
            resp.addCookie(jwt);

            HttpSession session = req.getSession(true);
            session.setAttribute("pharmacist", pharmacist);
            session.setAttribute("currentUser", pharmacist);
            session.setAttribute("userRole", "pharmacist");

            resp.sendRedirect(req.getContextPath() + "/pharmacist/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during pharmacist login", e);
            req.setAttribute("error", "Login failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-login.jsp").forward(req, resp);
        }
    }

    public void handlePharmacistRegister(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // Pharmacist registration logic - delegating to existing flow for now
        req.setAttribute("error", "Registration via RequestRouter not yet implemented.");
        req.getRequestDispatcher("/WEB-INF/views/auth/pharmacist-register.jsp").forward(req, resp);
    }

    // ========================================================================
    // ADMIN AUTH
    // ========================================================================

    public void handleAdminLogin(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String adminId = req.getParameter("adminId");
        String password = req.getParameter("password");
        LOGGER.info("Admin login attempt for ID=" + adminId);

        try (Connection conn = DB.getConnection()) {
            AdminDAO dao = new AdminDAO(conn);
            Admin admin = dao.validate(adminId, password);

            if (admin == null) {
                req.setAttribute("error", "Invalid credentials.");
                req.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(req, resp);
                return;
            }

            String secret = req.getServletContext().getInitParameter("jwt.secret");
            long expiry = 3600L;

            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    adminId,
                    "admin",
                    expiry);

            Cookie jwt = new Cookie("JWT_ADMIN", token);
            jwt.setHttpOnly(true);
            jwt.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
            jwt.setMaxAge((int) expiry);
            resp.addCookie(jwt);

            HttpSession session = req.getSession(true);
            session.setAttribute("admin", admin);
            session.setAttribute("currentUser", admin);
            session.setAttribute("userRole", "admin");

            resp.sendRedirect(req.getContextPath() + "/admin/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during admin login", e);
            req.setAttribute("error", "Login failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/admin-login.jsp").forward(req, resp);
        }
    }

    public void handleAdminRegister(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.setAttribute("error", "Registration via RequestRouter not yet implemented.");
        req.getRequestDispatcher("/WEB-INF/views/auth/admin-register.jsp").forward(req, resp);
    }

    // ========================================================================
    // GUARDIAN AUTH
    // ========================================================================

    public void handleGuardianLogin(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String guardianNic = req.getParameter("nic");
        String password = req.getParameter("password");
        LOGGER.info("Guardian login attempt for NIC=" + guardianNic);

        try {
            // GuardianDAO.validate is a static method
            Guardian guardian = GuardianDAO.validate(guardianNic, password);

            if (guardian == null) {
                req.setAttribute("error", "Invalid credentials.");
                req.getRequestDispatcher("/WEB-INF/views/auth/guardian-login.jsp").forward(req, resp);
                return;
            }

            String secret = req.getServletContext().getInitParameter("jwt.secret");
            long expiry = 3600L;

            String token = JwtUtil.createToken(
                    secret != null ? secret : "change_this_secret_before_production_2025",
                    guardianNic,
                    "guardian",
                    expiry);

            Cookie jwt = new Cookie("JWT_GUARDIAN", token);
            jwt.setHttpOnly(true);
            jwt.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
            jwt.setMaxAge((int) expiry);
            resp.addCookie(jwt);

            HttpSession session = req.getSession(true);
            session.setAttribute("guardian", guardian);
            session.setAttribute("currentUser", guardian);
            session.setAttribute("userRole", "guardian");

            resp.sendRedirect(req.getContextPath() + "/guardian/dashboard");

        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during guardian login", e);
            req.setAttribute("error", "Login failed. Please try again.");
            req.getRequestDispatcher("/WEB-INF/views/auth/guardian-login.jsp").forward(req, resp);
        }
    }

    public void handleGuardianRegister(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.setAttribute("error", "Registration via RequestRouter not yet implemented.");
        req.getRequestDispatcher("/WEB-INF/views/auth/guardian-register.jsp").forward(req, resp);
    }

    // ========================================================================
    // LOGOUT
    // ========================================================================

    public void handleLogout(HttpServletRequest req, HttpServletResponse resp)
            throws IOException {

        // Get current role from session to determine redirect
        HttpSession session = req.getSession(false);
        String userRole = session != null ? (String) session.getAttribute("userRole") : null;

        // Clear all JWT cookies
        for (String cookieName : new String[] { "JWT_PATIENT", "JWT_PHARMACIST", "JWT_ADMIN", "JWT_GUARDIAN" }) {
            Cookie cookie = new Cookie(cookieName, "");
            cookie.setPath(req.getContextPath().isEmpty() ? "/" : req.getContextPath());
            cookie.setMaxAge(0);
            cookie.setHttpOnly(true);
            resp.addCookie(cookie);
        }

        // Invalidate session
        if (session != null) {
            session.invalidate();
        }

        // Redirect based on role
        String redirectPath;
        if ("admin".equals(userRole)) {
            redirectPath = "/admin/login";
        } else if ("pharmacist".equals(userRole)) {
            redirectPath = "/pharmacist/login";
        } else if ("guardian".equals(userRole)) {
            redirectPath = "/guardian/login";
        } else {
            redirectPath = "/login";
        }

        resp.sendRedirect(req.getContextPath() + redirectPath);
    }
}
