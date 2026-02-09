package com.example.base.model;

public class SellingUnit {
    private int id;
    private String name;

    public SellingUnit() {
    }

    public SellingUnit(int id, String name) {
        this.id = id;
        this.name = name;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }
}
