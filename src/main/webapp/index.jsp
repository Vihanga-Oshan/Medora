<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Medora - Never Miss a Dose Again</title>
    <link rel="stylesheet" href="${cp}/css/index.css" />
</head>

<body>

<!-- Header / Hero Section -->
<header class="hero">
    <div class="overlay"></div>
    <div class="container hero-content">
        <div class="hero-text">
            <div class="logo">
                <img src="${cp}/assets/logo.png" alt="Medora Logo" />
                <h3>Medora</h3>
            </div>
            <h1>Never Miss a <span class="highlight">Dose Again</span></h1>
            <p>
                Smart prescription tracking powered by intelligent reminders.
                Helping patients and caregivers manage medications effortlessly.
            </p>
            <ul class="features-list">
                <li>Automated medication schedules</li>
                <li>Real-time reminders & alerts</li>
                <li>Guardian monitoring & support</li>
            </ul>
            <div class="cta-buttons">
                <a href="${cp}/register/patient" class="btn primary">Get Started Free</a>
                <a href="${cp}/login" class="btn secondary">Sign In</a>
            </div>
            <div class="compliance">
                <span class="green-dot">●</span> HIPAA Compliant
                <span class="gray-dot">•</span> Encrypted Data
            </div>
        </div>

        <div class="hero-image">
            <img src="${cp}/assets/hero-image.jpg" alt="Doctor using digital tablet" />
            <div class="badge">
                <h4>99.9%</h4>
                <p>Reminder Accuracy</p>
            </div>
        </div>
    </div>
</header>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div>
                <div class="stats-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/456/456283.png" alt="Users Icon" />
                </div>
                <h2>50,000+</h2>
                <p>Active Users</p>
                <span>Trust Medora daily</span>
            </div>

            <div>
                <div class="stats-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/747/747310.png" alt="Calendar Icon" />
                </div>
                <h2>1M+</h2>
                <p>Schedules Created</p>
                <span>Medications tracked</span>
            </div>

            <div>
                <div class="stats-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/1827/1827370.png" alt="Bell Icon" />
                </div>
                <h2>5M+</h2>
                <p>Reminders Sent</p>
                <span>Doses never missed</span>
            </div>

            <div>
                <div class="stats-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/1828/1828884.png" alt="Chart Icon" />
                </div>
                <h2>98%</h2>
                <p>Adherence Rate</p>
                <span>Improved outcomes</span>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works">
    <div class="container">
        <h2>How Medora Works</h2>
        <p>Four simple steps to effortless medication management</p>
        <div class="steps-grid">
            <div class="step-card">
                <h3>Upload Prescription</h3>
                <p>Simply upload your prescription or enter medication details manually into the system.</p>
            </div>
            <div class="step-card">
                <h3>Auto-Generated Schedule</h3>
                <p>Medora intelligently creates a personalized medication schedule based on your prescriptions.</p>
            </div>
            <div class="step-card">
                <h3>Timely Reminders</h3>
                <p>Receive notifications at the right time to ensure you never miss a dose.</p>
            </div>
            <div class="step-card">
                <h3>Better Health Outcomes</h3>
                <p>Stay consistent with your medication routine and improve your overall health.</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2>Powerful Features for Better Health</h2>
        <p>Everything you need to stay on track with your medications</p>
        <div class="features-grid">
            <div class="feature-card">
                <h3>Smart Reminders</h3>
                <p>Receive intelligent, personalized reminders that adapt to your schedule and ensure perfect timing.</p>
            </div>
            <div class="feature-card">
                <h3>Flexible Scheduling</h3>
                <p>Create customizable medication schedules that fit your lifestyle and complex prescription requirements.</p>
            </div>
            <div class="feature-card">
                <h3>Guardian Access</h3>
                <p>Enable caregivers to remotely monitor medication adherence and provide support when needed.</p>
            </div>
            <div class="feature-card">
                <h3>HIPAA Compliant</h3>
                <p>Bank-level encryption and security protocols protect your sensitive health information.</p>
            </div>
            <div class="feature-card">
                <h3>Multi-Platform</h3>
                <p>Access Medora seamlessly across all your devices with automatic synchronization.</p>
            </div>
            <div class="feature-card">
                <h3>Medication History</h3>
                <p>Track your complete medication history with detailed logs and adherence reports.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="container">
        <h2>Trusted by Thousands</h2>
        <p>See what our users have to say about their experience with Medora</p>

        <div class="testimonial-grid">
            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p>“Medora has completely transformed how I manage my medications. I used to forget doses regularly, but now I'm 100% compliant.”</p>
                <h4>Sarah Mitchell</h4>
                <span>Patient</span>
            </div>

            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p>“I recommend Medora to all my patients who struggle with medication adherence. The improvement has been remarkable.”</p>
                <h4>Dr. James Anderson</h4>
                <span>Healthcare Provider</span>
            </div>

            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p>“Being able to monitor my mother’s medication remotely gives me incredible peace of mind. The alerts keep her on track.”</p>
                <h4>Maria Rodriguez</h4>
                <span>Guardian</span>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="final-cta">
    <div class="container">
        <h2>Ready to Never Miss a Dose?</h2>
        <p>Join thousands of patients and caregivers who trust Medora for better medication management.</p>
        <a href="${cp}/register.jsp" class="btn primary large">Start Your Free Account</a>
        <ul class="benefits">
            <li>Free to get started</li>
            <li>No credit card required</li>
            <li>Cancel anytime</li>
        </ul>
    </div>
</section>

<script src="${cp}/js/script.js"></script>
</body>
</html>