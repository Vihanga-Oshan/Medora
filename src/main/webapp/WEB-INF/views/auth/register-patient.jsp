<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account - Medora</title>
  <link rel="stylesheet" href="${cp}/css/register/register-patient.css" />

  <!-- Inline fallback styles for errors -->
  <style>
    .invalid { outline: 2px solid #e11d48; }
    .input-error { color: #e11d48; font-size: .9rem; margin-top: 4px; display: block; }
  </style>
</head>
<body>

<div class="register-box">

  <!-- Back to Home -->
  <div class="back-link" onclick="window.location.href='${cp}/index.jsp'">← Back to Home</div>

  <!-- Logo -->
  <div class="logo">
    <img src="${cp}/assets/logo.png" alt="Medora Logo" />
  </div>

  <!-- Title -->
  <h1>Create Account</h1>
  <p class="subtitle">Join Medora and never miss a dose again</p>

  <!-- Toggle Buttons -->
  <div class="toggle-btns">
    <button class="active" type="button">Patient</button>
    <button type="button" onclick="location.href='${cp}/guardian/register'">Guardian</button>
  </div>

  <!-- Show server error if present -->
  <c:if test="${not empty error}">
    <p class="error-text" style="color:#e11d48;margin:.5rem 0;">${error}</p>
  </c:if>

  <!-- Registration Form -->
  <form method="post" action="${cp}/register/patient" id="patientForm"
        novalidate onsubmit="return window.patientValidate && window.patientValidate(this);">

    <!-- STEP 1 -->
    <div id="step1" class="form active animate-in">
      <div class="form-grid">
        <div class="input-group full">
          <label>Full Name</label>
          <input type="text" name="name" autocomplete="name"
                 required value="${param.name}" />
        </div>

        <div class="input-group full gender-group">
          <label>Gender</label>
          <div class="gender-selection">
            <label><input type="radio" name="gender" value="Male" ${param.gender == 'Male' ? 'checked' : ''}/> Male</label>
            <label><input type="radio" name="gender" value="Female" ${param.gender == 'Female' ? 'checked' : ''}/> Female</label>
          </div>
        </div>

        <div class="input-group full">
          <label>Emergency Contact Number</label>
          <input type="text" name="emergencyContact" inputmode="tel"
                 autocomplete="tel" required value="${param.emergencyContact}" />
        </div>

        <div class="input-group full">
          <label>NIC</label>
          <input type="text" name="nic" inputmode="numeric"
                 autocomplete="off" required value="${param.nic}" />
        </div>

        <div class="input-group full">
          <label>Email</label>
          <input type="email" name="email" autocomplete="email"
                 required value="${param.email}" />
        </div>

        <div class="input-group full">
          <label>Password</label>
          <input type="password" name="password" id="password"
                 autocomplete="new-password" required />
        </div>

        <div class="input-group full">
          <label>Confirm Password</label>
          <input type="password" name="confirmPassword" id="confirmPassword" required />
        </div>

        <div class="input-group full">
          <button type="button" class="btn-submit" onclick="nextStep()">Next</button>
        </div>
      </div>
    </div>

    <!-- STEP 2 -->
    <div id="step2" class="form" style="display: none;">
      <div class="form-grid">
        <div class="input-group full">
          <label>If you have any Allergies</label>
          <textarea name="allergies">${param.allergies}</textarea>
        </div>

        <div class="input-group full">
          <label>If you have any Chronic Issues</label>
          <textarea name="chronic">${param.chronic}</textarea>
        </div>

        <div class="input-group full">
          <label>If you have a Guardian</label>
          <input type="text" name="guardianNic"
                 placeholder="NIC of the Guardian" value="${param.guardianNic}" />
        </div>

        <div class="checkbox full">
          <label>
            <input type="checkbox" id="privacy" name="privacy" required />
            I agree to the Privacy Policies
          </label>
        </div>

        <div class="input-group full button-row">
          <button type="button" class="btn-outline" onclick="previousStep()">← Back</button>
          <button type="submit" class="btn-submit">Create Account</button>
        </div>
      </div>
    </div>
  </form>

  <p class="login-text">
    Already have an account? <a href="${cp}/login">Login here</a>
  </p>

</div>

<script src="${cp}/js/form-validation.js?v=8" defer></script>
<script>
  function nextStep() {
    const form = document.getElementById('patientForm');
    if (window.patientValidate && window.patientValidate(form, true)) {
      document.getElementById('step1').classList.remove('animate-in');
      document.getElementById('step1').classList.add('animate-out');
      setTimeout(() => {
        document.getElementById('step1').style.display = 'none';
        const step2 = document.getElementById('step2');
        step2.style.display = 'block';
        step2.classList.add('animate-in');
      }, 300);
    }
  }

  function previousStep() {
    document.getElementById('step2').classList.remove('animate-in');
    document.getElementById('step2').classList.add('animate-out');
    setTimeout(() => {
      document.getElementById('step2').style.display = 'none';
      const step1 = document.getElementById('step1');
      step1.style.display = 'block';
      step1.classList.add('animate-in');
    }, 300);
  }
</script>
</body>
</html>
