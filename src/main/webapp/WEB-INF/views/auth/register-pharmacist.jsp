<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html>
<head>
  <title>Register Pharmacist - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/index.css">
  <style>
    .form-container {
      background: white;
      border-radius: 12px;
      padding: 30px;
      max-width: 500px;
      margin: 60px auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .btn-submit { background: #007acc; color: white; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer; }
    .error { color: red; margin-top: 10px; }
    .back-link { display: inline-block; margin-top: 15px; color: #007acc; }
  </style>
</head>
<body>
<div class="form-container">
  <h2>Register Pharmacist</h2>
  <form action="${pageContext.request.contextPath}/register/pharmacist" method="post">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="form-group">
      <label for="confirmPassword">Confirm Password</label>
      <input type="password" id="confirmPassword" name="confirmPassword" required>
    </div>
    <button type="submit" class="btn-submit">Register</button>
    <a href="${pageContext.request.contextPath}/login/pharmacist" class="back-link">← Back to Login</a>
    <c:if test="${not empty error}">
      <p class="error">${error}</p>
    </c:if>
  </form>
</div>
</body>
</html>