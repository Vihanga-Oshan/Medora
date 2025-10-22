package com.example.base.auth;

import javax.servlet.*;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.Map;
import java.util.logging.Logger;

public class JwtAuthFilter implements Filter {
    private String secret;
    private static final Logger LOGGER = Logger.getLogger(JwtAuthFilter.class.getName());

    @Override
    public void init(FilterConfig filterConfig) {
        secret = filterConfig.getServletContext().getInitParameter("jwt.secret");
        if (secret == null || secret.isEmpty()) {
            secret = "change_this_secret_before_production_2025";
            LOGGER.warning("JWT secret not configured; using default insecure secret.");
        }
    }

    @Override
    public void doFilter(ServletRequest servletRequest, ServletResponse servletResponse, FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest req = (HttpServletRequest) servletRequest;
        HttpServletResponse resp = (HttpServletResponse) servletResponse;
        String path = req.getRequestURI().substring(req.getContextPath().length());

        LOGGER.info("JwtAuthFilter checking path: " + path);

        // 1️⃣ Allow public endpoints (no authentication)
        if (isPublicPath(path)) {
            chain.doFilter(req, resp);
            return;
        }

        // 2️⃣ Determine which JWT cookie to check based on route prefix
        String cookieName = getRoleCookieName(path);

        // ✅ Special handling for shared resource routes (like /prescriptionFile)
        if (cookieName == null && path.startsWith("/prescriptionFile")) {
            // Try all known JWT cookies — whichever exists first will be used
            for (String candidate : new String[]{"JWT_PATIENT", "JWT_PHARMACIST", "JWT_ADMIN"}) {
                String candidateToken = getCookieValue(req, candidate);
                if (candidateToken != null && !candidateToken.isEmpty()) {
                    cookieName = candidate;
                    break;
                }
            }
        }

        if (cookieName == null) {
            resp.sendError(HttpServletResponse.SC_FORBIDDEN, "Unknown access path");
            return;
        }

        String token = getCookieValue(req, cookieName);
        if (token == null || token.isEmpty()) {
            redirectOrReject(req, resp);
            return;
        }

        // 3️⃣ Validate and decode JWT
        Map<String, String> claims = JwtUtil.validateToken(secret, token);
        if (claims == null || JwtUtil.isExpired(claims)) {
            LOGGER.warning("JWT expired or invalid for cookie " + cookieName);
            clearCookie(resp, cookieName, req.getContextPath());
            redirectOrReject(req, resp);
            return;
        }

        // 4️⃣ Role validation: ensure the role matches the route prefix
        String role = claims.get("role");
        if (!isAuthorizedPath(role, path) && !path.startsWith("/prescriptionFile")) {
            // skip path restriction for prescription files
            LOGGER.warning("Unauthorized access attempt by role=" + role + " for path=" + path);
            resp.sendError(HttpServletResponse.SC_FORBIDDEN);
            return;
        }

        // 5️⃣ Attach claims for downstream servlets
        req.setAttribute("jwtClaims", claims);
        req.setAttribute("jwtRole", role);
        req.setAttribute("jwtSub", claims.get("sub"));

        chain.doFilter(req, resp);
    }

    @Override
    public void destroy() {}

    // --------------------------------------------------------------------
    // 🔧 Helper Methods
    // --------------------------------------------------------------------

    private String getRoleCookieName(String path) {
        if (path.startsWith("/admin")) return "JWT_ADMIN";
        if (path.startsWith("/pharmacist")) return "JWT_PHARMACIST";
        if (path.startsWith("/patient")) return "JWT_PATIENT";
        if (path.startsWith("/guardian")) return "JWT_GUARDIAN";
        return null;
    }

    private String getCookieValue(HttpServletRequest req, String name) {
        Cookie[] cookies = req.getCookies();
        if (cookies != null) {
            for (Cookie c : cookies) {
                if (name.equals(c.getName())) return c.getValue();
            }
        }
        return null;
    }

    private void clearCookie(HttpServletResponse resp, String name, String contextPath) {
        Cookie cookie = new Cookie(name, "");
        cookie.setPath(contextPath.isEmpty() ? "/" : contextPath);
        cookie.setMaxAge(0);
        cookie.setHttpOnly(true);
        resp.addCookie(cookie);
    }

    private boolean isPublicPath(String path) {
        return path.equals("/") ||
                path.equals("/index") ||
                path.equals("/home") ||
                path.startsWith("/assets/") ||
                path.startsWith("/css/") ||
                path.startsWith("/js/") ||
                path.startsWith("/images/") ||
                // Public auth routes
                path.equals("/login") || path.equals("/register") ||
                path.equals("/patient/login") || path.equals("/patient/register") ||
                path.equals("/pharmacist/login") || path.equals("/pharmacist/register") ||
                path.equals("/admin/login") || path.equals("/admin/register") ||
                path.equals("/guardian/login") || path.equals("/guardian/register") ||
                path.equals("/logout");
    }

    private boolean isAuthorizedPath(String role, String path) {
        if (role == null) return false;
        switch (role) {
            case "admin": return path.startsWith("/admin");
            case "pharmacist": return path.startsWith("/pharmacist");
            case "patient": return path.startsWith("/patient");
            case "guardian": return path.startsWith("/guardian");
            default: return false;
        }
    }

    private void redirectOrReject(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        String path = req.getRequestURI().substring(req.getContextPath().length());
        boolean isApi = path.startsWith("/api/");

        if (isApi) {
            resp.sendError(HttpServletResponse.SC_UNAUTHORIZED);
        } else {
            String redirect = req.getContextPath() +
                    (path.startsWith("/admin") ? "/admin/login"
                            : path.startsWith("/pharmacist") ? "/pharmacist/login"
                            : path.startsWith("/guardian") ? "/guardian/login"
                            : "/login");
            resp.sendRedirect(redirect);
        }
    }
}
