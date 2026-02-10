<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Admin Login - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/common.css" />
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/auth.css" />
  </head>

  <body class="auth-page">

    <div class="register-box">

      <!-- Back to Home -->
      <div class="back-link" onclick="window.location.href='${pageContext.request.contextPath}/index.jsp'">← Back to
        Home</div>

      <!-- Logo -->
      <div class="logo">
        <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo" />
      </div>

      <h2>Admin Registration</h2>
      <p class="subtitle">Join the administration team</p>

      <form method="post" action="${pageContext.request.contextPath}/admin/register">
        <div class="form-grid">
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" class="form-input" placeholder="Enter full name"
              required />
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="Enter email address" required />
          </div>

          <div class="form-group">
            <label for="nic">NIC Number</label>
            <input type="text" id="nic" name="nic" class="form-input" placeholder="Enter NIC number" required />
          </div>

          <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" class="form-input" placeholder="Enter contact number"
              required />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="Enter password"
              required />
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input"
              placeholder="Re-enter password" required />
          </div>

          <button type="submit" class="btn-auth-submit">Register</button>
        </div>
      </form>

      <p class="bottom-text">Already have an account? <a href="${pageContext.request.contextPath}/admin/login">Login</a>
      </p>
    </div>
  </body>

  </html>