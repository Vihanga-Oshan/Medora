<%@ page contentType="text/html;charset=UTF-8" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pharmacist Login - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/index.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/auth.css">
</head>

<body>
<div class="wrap">
    <div class="card login" role="main" aria-labelledby="loginTitle">
        <h2 id="loginTitle">Pharmacist Login</h2>
        <p class="subtitle">Enter your ID and password to continue</p>

        <!-- Feedback messages -->
        <c:if test="${not empty error}">
            <div class="msg error">${error}</div>
        </c:if>
        <c:if test="${not empty message}">
            <div class="msg success">${message}</div>
        </c:if>

        <!-- Login Form -->
        <form id="loginForm" action="${pageContext.request.contextPath}/pharmacist/login" method="post" novalidate>
            <div class="form-group">
                <label for="pharmacistId">Pharmacist ID</label>
                <input type="text" id="pharmacistId" name="pharmacistId" placeholder="Enter your pharmacist ID" required />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required />
                    <button type="button" id="toggle"
                            style="position:absolute;right:8px;top:50%;transform:translateY(-50%);
                           background:none;border:none;color:#007dca;cursor:pointer;font-weight:600;">
                        Show
                    </button>
                </div>
            </div>

            <div style="margin-top:16px;">
                <button type="submit" id="submitBtn" class="btn-primary">Sign in</button>
            </div>
        </form>

        <!-- Helper links -->
        <div class="helper-links">
            <p style="margin:10px 0 0;text-align:center;">
                <a href="${pageContext.request.contextPath}/register/pharmacist" class="btn-secondary">
                    Create a pharmacist account
                </a>
            </p>
        </div>
    </div>
</div>

<script>
    (function(){
        const form = document.getElementById('loginForm');
        const pwd = document.getElementById('password');
        const toggle = document.getElementById('toggle');
        const submitBtn = document.getElementById('submitBtn');

        toggle.addEventListener('click', function(){
            if (pwd.type === 'password'){
                pwd.type = 'text';
                toggle.textContent = 'Hide';
                toggle.setAttribute('aria-label','Hide password');
            } else {
                pwd.type = 'password';
                toggle.textContent = 'Show';
                toggle.setAttribute('aria-label','Show password');
            }
        });

        form.addEventListener('submit', function(e){
            if (!form.checkValidity()){
                e.preventDefault();
                form.reportValidity();
                return;
            }
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Signing in...';
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }, 5000);
        });
    })();
</script>
</body>
</html>
