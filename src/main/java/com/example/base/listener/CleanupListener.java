package com.example.base.listener;

import com.mysql.cj.jdbc.AbandonedConnectionCleanupThread;
import javax.servlet.*;
import java.sql.*;
import java.util.Enumeration;

public class CleanupListener implements ServletContextListener {
    @Override public void contextInitialized(ServletContextEvent sce) {}

    @Override public void contextDestroyed(ServletContextEvent sce) {
        // Deregister JDBC drivers
        Enumeration<Driver> drivers = DriverManager.getDrivers();
        while (drivers.hasMoreElements()) {
            try { DriverManager.deregisterDriver(drivers.nextElement()); }
            catch (SQLException ignored) {}
        }

        // Stop MySQL cleanup thread
        AbandonedConnectionCleanupThread.checkedShutdown();
    }
}
