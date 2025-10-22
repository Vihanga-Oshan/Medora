package com.example.base.dao;

import com.example.base.db.dbconnection;
import com.example.base.model.Pharmacist;
import com.example.base.util.PasswordUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

public class PharmacistDAO {
    private static final Logger LOGGER = Logger.getLogger(PharmacistDAO.class.getName());

    private final Connection conn;

    public PharmacistDAO(Connection conn) {
        this.conn = conn;
    }

    public void insertPharmacist(Pharmacist pharmacist) throws SQLException {
        String sql = "INSERT INTO pharmacist (id, name, email, password) VALUES (?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, pharmacist.getId());
            stmt.setString(2, pharmacist.getName());
            stmt.setString(3, pharmacist.getEmail());
            stmt.setString(4, PasswordUtil.hashPassword(pharmacist.getPassword()));
            stmt.executeUpdate();
        }
    }

    public static Pharmacist validate(String email, String password) {
        Pharmacist pharmacist = null;
        try (Connection conn = dbconnection.getConnection()) {
            String sql = "SELECT * FROM pharmacist WHERE email = ? AND password = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, email);
            stmt.setString(2, PasswordUtil.hashPassword(password));
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                pharmacist = new Pharmacist();
                pharmacist.setId(rs.getInt("id"));
                pharmacist.setName(rs.getString("name"));
                pharmacist.setEmail(rs.getString("email"));
                pharmacist.setPassword(rs.getString("password"));
            }
        } catch (Exception e) {
            LOGGER.log(Level.WARNING, "Error validating pharmacist credentials", e);
        }
        return pharmacist;}

        public static Pharmacist validateById ( int id, String password){
            Pharmacist pharmacist = null;
            try (Connection conn = dbconnection.getConnection()) {
                String sql = "SELECT * FROM pharmacist WHERE id = ? AND password = ?";
                PreparedStatement stmt = conn.prepareStatement(sql);
                stmt.setInt(1, id);
                stmt.setString(2, PasswordUtil.hashPassword(password));
                ResultSet rs = stmt.executeQuery();

                if (rs.next()) {
                    pharmacist = new Pharmacist();
                    pharmacist.setId(rs.getInt("id"));
                    pharmacist.setName(rs.getString("name"));
                    pharmacist.setEmail(rs.getString("email"));
                    pharmacist.setPassword(rs.getString("password"));
                }
            } catch (Exception e) {
                LOGGER.log(Level.WARNING, "Error validating pharmacist credentials by id", e);
            }
            return pharmacist;
        }

        public Pharmacist getPharmacistById ( int id) throws SQLException {
            String sql = "SELECT * FROM pharmacist WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        Pharmacist pharmacist = new Pharmacist();
                        pharmacist.setId(rs.getInt("id"));
                        pharmacist.setName(rs.getString("name"));
                        pharmacist.setEmail(rs.getString("email"));
                        pharmacist.setPassword(rs.getString("password"));
                        return pharmacist;
                    }
                }
            }
            return null;
        }

        public List<Pharmacist> getAllPharmacists () throws SQLException {
            List<Pharmacist> pharmacists = new ArrayList<>();
            String sql = "SELECT * FROM pharmacist ORDER BY created_at DESC";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    Pharmacist pharmacist = new Pharmacist();
                    pharmacist.setId(rs.getInt("id"));
                    pharmacist.setName(rs.getString("name"));
                    pharmacist.setEmail(rs.getString("email"));
                    pharmacist.setPassword(rs.getString("password"));
                    pharmacists.add(pharmacist);
                }
            }
            return pharmacists;
        }

        public void updatePharmacist(Pharmacist pharmacist) throws SQLException {
            String sql = "UPDATE pharmacist SET name = ?, email = ?, password = ? WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, pharmacist.getName());
                stmt.setString(2, pharmacist.getEmail());
                stmt.setString(3, PasswordUtil.hashPassword(pharmacist.getPassword()));
                stmt.setInt(4, pharmacist.getId());
                stmt.executeUpdate();
            }
        }

        public boolean deletePharmacist(int id) throws SQLException {
            String sql = "DELETE FROM pharmacist WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);
                stmt.executeUpdate();
            }
            return false;
        }

        public boolean idExists(int id) throws SQLException {
            String sql = "SELECT 1 FROM pharmacist WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);
                try (ResultSet rs = stmt.executeQuery()) {
                    return rs.next();
                }
            }
        }

        public boolean emailExists(String email) throws SQLException {
            String sql = "SELECT 1 FROM pharmacist WHERE email = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, email);
                try (ResultSet rs = stmt.executeQuery()) {
                    return rs.next();
                }
            }
        }

}