package com.example.base.dao;

import com.example.base.model.DosageForm;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class DosageFormDAO {

    public static List<DosageForm> getAll(Connection conn) throws SQLException {
        List<DosageForm> list = new ArrayList<>();
        String sql = "SELECT * FROM dosage_forms ORDER BY name ASC";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                DosageForm df = new DosageForm();
                df.setId(rs.getInt("id"));
                df.setName(rs.getString("name"));
                list.add(df);
            }
        }
        return list;
    }
}
