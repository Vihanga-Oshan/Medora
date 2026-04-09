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
        // Fallback defaults
        url = "jdbc:postgresql://ep-example-123456.us-east-2.aws.neon.tech/neondb?sslmode=require";
        user = "neondb_owner";
        password = "your_password_here";
        initialized = true;

        try (InputStream input = DB.class.getClassLoader().getResourceAsStream("db.properties")) {
            if (input != null) {
                Properties props = new Properties();
                props.load(input);

                if (props.getProperty("db.url") != null)
                    url = props.getProperty("db.url");
                if (props.getProperty("db.user") != null)
                    user = props.getProperty("db.user");
                if (props.getProperty("db.pass") != null)
                    password = props.getProperty("db.pass");

                LOGGER.info("Database configuration loaded from db.properties");
            } else {
                LOGGER.warning("db.properties not found, using hardcoded defaults");
            }

            // Load PostgreSQL driver
            Class.forName("org.postgresql.Driver");
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Error during database initialization", e);
        }
    }

    /**
     * Get a database connection using properties from db.properties.
     * 
     * @return Connection object, or null if connection fails
     */
    public static Connection getConnection() throws SQLException {
        if (!initialized) {
            throw new SQLException("Database not initialized. Check db.properties file.");
        }
        return DriverManager.getConnection(url, user, password);
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
            System.out.println("✅ Connected to PostgreSQL (Neon)!");
        } else {
            System.out.println("❌ Connection failed");
        }
    }
}
