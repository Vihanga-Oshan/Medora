<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 8/27/2025
  Time: 5:58 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Medical Information</title>
  <link rel="stylesheet" href="../css/register/register-patient2.css"/>
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
    <img src="../assets/register-patient2.cms" alt="Nurse" class="form-image" />
  </div>

  <div class="right-panel">
    <div class="form-card">
      <img src="../assets/medi-info.png" alt="medical info icon" class="medical-info-icon" />
      <h2 class="form-title">Medical Information</h2>
      <form>
        <label for="allergies">If you have any Allergies:</label>
        <textarea id="allergies" placeholder=""></textarea>

        <label for="chronic">If you have any Chronic Issues:</label>
        <textarea id="chronic" placeholder=""></textarea>

        <label for="guardian">If you have a guardian:</label>
        <input type="text" id="guardian" placeholder="NIC of the Guardian" />

        <div class="checkbox-group">
          <input type="checkbox" id="privacy" required />
          <label for="privacy">I agree to the Privacy Policies</label>
        </div>

        <button type="submit" class="submit-btn" formaction="subscription.jsp">Continue</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
