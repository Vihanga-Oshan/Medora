package com.example.base.auth;

import com.example.base.dao.patientDAO;
import com.example.base.model.patient;
import com.example.base.db.dbconnection;

import javax.servlet.*;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Connection;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

public class JwtAuthFilter implements Filter {
    private String secret;
    private static final Logger LOGGER = Logger.getLogger(JwtAuthFilter.class.getName());

    @Override
    public void init(FilterConfig filterConfig) {
        secret = filterConfig.getServletContext().getInitParameter("jwt.secret");
        if (secret == null || secret.isEmpty()) {
            secret = "change_this_secret_before_production_2025";
            LOGGER.warning("JWT secret not configured; using default insecure secret. Update web.xml context-param jwt.secret.");
        }
    }

    @Override
    public void doFilter(ServletRequest servletRequest, ServletResponse servletResponse, FilterChain chain) throws IOException, ServletException {
        HttpServletRequest req = (HttpServletRequest) servletRequest;
        HttpServletResponse resp = (HttpServletResponse) servletResponse;

        // Public endpoints: allow /login and /logout to pass through without JWT
        String path = req.getRequestURI().substring(req.getContextPath().length());
        LOGGER.info("JwtAuthFilter checking path: " + path);
        if (path.startsWith("/login") || path.startsWith("/logout") || path.startsWith("/pharmacist/login") || path.startsWith("/assets/") || path.startsWith("/css/") || path.startsWith("/js/")) {
            chain.doFilter(servletRequest, servletResponse);
            return;
        }

        String token = null;
        Cookie[] cookies = req.getCookies();
        if (cookies != null) {
            for (Cookie c : cookies) {
                if ("JWT".equals(c.getName())) {
                    token = c.getValue();
                    break;
                }
            }
        }
        LOGGER.info("JwtAuthFilter token present: " + (token != null));

        boolean verified = false;
        if (token != null && !token.isEmpty()) {
            Map<String, String> claims = JwtUtil.validateToken(secret, token);
            if (claims != null) {
                String sub = claims.get("sub");
                String role = claims.get("role");
                if (sub != null && role != null) {
                    try {
                        if ("patient".equals(role)) {
                            // Ensure patient is present in session and matches token subject
                            HttpSession session = req.getSession(false);
                            com.example.base.model.patient sessPatient = null;
                            if (session != null) {
                                Object obj = session.getAttribute("patient");
                                if (obj instanceof com.example.base.model.patient) {
                                    sessPatient = (com.example.base.model.patient) obj;
                                }
                            }

                            if (sessPatient != null && sub.equals(sessPatient.getNic())) {
                                verified = true;
                            } else {
                                // Load from DB and set session
                                try (Connection conn = dbconnection.getConnection()) {
                                    patientDAO dao = new patientDAO(conn);
                                    com.example.base.model.patient p = dao.getPatientByNIC(sub);
                                    if (p != null) {
                                        req.getSession(true).setAttribute("patient", p);
                                        verified = true;
                                    }
                                } catch (Exception e) {
                                    LOGGER.log(Level.WARNING, "Failed to load patient for NIC from DB", e);
                                }
                            }
                        } else if ("pharmacist".equals(role)) {
                            // Ensure pharmacist is present in session and matches token subject (pharmacist id)
                            HttpSession session = req.getSession(false);
                            com.example.base.model.Pharmacist sessPharm = null;
                            if (session != null) {
                                Object obj = session.getAttribute("pharmacist");
                                if (obj instanceof com.example.base.model.Pharmacist) {
                                    sessPharm = (com.example.base.model.Pharmacist) obj;
                                }
                            }

                            if (sessPharm != null && String.valueOf(sessPharm.getId()).equals(sub)) {
                                verified = true;
                            } else {
                                // Load pharmacist from DB using id in subject
                                try (Connection conn = dbconnection.getConnection()) {
                                    com.example.base.dao.PharmacistDAO phDao = new com.example.base.dao.PharmacistDAO(conn);
                                    int pid = Integer.parseInt(sub);
                                    com.example.base.model.Pharmacist pharm = phDao.getPharmacistById(pid);
                                    if (pharm != null) {
                                        req.getSession(true).setAttribute("pharmacist", pharm);
                                        verified = true;
                                    }
                                } catch (Exception e) {
                                    LOGGER.log(Level.WARNING, "Failed to load pharmacist for id from DB", e);
                                }
                            }
                        } else {
                            // Unknown role — do not verify
                            LOGGER.fine("JWT contains unknown role: " + role);
                        }
                    } catch (Exception e) {
                        LOGGER.log(Level.WARNING, "Error while processing JWT claims", e);
                    }
                }
            }
        }

        if (!verified) {
            // Not authenticated - redirect to appropriate login page
            String loginRedirect = req.getContextPath() + (path.startsWith("/pharmacist") ? "/pharmacist/login" : "/login");
            resp.sendRedirect(loginRedirect);
            return;
        }

        chain.doFilter(servletRequest, servletResponse);
    }

    @Override
    public void destroy() { }
}
