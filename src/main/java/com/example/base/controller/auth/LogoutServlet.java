package com.example.base.controller.auth;

import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.logging.Logger;

@WebServlet("/logout")
public class LogoutServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(LogoutServlet.class.getName());

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);
        if (session != null) {
            session.invalidate();
        }

        // Identify which JWT cookie exists
        String role = detectRoleFromCookies(req.getCookies());

        // Remove all JWT cookies (defense-in-depth)
        clearCookie(resp, "JWT_PATIENT", req);
        clearCookie(resp, "JWT_PHARMACIST", req);
        clearCookie(resp, "JWT_ADMIN", req);
        clearCookie(resp, "JWT_GUARDIAN", req);
        clearCookie(resp, "JWT", req); // old fallback

        LOGGER.info("✅ Cleared all JWT cookies. Role detected: " + role);

        // Redirect based on role
        String redirectTo;
        switch (role) {
            case "pharmacist":
                redirectTo = req.getContextPath() + "/pharmacist/login";
                break;
            case "admin":
                redirectTo = req.getContextPath() + "/admin/login";
                break;
            case "guardian":
                redirectTo = req.getContextPath() + "/guardian/login";
                break;
            case "patient":
            default:
                redirectTo = req.getContextPath() + "/login";
                break;
        }

        resp.sendRedirect(redirectTo);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        doGet(req, resp);
    }

    // ------------------------------------------------------------------
    // 🔧 Helpers
    // ------------------------------------------------------------------

    private void clearCookie(HttpServletResponse resp, String name, HttpServletRequest req) {
        String[] paths = {"/", req.getContextPath(), "/pharmacist", "/admin", "/guardian", "/patient"};
        for (String p : paths) {
            if (p == null || p.isEmpty()) p = "/";
            Cookie cookie = new Cookie(name, "");
            cookie.setPath(p);
            cookie.setHttpOnly(true);
            cookie.setMaxAge(0);
            if (req.isSecure()) cookie.setSecure(true);
            resp.addCookie(cookie);
        }
    }

    private String detectRoleFromCookies(Cookie[] cookies) {
        if (cookies == null) return null;
        for (Cookie c : cookies) {
            switch (c.getName()) {
                case "JWT_PATIENT": return "patient";
                case "JWT_PHARMACIST": return "pharmacist";
                case "JWT_ADMIN": return "admin";
                case "JWT_GUARDIAN": return "guardian";
            }
        }
        return null;
    }
}
