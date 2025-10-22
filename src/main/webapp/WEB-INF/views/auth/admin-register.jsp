<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/auth.css">
</head>
<body>
<div class="wrap">
  <div class="card login">
    <div class="brand">
      <div class="logo">M</div>
      <div class="title">Medora Admin Login</div>
    </div>
    <form method="post" action="${pageContext.request.contextPath}/admin/register">
      <label for="fullName">Full Name</label>
      <input type="text" id="fullName" name="fullName" placeholder="Enter full name" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Enter email address" required>

      <label for="nic">NIC</label>
      <input type="text" id="nic" name="nic" placeholder="Enter NIC number" required>

      <label for="contact">Contact Number</label>
      <input type="text" id="contact" name="contact" placeholder="Enter contact number" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>

      <label for="confirmPassword">Confirm Password</label>
      <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>

      <button type="submit" class="btn-auth">Register</button>
    </form>

    <p class="auth-footer">Already have an account? <a href="${pageContext.request.contextPath}/admin/login">Login</a></p>
  </div>
</div>
</body>
</html>
