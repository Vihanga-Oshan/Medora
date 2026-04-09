package com.example.base.config;

import java.util.Arrays;
import java.util.HashSet;
import java.util.Set;

/**
 * Centralized route configuration for authentication and authorization.
 * Defines which routes are public (no auth required) vs protected.
 */
public class RouteConfig {

    // ========================================================================
    // PUBLIC ROUTES - No authentication required
    // ========================================================================
    private static final Set<String> PUBLIC_ROUTES = new HashSet<>(Arrays.asList(
            // Landing pages
            "/",
            "/index",
            "/home",

            // Authentication routes (login/register)
            "/login",
            "/register",
            "/patient/login",
            "/patient/register",
            "/pharmacist/login",
            "/pharmacist/register",
            "/admin/login",
            "/admin/register",
            "/guardian/login",
            "/guardian/register",
            "/logout"));

    // ========================================================================
    // STATIC RESOURCE PREFIXES - Always allowed
    // ========================================================================
    private static final Set<String> STATIC_PREFIXES = new HashSet<>(Arrays.asList(
            "/assets/",
            "/css/",
            "/js/",
            "/images/",
            "/icons/"));

    // ========================================================================
    // ROLE-SPECIFIC ROUTE PREFIXES
    // ========================================================================
    public static final String PATIENT_PREFIX = "/patient";
    public static final String PHARMACIST_PREFIX = "/pharmacist";
    public static final String ADMIN_PREFIX = "/admin";
    public static final String GUARDIAN_PREFIX = "/guardian";
    public static final String SHOP_PREFIX = "/shop";

    /**
     * Check if a route is public (no authentication required).
     */
    public static boolean isPublicRoute(String path) {
        if (path == null)
            return false;

        // Check exact match
        if (PUBLIC_ROUTES.contains(path)) {
            return true;
        }

        // Check static resource prefixes
        for (String prefix : STATIC_PREFIXES) {
            if (path.startsWith(prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a route is protected (authentication required).
     */
    public static boolean isProtectedRoute(String path) {
        return !isPublicRoute(path);
    }

    /**
     * Check if a route is a static resource.
     */
    public static boolean isStaticResource(String path) {
        if (path == null)
            return false;

        // ✅ NEVER treat prescription files as generic static resources
        // They require JWT authentication check
        if (path.startsWith("/prescriptionFile/")) {
            return false;
        }

        for (String prefix : STATIC_PREFIXES) {
            if (path.startsWith(prefix)) {
                return true;
            }
        }

        // Also check file extensions
        return path.endsWith(".css") ||
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

    /**
     * Get the required role for a given path based on its prefix.
     * 
     * @return role name ("patient", "pharmacist", "admin", "guardian") or null if
     *         no specific role required
     */
    public static String getRequiredRole(String path) {
        if (path == null)
            return null;

        if (path.startsWith(ADMIN_PREFIX))
            return "admin";
        if (path.startsWith(PHARMACIST_PREFIX))
            return "pharmacist";
        if (path.startsWith(PATIENT_PREFIX))
            return "patient";
        if (path.startsWith(GUARDIAN_PREFIX))
            return "guardian";
        if (path.startsWith(SHOP_PREFIX))
            return "patient";

        return null;
    }

    /**
     * Get the login redirect URL for a given path.
     */
    public static String getLoginRedirect(String path) {
        if (path == null)
            return "/login";

        if (path.startsWith(ADMIN_PREFIX))
            return "/admin/login";
        if (path.startsWith(PHARMACIST_PREFIX))
            return "/pharmacist/login";
        if (path.startsWith(GUARDIAN_PREFIX))
            return "/guardian/login";
        if (path.startsWith(SHOP_PREFIX))
            return "/patient/login";

        return "/login"; // Default to patient login
    }
}
