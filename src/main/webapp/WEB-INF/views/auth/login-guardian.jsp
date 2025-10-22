<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login - Guardian</title>
  <link rel="stylesheet" href="${cp}/css/login/login-guardian.css"/>
</head>
<body class="login-page">

<div class="login-container">

  <!-- Back to Home -->
  <div class="back-link" onclick="window.location.href='${cp}/index.jsp'">← Back to Home</div>

  <!-- Logo -->
  <div class="logo">
    <img src="${cp}/assets/logo.png" alt="Medora Logo"/>
  </div>

  <!-- Heading -->
  <h1>Welcome Back</h1>
  <p class="subtitle">Login to manage your patients’ health with care</p>

  <!-- Toggle Buttons -->
  <div class="form-toggle">
    <button type="button" onclick="location.href='${cp}/login'">Patient</button>
    <button type="button" class="active">Guardian</button>
  </div>

  <!-- Show error message if login failed -->
  <c:if test="${not empty error}">
    <p class="error-text" style="color:#e11d48; margin-top:.75rem;">${error}</p>
  </c:if>

  <!-- Login Form -->
  <form method="post" action="${cp}/guardian/login" novalidate
        onsubmit="return window.loginValidate && window.loginValidate(this);">

    <label for="nic">NIC</label>
    <input type="text" id="nic" name="nic" value="${param.nic}" placeholder="Enter your NIC" required/>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Enter your password" required/>

    <div class="form-options">
      <label><input type="checkbox" name="remember"/> Remember Me</label>
      <a href="#" class="forgot-password">Forgot Password?</a>
    </div>

    <button type="submit" class="btn-submit">Login</button>
  </form>

  <p class="bottom-text">
    Don’t have an account? <a href="${cp}/guardian/register">Register here</a>
  </p>
</div>

<script src="${cp}/js/form-validation.js?v=1" defer></script>
</body>
</html>
