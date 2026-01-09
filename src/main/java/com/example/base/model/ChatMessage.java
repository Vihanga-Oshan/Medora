package com.example.base.model;

import java.sql.Timestamp;

public class ChatMessage {
    private int id;
    private String senderType; // 'patient' or 'pharmacist'
    private String senderId;
    private String receiverId;
    private String messageText;
    private Timestamp sentAt;
    private boolean isRead;

    public ChatMessage() {
    }

    public ChatMessage(String senderType, String senderId, String receiverId, String messageText) {
        this.senderType = senderType;
        this.senderId = senderId;
        this.receiverId = receiverId;
        this.messageText = messageText;
    }

    // Getters and Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getSenderType() {
        return senderType;
    }

    public void setSenderType(String senderType) {
        this.senderType = senderType;
    }

    public String getSenderId() {
        return senderId;
    }

    public void setSenderId(String senderId) {
        this.senderId = senderId;
    }

    public String getReceiverId() {
        return receiverId;
    }

    public void setReceiverId(String receiverId) {
        this.receiverId = receiverId;
    }

    public String getMessageText() {
        return messageText;
    }

    public void setMessageText(String messageText) {
        this.messageText = messageText;
    }

    public Timestamp getSentAt() {
        return sentAt;
    }

    public void setSentAt(Timestamp sentAt) {
        this.sentAt = sentAt;
    }

    public boolean isRead() {
        return isRead;
    }

    public void setRead(boolean read) {
        isRead = read;
    }
}
