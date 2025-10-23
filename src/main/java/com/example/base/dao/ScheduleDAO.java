package com.example.base.dao;

import com.example.base.model.MedicationSchedule;

import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ScheduleDAO {
    private Connection conn;

    public ScheduleDAO(Connection conn) {
        this.conn = conn;
    }

    public List<MedicationSchedule> getMedicationByDate(String patientNic, LocalDate date) throws SQLException {
        List<MedicationSchedule> meds = new ArrayList<>();

        String sql = """
            SELECT 
                ms.id,
                med.name AS medicine_name,
                dc.label AS dosage,
                f.label AS frequency,
                mt.label AS meal_timing,
                ms.instructions,
                ms.start_date,
                ms.duration_days,
                COALESCE(ml.status, 'PENDING') AS status
            FROM medication_schedule ms
            JOIN schedule_master sm ON sm.id = ms.schedule_master_id
            JOIN medicines med ON med.id = ms.medicine_id
            JOIN dosage_categories dc ON dc.id = ms.dosage_id
            JOIN frequencies f ON f.id = ms.frequency_id
            LEFT JOIN meal_timing mt ON mt.id = ms.meal_timing_id
            LEFT JOIN medication_log ml 
                ON ml.medication_schedule_id = ms.id
                AND ml.dose_date = ?
                AND ml.patient_nic = sm.patient_nic
                AND ml.time_slot = f.label
            WHERE sm.patient_nic = ?
              AND ? BETWEEN ms.start_date AND DATE_ADD(ms.start_date, INTERVAL ms.duration_days - 1 DAY)
            ORDER BY 
                CASE 
                    WHEN f.label LIKE '%morning%' THEN 1
                    WHEN f.label LIKE '%day%' THEN 2
                    WHEN f.label LIKE '%afternoon%' THEN 3
                    WHEN f.label LIKE '%evening%' THEN 4
                    WHEN f.label LIKE '%night%' THEN 5
                    WHEN f.label LIKE '%bed%' THEN 6
                    ELSE 7
                END,
                med.name
        """;

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setDate(1, Date.valueOf(date));
            stmt.setString(2, patientNic);
            stmt.setDate(3, Date.valueOf(date));

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    String fullFreq = rs.getString("frequency");
                    String[] timesOfDay = fullFreq.split("&"); // e.g., "Morning & Night"

                    for (String time : timesOfDay) {
                        String timeSlot = time.trim(); // e.g., "Morning"

                        MedicationSchedule m = new MedicationSchedule();
                        m.setId(rs.getInt("id"));
                        m.setMedicineName(rs.getString("medicine_name"));
                        m.setDosage(rs.getString("dosage"));
                        m.setFrequency(timeSlot);
                        m.setMealTiming(rs.getString("meal_timing"));
                        m.setInstructions(rs.getString("instructions"));
                        m.setStartDate(rs.getDate("start_date").toLocalDate());
                        m.setDurationDays(rs.getInt("duration_days"));

                        // 🔍 Fetch status for this specific time slot
                        String statusQuery = """
            SELECT status FROM medication_log
            WHERE medication_schedule_id = ? 
              AND patient_nic = ? 
              AND dose_date = ? 
              AND time_slot = ?
        """;
                        try (PreparedStatement statusStmt = conn.prepareStatement(statusQuery)) {
                            statusStmt.setInt(1, rs.getInt("id"));
                            statusStmt.setString(2, patientNic);
                            statusStmt.setDate(3, Date.valueOf(date));
                            statusStmt.setString(4, timeSlot);

                            ResultSet statusRs = statusStmt.executeQuery();
                            if (statusRs.next()) {
                                m.setStatus(statusRs.getString("status"));
                            } else {
                                m.setStatus("PENDING");
                            }
                        }

                        meds.add(m);
                    }
                }

            }
        }

        meds.sort((a, b) -> getTimeOrder(a.getFrequency()) - getTimeOrder(b.getFrequency()));
        return meds;
    }

    private int getTimeOrder(String frequency) {
        if (frequency == null) return 99;
        String f = frequency.toLowerCase();
        if (f.contains("morning")) return 1;
        if (f.contains("day") || f.contains("afternoon")) return 2;
        if (f.contains("evening")) return 3;
        if (f.contains("night")) return 4;
        if (f.contains("bed")) return 5;
        return 99;
    }
    public boolean updateMedicationSchedule(int id, String dosage, String frequency,
                                            String mealTiming, String instructions,
                                            LocalDate startDate, int durationDays) throws SQLException {
        String sql = """
        UPDATE medication_schedule
        SET dosage_id = (SELECT id FROM dosage_categories WHERE label = ? LIMIT 1),
            frequency_id = (SELECT id FROM frequencies WHERE label = ? LIMIT 1),
            meal_timing_id = (SELECT id FROM meal_timing WHERE label = ? LIMIT 1),
            start_date = ?, 
            duration_days = ?, 
            instructions = ?
        WHERE id = ?
    """;

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, dosage);
            stmt.setString(2, frequency);
            stmt.setString(3, mealTiming);
            stmt.setDate(4, java.sql.Date.valueOf(startDate));
            stmt.setInt(5, durationDays);
            stmt.setString(6, instructions);
            stmt.setInt(7, id);
            return stmt.executeUpdate() > 0;
        }
    }
    public MedicationSchedule getScheduleById(int id) throws SQLException {
        String sql = """
        SELECT ms.id, med.name AS medicine_name, dc.label AS dosage,
               f.label AS frequency, mt.label AS meal_timing,
               ms.instructions, ms.start_date, ms.duration_days
        FROM medication_schedule ms
        JOIN medicines med ON med.id = ms.medicine_id
        JOIN dosage_categories dc ON dc.id = ms.dosage_id
        JOIN frequencies f ON f.id = ms.frequency_id
        LEFT JOIN meal_timing mt ON mt.id = ms.meal_timing_id
        WHERE ms.id = ?
    """;

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, id);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                MedicationSchedule m = new MedicationSchedule();
                m.setId(rs.getInt("id"));
                m.setMedicineName(rs.getString("medicine_name"));
                m.setDosage(rs.getString("dosage"));
                m.setFrequency(rs.getString("frequency"));
                m.setMealTiming(rs.getString("meal_timing"));
                m.setInstructions(rs.getString("instructions"));
                m.setStartDate(rs.getDate("start_date").toLocalDate());
                m.setDurationDays(rs.getInt("duration_days"));
                return m;
            }
        }
        return null;
    }

        public boolean deleteScheduleById(int id) {
            String sql = "DELETE FROM medication_schedule WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);
                int rowsAffected = stmt.executeUpdate();
                return rowsAffected > 0;
            } catch (SQLException e) {
                e.printStackTrace();
                return false;
            }
        }

}
