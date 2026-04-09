package com.example.base.model;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public class Notification {
    private int id;
    private String patientNic;
    private String message;
    private String type; // SYSTEM, PRESCRIPTION, MEDICATION, REMINDER
    private boolean isRead;
    private LocalDateTime date;

    public Notification() {
    }

    public Notification(int id, String patientNic, String message, String type, boolean isRead, LocalDateTime date) {
        this.id = id;
        this.patientNic = patientNic;
        this.message = message;
        this.type = type;
        this.isRead = isRead;
        this.date = date;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getPatientNic() {
        return patientNic;
    }

    public void setPatientNic(String patientNic) {
        this.patientNic = patientNic;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public boolean isRead() {
        return isRead;
    }

    public void setRead(boolean read) {
        isRead = read;
    }

    public LocalDateTime getDate() {
        return date;
    }

    public void setDate(LocalDateTime date) {
        this.date = date;
    }

    public String getFormattedDate() {
        if (date == null)
            return "";
        return date.format(DateTimeFormatter.ofPattern("dd MMM yyyy, hh:mm a"));
    }

    @Override
    public String toString() {
        return "Notification{" +
                "id=" + id +
                ", message='" + message + '\'' +
                ", type='" + type + '\'' +
                ", date=" + date +
                '}';
    }
}
