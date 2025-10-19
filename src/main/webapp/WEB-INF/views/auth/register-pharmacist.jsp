<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register Pharmacist - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/index.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/auth.css">
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h2>Create Pharmacist Account</h2>
      <div class="lead">Create an account to start validating prescriptions and scheduling medications.</div>

      <c:if test="${not empty error}">
        <div class="msg error">${error}</div>
      </c:if>

      <form id="regForm" action="${pageContext.request.contextPath}/register/pharmacist" method="post" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="${fn:escapeXml(param.name)}">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="${fn:escapeXml(param.email)}">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="pharmacistId">Pharmacist ID</label>
            <input type="text" id="pharmacistId" name="pharmacistId" pattern="\d+" placeholder="Enter your pharmacy numeric ID" required value="${fn:escapeXml(param.pharmacistId)}">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="6">
          </div>
          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
          </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
          <button type="submit" id="submitBtn" class="btn-primary">Create account</button>
          <a href="${pageContext.request.contextPath}/pharmacist/login" class="btn-secondary">Back to login</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      const form = document.getElementById('regForm');
      const submitBtn = document.getElementById('submitBtn');

      form.addEventListener('submit', function(e){
        if (!form.checkValidity()){
          e.preventDefault();
          form.reportValidity();
          return;
        }
        submitBtn.disabled = true;
        const orig = submitBtn.textContent;
        submitBtn.textContent = 'Creating...';
        setTimeout(()=>{ submitBtn.disabled = false; submitBtn.textContent = orig; }, 6000);
      });
    })();
  </script>
</body>
</html>