package com.example.base.config;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Database connection manager using external properties file.
 * Reads configuration from db.properties on the classpath.
 */
public class DB {
    private static final Logger LOGGER = Logger.getLogger(DB.class.getName());

    private static String url;
    private static String user;
    private static String password;
    private static boolean initialized = false;

    static {
        loadProperties();
    }

    private static void loadProperties() {
        try (InputStream input = DB.class.getClassLoader().getResourceAsStream("db.properties")) {
            if (input == null) {
                LOGGER.severe("Unable to find db.properties on classpath");
                return;
            }

            Properties props = new Properties();
            props.load(input);

            url = props.getProperty("db.url");
            user = props.getProperty("db.user");
            password = props.getProperty("db.pass");

            // Load MySQL driver
            Class.forName("com.mysql.cj.jdbc.Driver");
            initialized = true;

            LOGGER.info("Database configuration loaded successfully");
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Failed to load database properties", e);
        }
    }

    /**
     * Get a database connection using properties from db.properties.
     * 
     * @return Connection object, or null if connection fails
     */
    public static Connection getConnection() {
        if (!initialized) {
            LOGGER.severe("Database not initialized. Check db.properties file.");
            return null;
        }

        try {
            return DriverManager.getConnection(url, user, password);
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Failed to establish database connection", e);
            return null;
        }
    }

    /**
     * Test database connection - useful for health checks.
     */
    public static boolean testConnection() {
        try (Connection conn = getConnection()) {
            return conn != null && conn.isValid(5);
        } catch (SQLException e) {
            return false;
        }
    }

    public static void main(String[] args) {
        if (testConnection()) {
            System.out.println("✅ Connected to MySQL!");
        } else {
            System.out.println("❌ Connection failed");
        }
    }
}
