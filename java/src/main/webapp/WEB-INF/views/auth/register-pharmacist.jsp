<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
    <%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
      <c:set var="cp" value="${pageContext.request.contextPath}" />

      <!DOCTYPE html>
      <html lang="en">

      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Pharmacist Registration - Medora</title>

        <!-- Design Tokens and Base Styles -->
        <link rel="stylesheet" href="${cp}/css/common.css" />
        <link rel="stylesheet" href="${cp}/css/auth.css" />

        <!-- Custom Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

        <style>
          .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            text-align: left;
          }

          .full-width {
            grid-column: span 2;
          }

          @media (max-width: 600px) {
            .form-grid {
              grid-template-columns: 1fr;
            }

            .full-width {
              grid-column: span 1;
            }
          }
        </style>
      </head>

      <body class="auth-page">
        <!-- Decorative Blobs from common.css -->
        <div class="decorative-blob blob-1"></div>
        <div class="decorative-blob blob-2"></div>
        <div class="decorative-blob blob-3"></div>

        <div class="register-box animate-in">
          <!-- Back to Home -->
          <a href="${cp}/index.jsp" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Home
          </a>

          <!-- Logo -->
          <div class="logo">
            <img src="${cp}/assets/logo.png" alt="Medora Logo" />
            <span class="brand-text">Pharmacist Registration</span>
          </div>

          <p class="subtitle">Join our healthcare network and help save lives.</p>

          <!-- Registration Form -->
          <form id="regForm" action="${cp}/pharmacist/register" method="post" novalidate>
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="name">Doctor / Pharmacist Name</label>
                <div class="input-wrapper">
                  <input type="text" id="name" name="name" class="form-input" placeholder="Dr. John Doe" required
                    value="${fn:escapeXml(param.name)}">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label for="email">Medical Email</label>
                <div class="input-wrapper">
                  <input type="email" id="email" name="email" class="form-input" placeholder="doctor@hospital.com"
                    required value="${fn:escapeXml(param.email)}">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                      <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label for="pharmacistId">Medical ID Number</label>
                <div class="input-wrapper">
                  <input type="text" id="pharmacistId" name="pharmacistId" class="form-input" pattern="\d+"
                    placeholder="Registration Number" required value="${fn:escapeXml(param.pharmacistId)}">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                      <line x1="7" y1="8" x2="17" y2="8"></line>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label for="password">Create Password</label>
                <div class="input-wrapper">
                  <input type="password" id="password" name="password" class="form-input" placeholder="••••••••"
                    required minlength="6">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-wrapper">
                  <input type="password" id="confirmPassword" name="confirmPassword" class="form-input"
                    placeholder="••••••••" required minlength="6">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="full-width" style="margin-top: 10px;">
                <button type="submit" id="submitBtn" class="btn-auth-submit">
                  Complete Registration
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Server Error -->
            <c:if test="${not empty error}">
              <div class="message-box error-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="12"></line>
                  <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>${error}</span>
              </div>
            </c:if>
          </form>

          <p class="bottom-text">Already have a medical account? <a href="${cp}/pharmacist/login">Log In</a></p>
        </div>

        <!-- Background Elements -->
        <div class="mesh-background"></div>

        <script>
          (function () {
            const form = document.getElementById('regForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function (e) {
              if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
              }
              submitBtn.disabled = true;
              const orig = submitBtn.innerHTML;
              submitBtn.innerHTML = 'Creating Account...';
              setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = orig;
              }, 6000);
            });
          })();
        </script>
      </body>

      </html>