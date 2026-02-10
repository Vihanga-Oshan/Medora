<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
    <%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
      <!DOCTYPE html>
      <html lang="en">

      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Register Pharmacist - Medora</title>
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

          <h2>Create Pharmacist Account</h2>
          <p class="subtitle">Create an account to start validating prescriptions and scheduling medications.</p>

          <c:if test="${not empty error}">
            <p class="error-text">${error}</p>
          </c:if>

          <form id="regForm" action="${pageContext.request.contextPath}/pharmacist/register" method="post" novalidate>
            <div class="form-grid">
              <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" required
                  value="${fn:escapeXml(param.name)}">
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required
                  value="${fn:escapeXml(param.email)}">
              </div>

              <div class="form-group">
                <label for="pharmacistId">Pharmacist ID</label>
                <input type="text" id="pharmacistId" name="pharmacistId" class="form-input" pattern="\d+"
                  placeholder="Enter your pharmacy numeric ID" required value="${fn:escapeXml(param.pharmacistId)}">
              </div>

              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="6">
              </div>
              <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required
                  minlength="6">
              </div>

              <div class="button-row" style="margin-top: 20px;">
                <button type="submit" id="submitBtn" class="btn-auth-submit">Create account</button>
                <button type="button" onclick="location.href='${pageContext.request.contextPath}/pharmacist/login'"
                  class="btn-outline">Back to login</button>
              </div>
            </div>
          </form>
        </div>

        <script>
          (function () {
            const form = document.getElementById('regForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function (e) {
              if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
              }
              submitBtn.disabled = true;
              const orig = submitBtn.textContent;
              submitBtn.textContent = 'Creating...';
              setTimeout(() => { submitBtn.disabled = false; submitBtn.textContent = orig; }, 6000);
            });
          })();
        </script>
      </body>

      </html>