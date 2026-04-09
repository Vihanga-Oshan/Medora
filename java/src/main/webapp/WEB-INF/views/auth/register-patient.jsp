<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
    <c:set var="cp" value="${pageContext.request.contextPath}" />

    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Create Account - Medora</title>

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

        .gender-selection {
          display: flex;
          gap: 16px;
          padding: 8px 0;
        }

        @media (max-width: 600px) {
          .form-grid {
            grid-template-columns: 1fr;
          }

          .full-width {
            grid-column: span 1;
          }
        }

        .step-indicator {
          display: flex;
          justify-content: center;
          gap: 8px;
          margin-bottom: 20px;
        }

        .step-dot {
          width: 10px;
          height: 10px;
          border-radius: 50%;
          background: rgba(0, 0, 0, 0.1);
          transition: var(--transition-mid);
        }

        .step-dot.active {
          background: var(--primary-blue);
          width: 24px;
          border-radius: 5px;
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
          <span class="brand-text">Create Account</span>
        </div>

        <p class="subtitle">Join Medora and start your wellness journey</p>

        <!-- Toggle Buttons -->
        <div class="form-toggle" style="max-width: 280px; margin: 0 auto 24px;">
          <button class="active" type="button">Patient</button>
          <button type="button" onclick="location.href='${cp}/guardian/register'">Guardian</button>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
          <div id="dot1" class="step-dot active"></div>
          <div id="dot2" class="step-dot"></div>
        </div>

        <!-- Registration Form -->
        <form method="post" action="${cp}/patient/register" id="patientForm" novalidate
          onsubmit="return window.patientValidate && window.patientValidate(this);">

          <!-- STEP 1 -->
          <div id="step1" class="animate-in">
            <div class="form-grid">
              <div class="form-group full-width">
                <label>Full Name</label>
                <div class="input-wrapper">
                  <input type="text" name="name" class="form-input" placeholder="John Doe" required
                    value="${param.name}" />
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
                <label>Gender</label>
                <div class="gender-selection">
                  <label class="checkbox-container">
                    <input type="radio" name="gender" value="Male" ${param.gender=='Male' ? 'checked' : '' } required />
                    <span class="checkmark" style="border-radius: 50%;"></span>
                    Male
                  </label>
                  <label class="checkbox-container">
                    <input type="radio" name="gender" value="Female" ${param.gender=='Female' ? 'checked' : '' } />
                    <span class="checkmark" style="border-radius: 50%;"></span>
                    Female
                  </label>
                </div>
              </div>

              <div class="form-group">
                <label>Emergency Contact</label>
                <div class="input-wrapper">
                  <input type="text" name="emergencyContact" class="form-input" placeholder="+94 7X XXX XXXX" required
                    value="${param.emergencyContact}" />
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                      </path>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label>NIC Number</label>
                <div class="input-wrapper">
                  <input type="text" name="nic" class="form-input" placeholder="2001XXXXXXXX" required
                    value="${param.nic}" />
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                      <line x1="7" y1="8" x2="17" y2="8"></line>
                      <line x1="7" y1="12" x2="17" y2="12"></line>
                      <line x1="7" y1="16" x2="13" y2="16"></line>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                  <input type="email" name="email" class="form-input" placeholder="john@example.com" required
                    value="${param.email}" />
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
                <label>Password</label>
                <div class="input-wrapper">
                  <input type="password" name="password" id="password" class="form-input" placeholder="••••••••"
                    required />
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
                <label>Confirm Password</label>
                <div class="input-wrapper">
                  <input type="password" name="confirmPassword" id="confirmPassword" class="form-input"
                    placeholder="••••••••" required />
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="full-width" style="margin-top: 10px;">
                <button type="button" class="btn-auth-submit" onclick="nextStep()">
                  Continue Registration
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- STEP 2 -->
          <div id="step2" style="display: none;">
            <div class="form-grid">
              <div class="form-group full-width">
                <label>Known Allergies (Optional)</label>
                <textarea name="allergies" class="form-input" style="height: 100px; padding-top: 15px;"
                  placeholder="List any medical or food allergies...">${param.allergies}</textarea>
              </div>

              <div class="form-group full-width">
                <label>Chronic Medical Conditions (Optional)</label>
                <textarea name="chronic" class="form-input" style="height: 100px; padding-top: 15px;"
                  placeholder="e.g. Diabetes, Hypertension...">${param.chronic}</textarea>
              </div>

              <div class="form-group full-width">
                <label>Guardian NIC (If applicable)</label>
                <div class="input-wrapper">
                  <input type="text" name="guardianNic" class="form-input" placeholder="NIC of your medical guardian"
                    value="${param.guardianNic}" />
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                      <circle cx="9" cy="7" r="4"></circle>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                  </span>
                </div>
              </div>

              <div class="form-options full-width">
                <label class="checkbox-container">
                  <input type="checkbox" id="privacy" name="privacy" required />
                  <span class="checkmark"></span>
                  I agree to the <a href="#" style="color: var(--primary-blue);">Privacy Policies</a> and Terms of
                  Service
                </label>
              </div>

              <div class="full-width" style="display: flex; gap: 15px; margin-top: 10px;">
                <button type="button" class="btn-auth-submit"
                  style="background: rgba(0,0,0,0.05); color: var(--text-dark); box-shadow: none;"
                  onclick="previousStep()">
                  Back
                </button>
                <button type="submit" class="btn-auth-submit">
                  Complete Account Setup
                </button>
              </div>
            </div>
          </div>

          <!-- Show server error if present -->
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

        <p class="bottom-text">
          Already have an account? <a href="${cp}/login">Log In</a>
        </p>
      </div>

      <!-- Background Elements -->
      <div class="mesh-background"></div>

      <script src="${cp}/js/form-validation.js?v=8" defer></script>
      <script>
        function nextStep() {
          const form = document.getElementById('patientForm');
          // Simple visual validation before going next
          if (document.getElementById('step1').querySelectorAll(':invalid').length === 0) {
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('step2').classList.add('animate-in');
            document.getElementById('dot2').classList.add('active');
            document.getElementById('dot1').classList.remove('active');
          } else {
            // Trigger browser validation
            form.reportValidity();
          }
        }

        function previousStep() {
          document.getElementById('step2').style.display = 'none';
          document.getElementById('step1').style.display = 'block';
          document.getElementById('step1').classList.add('animate-in');
          document.getElementById('dot1').classList.add('active');
          document.getElementById('dot2').classList.remove('active');
        }
      </script>
    </body>

    </html>