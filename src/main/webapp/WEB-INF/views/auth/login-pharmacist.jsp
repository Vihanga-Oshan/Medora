<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
  <title>Pharmacist Login - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/index.css">
  <style>
    .form-container {
      background: white;
      border-radius: 12px;
      padding: 30px;
      max-width: 400px;
      margin: 80px auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .btn-login { background: #007acc; color: white; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer; }
    .error { color: red; margin-top: 10px; }
    .success { color: green; margin-top: 10px; }
    .register-link { display: inline-block; margin-top: 15px; color: #007acc; }
  </style>
</head>
<body>
<div class="form-container">
  <h2>Pharmacist Login</h2>
  <form action="${pageContext.request.contextPath}/login/pharmacist" method="post">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn-login">Login</button>
    <a href="${pageContext.request.contextPath}/register/pharmacist" class="register-link">Register New Account</a>
    <c:if test="${not empty error}">
      <p class="error">${error}</p>
    </c:if>
    <c:if test="${not empty message}">
      <p class="success">${message}</p>
    </c:if>
  </form>
</div>
</body>
</html>