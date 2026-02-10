<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Admin Login - Medora</title>
        <link rel="stylesheet" href="${pageContext.request.contextPath}/css/common.css" />
        <link rel="stylesheet" href="${pageContext.request.contextPath}/css/auth.css" />
    </head>

    <body class="auth-page">

        <div class="auth-container">

            <!-- Back to Home -->
            <div class="back-link" onclick="window.location.href='${pageContext.request.contextPath}/index.jsp'">← Back
                to Home</div>

            <!-- Logo -->
            <div class="logo">
                <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo" />
            </div>

            <h1>Admin Login</h1>
            <p class="subtitle">Access the management dashboard</p>

            <form action="${pageContext.request.contextPath}/admin/login" method="post">
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" name="email" id="email" class="form-input" placeholder="Enter email" required />
                </div>

                <div class="form-group">
                    <label for="password">Admin Password</label>
                    <input type="password" name="password" id="password" class="form-input" placeholder="Enter password"
                        required />
                </div>

                <button type="submit" class="btn-auth-submit">Login</button>
            </form>

            <div class="bottom-text">
                <p>Don't have an account? <a href="${pageContext.request.contextPath}/admin/register">Register</a></p>
            </div>
        </div>
    </body>

    </html>