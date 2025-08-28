<%--
  Created by IntelliJ IDEA.
  User: User
  Date: 8/27/2025
  Time: 5:54 PM
  To change this template use File | Settings | File Templates.
--%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Medora - Guardian Registration</title>
    <link rel="stylesheet" href="../css/register/register-guardian.css" />
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="logo-brand">
        <img src="../assets/logo.png" alt="Medora Logo" class="logo" />
        <h1 class="brand-name">Medora</h1>
    </div>
</header>

<!-- Main Content -->
<main class="container">

    <!-- Left-side blank image space (same size as left page image) -->
    <div class="left-side">
        <img src="../assets/reg-guardian.webp"  class="guardian-logo" />
        <div class="image-placeholder"></div>
    </div>

    <!-- Right-side form content -->
    <div class="right-side">
        <form class="register-form" method="post" action="${pageContext.request.contextPath}/RegisterGuardianServlet">
            <img src="../assets/c-a-icon.png" alt="create account icon" class="create-acc-icon" />
            <h2>Create your account</h2>

            <div class="toggle-buttons">
                <button type="button" class="toggle" onclick="window.location.href='register-patient1.jsp'">Register as Patient</button>
                <button type="button" class="toggle active">Register as Guardian</button>
            </div>

            <input type="text" name="g_name" placeholder="User name" required />
            <input type="text" name="nic" placeholder="NIC" required />
            <input type="text" name="contact_number" placeholder="Contact Number" required />
            <input type="email" name="email" placeholder="Email" />
            <input type="password" name="password" placeholder="Password" required />

            <div class="checkbox">
                <input type="checkbox" id="agree" required />
                <label for="agree">I agree to the Privacy Policies</label>
            </div>

            <button type="submit" class="register-button">Register</button>
        </form>

    </div>

</main>

</body>
</html>

