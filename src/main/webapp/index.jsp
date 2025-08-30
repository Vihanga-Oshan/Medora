<%@ page contentType="text/html; charset=UTF-8" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Medora</title>
    <link rel="stylesheet" href="${cp}/index.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <div class="logo-brand">
                <img src="${cp}/assets/logo.png" alt="Medora Logo" class="logo"/>
                <span class="small-brand">Medora</span>
            </div>

            <div class="brand">
                <h1>
                    <span class="big-brand">Medora</span><br>
                    <span class="nevermiss">Never Miss a Dose Again.</span>
                </h1>
                <img src="${cp}/assets/pharmacy4.png" class="pharmacy" alt="pharmacyimg"/>
            </div>

            <span class="above-buttons">
        Medora is a medication reminder system designed to help you<br>
        or your loved ones manage medications effectively.
      </span>

            <div class="hero-buttons">
                <button class="login-btn" onclick="location.href='${cp}/register/patient'">Register</button>
                <button class="login-btn" onclick="location.href='${cp}/login'">Login</button>
            </div>
        </div>
    </div>
</section>

<section class="features"> ... </section>

<footer class="footer"> ... </footer>
</body>
</html>
