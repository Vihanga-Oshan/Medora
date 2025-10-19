package com.example.base.model;

public class Alert {
    private String patient;
    private String medication;
    private String severity; // HIGH, MEDIUM, LOW
    private String icon;
    private String timeAgo;
    private boolean isNew;
    private boolean isUnread;
    private boolean isNewAlert;

    // Constructors
    public Alert() {}

    public Alert(String patient, String medication, String severity, String icon, String timeAgo, boolean isNew, boolean isUnread) {
        this.patient = patient;
        this.medication = medication;
        this.severity = severity;
        this.icon = icon;
        this.timeAgo = timeAgo;
        this.isNew = isNew;
        this.isUnread = isUnread;
    }

    // Getters and Setters
    public String getPatient() {
        return patient;
    }

    public void setPatient(String patient) {
        this.patient = patient;
    }

    public String getMedication() {
        return medication;
    }

    public void setMedication(String medication) {
        this.medication = medication;
    }

    public String getSeverity() {
        return severity;
    }

    public void setSeverity(String severity) {
        this.severity = severity;
    }

    public String getIcon() {
        return icon;
    }

    public void setIcon(String icon) {
        this.icon = icon;
    }

    public String getTimeAgo() {
        return timeAgo;
    }

    public void setTimeAgo(String timeAgo) {
        this.timeAgo = timeAgo;
    }


    public boolean isNewAlert() {
        return isNewAlert;
    }

    public void setNewAlert(boolean newAlert) {
        isNewAlert = newAlert;
    }



    public boolean isUnread() {
        return isUnread;
    }

    public void setUnread(boolean unread) {
        isUnread = unread;
    }

    // Custom UI helpers
    public String getSeverityClass() {
        switch (severity.toUpperCase()) {
            case "HIGH": return "badge-high";
            case "MEDIUM": return "badge-medium";
            default: return "";
        }
    }

    public String getBgClass() {
        switch (severity.toUpperCase()) {
            case "HIGH": return "alert-high";
            case "MEDIUM": return "alert-medium";
            default: return "";
        }
    }
}
