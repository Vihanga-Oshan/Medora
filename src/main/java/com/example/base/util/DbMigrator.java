package com.example.base.util;

import com.example.base.config.DB;
import java.sql.Connection;
import java.sql.Statement;

public class DbMigrator {
    public static void main(String[] args) {
        String[] queries = {
                "CREATE TABLE IF NOT EXISTS supplier (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, contact_number VARCHAR(15), email VARCHAR(100) UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
                "ALTER TABLE chat_messages MODIFY COLUMN sender_type ENUM('patient', 'pharmacist', 'supplier') NOT NULL",
                "INSERT INTO supplier (name, contact_number, email) SELECT 'MediPharma Distributors', '0112345678', 'orders@medipharma.com' WHERE NOT EXISTS (SELECT * FROM supplier WHERE email = 'orders@medipharma.com')",
                "INSERT INTO supplier (name, contact_number, email) SELECT 'City Health Supplies', '0777123456', 'sales@cityhealth.lk' WHERE NOT EXISTS (SELECT * FROM supplier WHERE email = 'sales@cityhealth.lk')"
        };

        try (Connection conn = DB.getConnection()) {
            if (conn == null) {
                System.err.println("Failed to connect to database");
                System.exit(1);
            }
            Statement stmt = conn.createStatement();
            for (String sql : queries) {
                try {
                    stmt.execute(sql);
                    System.out.println("Executed: " + sql);
                } catch (Exception e) {
                    System.err.println("Error executing: " + sql + " - " + e.getMessage());
                }
            }
            System.out.println("Migration complete!");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
