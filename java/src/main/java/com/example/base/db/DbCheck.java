package com.example.base.db;

import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.ResultSet;
import java.sql.SQLException;

public class DbCheck {
    public static void main(String[] args) {
        try (Connection conn = dbconnection.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Failed to connect to database.");
                return;
            }
            System.out.println("✅ Connected to database: " + conn.getMetaData().getURL());

            DatabaseMetaData meta = conn.getMetaData();
            ResultSet rs = meta.getTables(null, null, "%", new String[] { "TABLE" });
            System.out.println("Tables:");
            while (rs.next()) {
                System.out.println(" - " + rs.getString("TABLE_NAME"));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
