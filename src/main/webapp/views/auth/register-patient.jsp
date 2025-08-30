<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Patient Account - Medora</title>
  <link rel="stylesheet" href="../../css/register/register-patient.css" />
</head>
<body>

<header class="navbar">
  <div class="logo-brand">
    <img src="../../assets/logo.png" alt="Medora Logo" class="logo" />
    <span class="small-brand">Medora</span>
  </div>
</header>

<div class="container">
  <div class="left-panel">
    <img src="../../assets/register-patient1.jpg" alt="register image" class="register-image" />
  </div>

  <div class="right-panel">
    <div class="form-card">
      <div class="form-header">
        <img src="../../assets/c-a-icon.png" alt="create account icon" class="create-acc-icon" />
        <h2>Create Account</h2>
        <div class="toggle-buttons">
          <button class="active">Register as Patient</button>
          <button class="not-active" onclick="window.location.href='register-guardian.jsp'">Register as Guardian</button>
        </div>
      </div>

      <form method="post" action="${pageContext.request.contextPath}/RegisterPatientServlet" id="patientForm">
        <!-- STEP 1 -->
        <div id="step1">
          <input type="text" name="name" placeholder="Full Name" required />

          <div class="gender-selection">
            <label><input type="radio" name="gender" value="Male" checked /> Male</label>
            <label><input type="radio" name="gender" value="Female" /> Female</label>
          </div>

          <input type="text" name="emergencyContact" placeholder="Emergency Contact Number" required />
          <input type="text" name="nic" placeholder="NIC" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" id="password" placeholder="Password" required />
          <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required />

          <button type="button" class="submit-btn" onclick="nextStep()">Next</button>
        </div>

        <!-- STEP 2 -->
        <div id="step2" style="display: none;">
          <label for="allergies">If you have any Allergies:</label>
          <textarea id="allergies" name="allergies" placeholder=""></textarea>

          <label for="chronic">If you have any Chronic Issues:</label>
          <textarea id="chronic" name="chronic" placeholder=""></textarea>

          <label for="guardian">If you have a guardian:</label>
          <input type="text" id="guardian" name="guardianNic" placeholder="NIC of the Guardian" />

          <div class="checkbox-group">
            <input type="checkbox" id="privacy" required />
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
<script src="../../js/register-patient.js"></script>

</body>
</html>
