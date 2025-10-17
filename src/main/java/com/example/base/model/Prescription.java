package com.example.base.model;

import java.time.LocalDateTime;

public class Prescription {
    private int id;
    private String patientNic;
    private String fileName;
    private String filePath;
    private LocalDateTime uploadDate;
    private String status;

    @Override
    public String toString() {
        return "Prescription{id=" + id + ", patientNic=" + patientNic
                + ", filePath=" + filePath + ", fileName=" + fileName + "}";
    }


    // Getters and Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getPatientNic() { return patientNic; }
    public void setPatientNic(String patientNic) { this.patientNic = patientNic; }

    public String getFileName() { return fileName; }
    public void setFileName(String fileName) { this.fileName = fileName; }

    public String getFilePath() { return filePath; }
    public void setFilePath(String filePath) { this.filePath = filePath; }

    public LocalDateTime getUploadDate() { return uploadDate; }
    public void setUploadDate(LocalDateTime uploadDate) { this.uploadDate = uploadDate; }

    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }
}