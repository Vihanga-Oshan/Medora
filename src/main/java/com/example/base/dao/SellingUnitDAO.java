package com.example.base.dao;

import com.example.base.model.SellingUnit;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class SellingUnitDAO {

    public static List<SellingUnit> getAll(Connection conn) throws SQLException {
        List<SellingUnit> list = new ArrayList<>();
        String sql = "SELECT * FROM selling_units ORDER BY name ASC";

        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                SellingUnit su = new SellingUnit();
                su.setId(rs.getInt("id"));
                su.setName(rs.getString("name"));
                list.add(su);
            }
        }
        return list;
    }
}
