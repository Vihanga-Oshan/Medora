package com.example.base.controller;

import com.example.base.controller.auth.AuthController;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
import java.io.IOException;
import java.util.logging.Logger;

/**
 * Central request dispatcher following NewPath architecture pattern.
 * All routes are handled through this single servlet.
 * 
 * This is loaded on startup (loadOnStartup=1) and mapped to "/" to intercept
 * all requests.
 */
@WebServlet(urlPatterns = "/router/*", loadOnStartup = 1)
@MultipartConfig(fileSizeThreshold = 1024 * 1024, // 1 MB
        maxFileSize = 1024 * 1024 * 10, // 10 MB
        maxRequestSize = 1024 * 1024 * 15 // 15 MB
)
public class RequestRouter extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(RequestRouter.class.getName());

    // Controllers - instantiated once and reused
    private AuthController authController;

    @Override
    public void init() throws ServletException {
        super.init();

        // Initialize all controllers
        authController = new AuthController();

        LOGGER.info("RequestRouter initialized with controllers");
    }

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        dispatch(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        dispatch(req, resp);
    }

    /**
     * Unified dispatch method for all HTTP methods.
     * Routes requests to appropriate controllers based on path.
     */
    private void dispatch(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String path = req.getRequestURI().substring(req.getContextPath().length());
        String method = req.getMethod();

        // Remove /router prefix if present (for transition period)
        if (path.startsWith("/router")) {
            path = path.substring("/router".length());
            if (path.isEmpty())
                path = "/";
        }

        LOGGER.info("RequestRouter dispatching: " + method + " " + path);

        // Skip static resources - let default servlet handle them
        if (isStaticResource(path)) {
            req.getRequestDispatcher(path).forward(req, resp);
            return;
        }

        // Route to appropriate controller
        switch (path) {
            // ================================================================
            // AUTH ROUTES (PUBLIC)
            // ================================================================
            case "/":
            case "/index":
            case "/home":
                forwardToJsp(req, resp, "/index.jsp");
                break;

            case "/login":
                if ("POST".equals(method)) {
                    authController.handlePatientLogin(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/login.jsp");
                }
                break;

            case "/register":
            case "/patient/register":
                if ("POST".equals(method)) {
                    authController.handlePatientRegister(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/register-patient.jsp");
                }
                break;

            case "/patient/login":
                if ("POST".equals(method)) {
                    authController.handlePatientLogin(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/login.jsp");
                }
                break;

            case "/pharmacist/login":
                if ("POST".equals(method)) {
                    authController.handlePharmacistLogin(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/pharmacist-login.jsp");
                }
                break;

            case "/pharmacist/register":
                if ("POST".equals(method)) {
                    authController.handlePharmacistRegister(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/pharmacist-register.jsp");
                }
                break;

            case "/admin/login":
                if ("POST".equals(method)) {
                    authController.handleAdminLogin(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/admin-login.jsp");
                }
                break;

            case "/admin/register":
                if ("POST".equals(method)) {
                    authController.handleAdminRegister(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/admin-register.jsp");
                }
                break;

            case "/guardian/login":
                if ("POST".equals(method)) {
                    authController.handleGuardianLogin(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/guardian-login.jsp");
                }
                break;

            case "/guardian/register":
                if ("POST".equals(method)) {
                    authController.handleGuardianRegister(req, resp);
                } else {
                    forwardToJsp(req, resp, "/WEB-INF/views/auth/guardian-register.jsp");
                }
                break;

            case "/logout":
                authController.handleLogout(req, resp);
                break;

            // ================================================================
            // DEFAULT: 404
            // ================================================================
            default:
                LOGGER.warning("No route found for: " + path);
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Route not found: " + path);
                break;
        }
    }

    /**
     * Forward request to a JSP view.
     */
    private void forwardToJsp(HttpServletRequest req, HttpServletResponse resp, String jspPath)
            throws ServletException, IOException {
        req.getRequestDispatcher(jspPath).forward(req, resp);
    }

    /**
     * Check if the path is a static resource.
     */
    private boolean isStaticResource(String path) {
        return path.startsWith("/css/") ||
                path.startsWith("/js/") ||
                path.startsWith("/assets/") ||
                path.startsWith("/images/") ||
                path.startsWith("/icons/") ||
                path.endsWith(".css") ||
                path.endsWith(".js") ||
                path.endsWith(".png") ||
                path.endsWith(".jpg") ||
                path.endsWith(".jpeg") ||
                path.endsWith(".gif") ||
                path.endsWith(".ico") ||
                path.endsWith(".svg") ||
                path.endsWith(".woff") ||
                path.endsWith(".woff2");
    }
}
