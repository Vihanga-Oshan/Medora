<?php
/**
 * Admin Settings Model
 */
class SettingsModel
{
    public static function getAll(): array
    {
        // Many systems use a simple key-value table for settings
        $rs = Database::search("SELECT * FROM settings");
        $settings = [];
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $settings[$row['config_key']] = $row['config_value'];
            }
        }
        return $settings;
    }

    public static function update(string $key, $value): bool
    {
        $key = Database::$connection->real_escape_string($key);
        $value = Database::$connection->real_escape_string($value);
        
        // Check if exists
        $rs = Database::search("SELECT 1 FROM settings WHERE config_key = '$key'");
        if ($rs && $rs->num_rows > 0) {
            return Database::iud("UPDATE settings SET config_value = '$value' WHERE config_key = '$key'");
        } else {
            return Database::iud("INSERT INTO settings (config_key, config_value) VALUES ('$key', '$value')");
        }
    }
}
