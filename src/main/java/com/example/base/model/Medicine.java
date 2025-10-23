package com.example.base.model;


import java.sql.Date;


public class Medicine {
    private String name, genericName, category, description, dosageForm, strength, manufacturer;
    private int quantityInStock, addedBy;
    private Date expiryDate;

    private int id;

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    // Getters and setters
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }


    public String getGenericName() { return genericName; }
    public void setGenericName(String genericName) { this.genericName = genericName; }


    public String getCategory() { return category; }
    public void setCategory(String category) { this.category = category; }


    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }


    public String getDosageForm() { return dosageForm; }
    public void setDosageForm(String dosageForm) { this.dosageForm = dosageForm; }


    public String getStrength() { return strength; }
    public void setStrength(String strength) { this.strength = strength; }


    public String getManufacturer() { return manufacturer; }
    public void setManufacturer(String manufacturer) { this.manufacturer = manufacturer; }


    public int getQuantityInStock() { return quantityInStock; }
    public void setQuantityInStock(int quantityInStock) { this.quantityInStock = quantityInStock; }


    public int getAddedBy() { return addedBy; }
    public void setAddedBy(int addedBy) { this.addedBy = addedBy; }


    public Date getExpiryDate() { return expiryDate; }
    public void setExpiryDate(Date expiryDate) { this.expiryDate = expiryDate; }
}