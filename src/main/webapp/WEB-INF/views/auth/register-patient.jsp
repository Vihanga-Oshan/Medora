<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Patient Account - Medora</title>
  <!-- Use context path (no ../../../) -->
  <link rel="stylesheet" href="${cp}/css/register/register-patient.css" />
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
          <button class="active">Register as Patient</button>
          <!-- Link to a controller route for guardian (create it later) -->
          <button class="not-active" onclick="location.href='${cp}/register/guardian'">Register as Guardian</button>
        </div>
      </div>

      <!-- Show a global error if present -->
      <c:if test="${not empty error}">
        <p style="color:#e11d48;margin:.5rem 0">${error}</p>
      </c:if>

      <form method="post" action="${cp}/register/patient" id="patientForm">
        <!-- STEP 1 -->
        <div id="step1">
          <input type="text"   name="name"             placeholder="Full Name" required value="${param.name}"/>

          <div class="gender-selection">
            <label><input type="radio" name="gender" value="Male"   ${param.gender == 'Male' ? 'checked' : ''}/> Male</label>
            <label><input type="radio" name="gender" value="Female" ${param.gender == 'Female' ? 'checked' : ''}/> Female</label>
          </div>

          <input type="text"   name="emergencyContact" placeholder="Emergency Contact Number" required value="${param.emergencyContact}"/>
          <input type="text"   name="nic"              placeholder="NIC" required value="${param.nic}"/>
          <input type="email"  name="email"            placeholder="Email" required value="${param.email}"/>
          <input type="password" name="password"       id="password"         placeholder="Password" required />
          <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required />

          <button type="button" class="submit-btn" onclick="nextStep()">Next</button>
        </div>

        <!-- STEP 2 -->
        <div id="step2" style="display:none;">
          <label for="allergies">If you have any Allergies:</label>
          <textarea id="allergies" name="allergies">${param.allergies}</textarea>

          <label for="chronic">If you have any Chronic Issues:</label>
          <textarea id="chronic" name="chronic">${param.chronic}</textarea>

          <label for="guardian">If you have a guardian:</label>
          <input type="text" id="guardian" name="guardianNic" placeholder="NIC of the Guardian" value="${param.guardianNic}"/>

          <div class="checkbox-group">
            <input type="checkbox" id="privacy" name="privacy" required />
            <label for="privacy">I agree to the Privacy Policies</label>
          </div>

          <button type="button" class="submit-btn" onclick="previousStep()">Back</button>
          <button type="submit" class="submit-btn">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript logic -->
<script>
  function nextStep(){
    const pw = document.getElementById('password').value;
    const cpw = document.getElementById('confirmPassword').value;
    if (pw !== cpw) { alert('Passwords do not match'); return; }
    document.getElementById('step1').style.display='none';
    document.getElementById('step2').style.display='';
  }
  function previousStep(){
    document.getElementById('step2').style.display='none';
    document.getElementById('step1').style.display='';
  }
</script>
<!-- Or include your file via context path -->
<!-- <script src="${cp}/js/register-patient.js"></script> -->
</body>
</html>
