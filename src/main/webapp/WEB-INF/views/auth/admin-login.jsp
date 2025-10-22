<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/auth.css">
</head>
<body>
<div class="wrap">
    <div class="card login">
        <div class="brand">
            <div class="logo">M</div>
            <div class="title">Medora Admin Login</div>
        </div>

        <form action="${pageContext.request.contextPath}/admin/login" method="post">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" name="email" id="email" placeholder="Enter email" required>
            </div>

            <div class="form-group">
                <label for="password">Admin Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="helper-links">
            <p>Don't have an account?  <a href="${pageContext.request.contextPath}/admin/register">Login</a></p>
        </div>
    </div>
</div>
</body>
</html>
