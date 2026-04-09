<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <c:set var="cp" value="${pageContext.request.contextPath}" />

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Create Guardian Account - Medora</title>

            <!-- Design Tokens and Base Styles -->
            <link rel="stylesheet" href="${cp}/css/common.css" />
            <link rel="stylesheet" href="${cp}/css/auth.css" />

            <!-- Custom Fonts -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link
                href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
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
                    <span class="brand-text">Guardian Registration</span>
                </div>

                <p class="subtitle">Join our network to care for your loved ones</p>

                <!-- Toggle Buttons -->
                <div class="form-toggle" style="max-width: 280px; margin: 0 auto 24px;">
                    <button type="button" onclick="location.href='${cp}/patient/register'">Patient</button>
                    <button class="active" type="button">Guardian</button>
                </div>

                <!-- Guardian Registration Form -->
                <form method="post" action="${cp}/guardian/register" id="guardianForm" novalidate
                    onsubmit="return window.guardianValidate && window.guardianValidate(this);">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="g_name">Full Name</label>
                            <div class="input-wrapper">
                                <input type="text" id="g_name" name="g_name" class="form-input"
                                    placeholder="Enter your full name" required value="${param.g_name}" />
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nic">NIC Number</label>
                            <div class="input-wrapper">
                                <input type="text" id="nic" name="nic" class="form-input" placeholder="2001XXXXXXXX"
                                    required value="${param.nic}" />
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
                                        <line x1="7" y1="8" x2="17" y2="8"></line>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <div class="input-wrapper">
                                <input type="text" id="contact_number" name="contact_number" class="form-input"
                                    placeholder="+94 7X XXX XXXX" required value="${param.contact_number}" />
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                        </path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" class="form-input"
                                    placeholder="guardian@example.com" required value="${param.email}" />
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                        </path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="password">Security Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" class="form-input"
                                    placeholder="••••••••" required />
                                <span class="input-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="form-options full-width">
                            <label class="checkbox-container">
                                <input type="checkbox" id="agree" name="agree" required />
                                <span class="checkmark"></span>
                                I agree to the <a href="#" style="color: var(--primary-blue);">Privacy Policies</a>
                            </label>
                        </div>

                        <div class="full-width">
                            <button type="submit" class="btn-auth-submit">
                                Create Guardian Account
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Server Error -->
                    <c:if test="${not empty error}">
                        <div class="message-box error-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span>${error}</span>
                        </div>
                    </c:if>
                </form>

                <p class="bottom-text">Already have an account? <a href="${cp}/guardian/login">Log In</a></p>
            </div>

            <!-- Background Elements -->
            <div class="mesh-background"></div>

            <script src="${cp}/js/form-validation.js?v=1" defer></script>
        </body>

        </html>