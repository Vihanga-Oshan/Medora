<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 8/27/2025
  Time: 5:35 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Medora</title>
  <link rel="stylesheet" href="css/login/login-guardian.css" />
</head>
<body>

<header class="navbar">
  <div class="logo-brand">
    <img src="assets/logo.png" alt="Medora Logo" class="logo" />
    <span class="small-brand">Medora</span>
  </div>
</header>

<div class="container">

  <div class="left-panel">
    <img src="assets/login-guardian.jpg" alt="login image" class="register-image" />
  </div>

  <div class="right-panel">
    <div class="form-card">
      <div class="form-header">
        <img src="assets/welcome.png" alt="welcome icon" class="welcome-icon" />
        <h2>Welcome back</h2>
        <div class="toggle-buttons">
          <button class="not-active" onclick="window.location.href='login.jsp'">Login as Guardian</button>
          <button class="active" onclick="window.location.href='login-guardian.jsp'">Login as Guardian</button>
        </div>
      </div>

      <form>
        <input type="text" placeholder="Username" required />
        <input type="password" placeholder="Password" required />
        <button type="submit" class="submit-btn">Login</button>
        <div class="forgot-password">
          <a href="#">Forgot your password?</a>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>
