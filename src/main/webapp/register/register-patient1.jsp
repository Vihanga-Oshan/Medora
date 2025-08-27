<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 8/27/2025
  Time: 5:51 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Your Account - Medora</title>
  <link rel="stylesheet" href="../css/register/register-patient1.css" />
</head>
<body>
<header class="navbar">
  <div class="logo-brand">
    <img src="../assets/logo.png" alt="Medora Logo" class="logo" />
    <span class="small-brand">Medora</span>
  </div>
</header>

<div class="container">
  <div class="left-panel">
    <img src="../assets/register-patient1.jpg" alt="register image" class="register-image" />
  </div>

  <div class="right-panel">
    <div class="form-card">
      <div class="form-header">
        <img src="../assets/c-a-icon.png" alt="create account icon" class="create-acc-icon" />
        <h2>Create Account</h2>
        <div class="toggle-buttons">
          <button class="active">Register as Patient</button>
          <button class="not-active" onclick="window.location.href='register-guardian.jsp'">Register as Guardian</button>
        </div>
      </div>

      <form>
        <input type="text" placeholder="Full Name" required />
        <div class="gender-selection">
          <label><input type="radio" name="gender" checked /> Male</label>
          <label><input type="radio" name="gender" /> Female</label>
        </div>
        <input type="text" placeholder="Emergency Contact Number" required />
        <input type="text" placeholder="NIC" required />
        <input type="email" placeholder="Email" required />
        <input type="password" placeholder="Password" required />
        <input type="password" placeholder="Confirm Password" required />
       <button type="button" class="submit-btn" onclick="window.location.href='register-patient2.jsp'">Continue</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
