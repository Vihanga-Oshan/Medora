package com.example.base.model;

public class patient {
    private String nic;
    private String name;
    private String gender;
    private String emergencyContact;
    private String email;
    private String password;
    private String allergies;
    private String chronicIssues;
    private String guardianNic;

    // Getters and setters

    public String getNic() { return nic; }
    public void setNic(String nic) { this.nic = nic; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getGender() { return gender; }
    public void setGender(String gender) { this.gender = gender; }

    public String getEmergencyContact() { return emergencyContact; }
    public void setEmergencyContact(String emergencyContact) { this.emergencyContact = emergencyContact; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getPassword() { return password; }
    public void setPassword(String password) { this.password = password; }

    public String getAllergies() { return allergies; }
    public void setAllergies(String allergies) { this.allergies = allergies; }

    public String getChronicIssues() { return chronicIssues; }
    public void setChronicIssues(String chronicIssues) { this.chronicIssues = chronicIssues; }

    public String getGuardianNic() { return guardianNic; }
    public void setGuardianNic(String guardianNic) { this.guardianNic = guardianNic; }
}
