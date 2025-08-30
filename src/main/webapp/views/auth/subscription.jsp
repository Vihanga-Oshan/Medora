<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 8/27/2025
  Time: 6:01 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Medora Plans</title>
  <link rel="stylesheet" href="../../css/register/subscription.css" />
</head>
<body>
<header class="header">
  <div class="logo-brand">
    <img src="../../assets/logo.png" alt="Medora Logo" class="logo" />
    <span class="small-brand">Medora</span>
  </div>
  <h1>Our Plans For Medora</h1>
  <p>Choose what suits your Journey - Try Free , Then subscribe</p>
</header>

<section class="plans">
  <!-- Free Trial Plan -->
  <div class="plan selected">

    <h2>Free Trial</h2>
    <p class="price">Free</p>
    <p class="description">Get full access to Medora for 2 Days. Perfect for exploring the features before committing</p>
    <ul>
      <li>No credit card required</li>
      <li>Full Access to All Features</li>
      <li>Cancel anytime during trial</li>
      <li>Reminders and Guardian Alerts Enabled</li>
    </ul>
    <button class="register-btn">Register</button>
  </div>

  <!-- Standard Plan -->
  <div class="plan recommended">
    <div class="tag blue">Recommended</div>
    <h2>Standard</h2>
    <p class="price">$40 <span>/Year</span></p>
    <ul>
      <li>Unlimited Prescription Reminders</li>
      <li>Guardian Monitoring and Alerts</li>
      <li>In-App Notifications</li>
      <li>Real-time Adherence Tracking</li>
      <li>Medication Timetable Management</li>
    </ul>
    <button>Subscribe Now</button>
  </div>

  <!-- Premium Plan -->
  <div class="plan">
    <h2>Premium</h2>
    <p class="price">$20 <span>/6 Months</span></p>
    <ul>
      <li>Medication Timetable Management</li>
      <li>Guardian Monitoring and Alerts</li>
      <li>In-App Notifications</li>
      <li>Real-time Adherence Tracking</li>
      <li>Reminders and Guardian Alerts Enabled</li>
    </ul>
    <button>Subscribe Now</button>
  </div>
</section>
</body>
</html>

