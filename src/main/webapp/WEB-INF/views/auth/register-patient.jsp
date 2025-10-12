<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Patient Account - Medora</title>
  <link rel="stylesheet" href="${cp}/css/register/register-patient.css" />
  <!-- Optional: tiny error styles (if not already in your CSS) -->
  <style>
    .invalid{ outline:2px solid #e11d48; }
    .input-error{ color:#e11d48; font-size:.9rem; margin-top:4px; display:block; }
  </style>
</head>
<body>

<header class="navbar">
  <div class="logo-brand">
    <img src="${cp}/assets/logo.png" alt="Medora Logo" class="logo" />
    <span class="small-brand">Medora</span>
  </div>
</header>

<div class="container">
  <div class="left-panel">
    <img src="${cp}/assets/register-patient1.jpg" alt="register image" class="register-image" />
  </div>

  <div class="right-panel">
    <div class="form-card">
      <div class="form-header">
        <img src="${cp}/assets/c-a-icon.png" alt="create account icon" class="create-acc-icon" />
        <h2>Create Account</h2>
        <div class="toggle-buttons">
          <button class="active" type="button">Register as Patient</button>
          <button class="not-active" type="button" onclick="location.href='${cp}/register/guardian'">Register as Guardian</button>
        </div>
      </div>

      <!-- Show server error if present -->
      <c:if test="${not empty error}">
        <p style="color:#e11d48;margin:.5rem 0">${error}</p>
      </c:if>

      <!-- FORM -->
      <form
              method="post"
              action="${cp}/register/patient"
              id="patientForm"
              novalidate
              onsubmit="return window.patientValidate && window.patientValidate(this);"
      >
        <!-- STEP 1 -->
        <div id="step1">
          <div class="field">
            <input type="text" name="name" placeholder="Full Name" autocomplete="name"
                   required value="${param.name}"/>
          </div>

          <div class="gender-selection" style="margin:.5rem 0 1rem">
            <label><input type="radio" name="gender" value="Male"   ${param.gender == 'Male' ? 'checked' : ''}/> Male</label>
            <label><input type="radio" name="gender" value="Female" ${param.gender == 'Female' ? 'checked' : ''}/> Female</label>
          </div>

          <div class="field">
            <input type="text" name="emergencyContact" placeholder="Emergency Contact Number"
                   inputmode="tel" autocomplete="tel" required value="${param.emergencyContact}"/>
          </div>

          <div class="field">
            <input type="text" name="nic" placeholder="NIC" inputmode="numeric" autocomplete="off"
                   required value="${param.nic}"/>
          </div>

          <div class="field">
            <input type="email" name="email" placeholder="Email" autocomplete="email"
                   required value="${param.email}"/>
          </div>

          <div class="field">
            <input type="password" name="password" id="password" placeholder="Password"
                   autocomplete="new-password" required />
          </div>

          <div class="field">
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required />
          </div>

          <button type="button" class="submit-btn" onclick="nextStep()">Next</button>
        </div>

        <!-- STEP 2 -->
        <div id="step2" style="display:none;">
          <label for="allergies">If you have any Allergies:</label>
          <div class="field">
            <textarea id="allergies" name="allergies">${param.allergies}</textarea>
          </div>

          <label for="chronic">If you have any Chronic Issues:</label>
          <div class="field">
            <textarea id="chronic" name="chronic">${param.chronic}</textarea>
          </div>

          <label for="guardian">If you have a guardian:</label>
          <div class="field">
            <input type="text" id="guardian" name="guardianNic" placeholder="NIC of the Guardian" value="${param.guardianNic}"/>
          </div>

          <div class="checkbox-group field">
            <label>
              <input type="checkbox" id="privacy" name="privacy" required />
              I agree to the Privacy Policies
            </label>
          </div>

          <button type="button" class="submit-btn" onclick="previousStep()">Back</button>
          <button type="submit" class="submit-btn">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Load validator (cache-bust to avoid stale) -->
<script src="${cp}/js/form-validation.js?v=7" defer></script>

<!-- Step control: uses patientValidate(form, true) to gate step 1 -->
<script>
  function nextStep(){
    const form = document.getElementById('patientForm');
    if (window.patientValidate && window.patientValidate(form, true)) {
      document.getElementById('step1').style.display = 'none';
      document.getElementById('step2').style.display = '';
    }
  }
  function previousStep(){
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = '';
  }
</script>

</body>
</html>
