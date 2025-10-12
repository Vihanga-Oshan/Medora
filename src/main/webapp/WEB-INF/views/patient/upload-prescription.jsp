<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Prescription - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/index.css">
  <style>
    .upload-container {
      background: white;
      border-radius: 12px;
      border: 1px solid #011632;
      padding: 24px;
      max-width: 600px;
      margin: 40px auto;
    }
    .upload-header {
      color: #141414;
      font-family: "Inter-Bold", sans-serif;
      font-size: 24px;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      color: #141414;
      font-family: "Inter-Medium", sans-serif;
      font-size: 16px;
      margin-bottom: 8px;
    }
    .form-group input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }
    .btn-upload {
      background-color: #007acc;
      color: white;
      font-weight: bold;
      border: none;
      padding: 12px 24px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 16px;
      font-family: 'Lexend', sans-serif;
    }
    .btn-upload:hover {
      background-color: #005a99;
    }
    .error {
      color: #ff0000;
      font-family: "Inter-Medium", sans-serif;
      margin-top: 10px;
    }
    .back-link {
      color: #007acc;
      font-family: "Lexend-Medium", sans-serif;
      font-size: 16px;
      margin-top: 10px;
      display: inline-block;
    }
  </style>
</head>
<body>
<div class="upload-container">
  <div class="upload-header">Upload Prescription</div>
  <form action="${pageContext.request.contextPath}/upload-prescription" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="prescriptionFile">Choose File (PDF, JPG, PNG):</label>
      <input type="file" name="prescriptionFile" id="prescriptionFile" accept=".pdf,.jpg,.jpeg,.png" required>
    </div>
    <button type="submit" class="btn-upload">Upload Prescription</button>
    <a href="${pageContext.request.contextPath}/patient/dashboard" class="back-link">← Back to Dashboard</a>
  </form>
  <c:if test="${not empty error}">
    <p class="error">${error}</p>
  </c:if>
</div>
</body>
</html>