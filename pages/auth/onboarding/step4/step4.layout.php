<?php
$pageTitle = 'Medora - Select Your Plan';
$authCss   = 'onboarding.css';
$base = APP_BASE ?: '';
require_once __DIR__ . '/../../common/auth.head.php';
?>

<body class="theme-user">
    <div class="onboarding-container">
        <header>
            <div class="logo-container" style="padding: 10px;">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo" class="logo">
                <span class="logo-text">Med<br>ora</span>
            </div>
        </header>

        <div class="onboarding-content">
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-text">Step 4 of 5</div>
                    <div class="progress-track" aria-label="Progress">
                        <div class="progress-fill" style="width: 80%;"></div>
                    </div>
                </div>
            </div>

            <div class="onboarding-form-container">
                <h1 class="onboarding-title">Choose Your Recovery Path</h1>
                <p style="text-align: center; color: var(--color-text-secondary); margin-bottom: 30px;">
                    Based on your profile, we recommend choosing a plan that fits your needs. You can always change this later.
                </p>

                <?php if ($error !== null): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; background-color: #ffe6e6;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form class="onboarding-form" id="recoveryPlanForm" method="POST" action="<?= htmlspecialchars($base) ?>/auth/onboarding/step4">
                    <div class="plan-selection-container">
                        <!-- System Plan Option -->
                        <div class="plan-card selected" onclick="selectPlan('system', this)">
                            <div class="plan-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                            </div>
                            <h3 class="plan-title">System Plan</h3>
                            <p class="plan-description">A structured, self-guided journey using our proven tools and resources.</p>
                            <ul class="plan-features">
                                <li>Daily check-ins & tracking</li>
                                <li>Guided exercises & journaling</li>
                                <li>Community support access</li>
                                <li>Free forever</li>
                            </ul>
                        </div>

                        <!-- Counselor Plan Option -->
                        <div class="plan-card" onclick="selectPlan('counselor', this)">
                            <div class="plan-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <h3 class="plan-title">Counselor Plan</h3>
                            <p class="plan-description">Work directly with a certified professional tailored to your specific needs.</p>
                            <ul class="plan-features">
                                <li>Everything in System Plan</li>
                                <li>1-on-1 video sessions</li>
                                <li>Personalized recovery strategy</li>
                                <li>Direct messaging support</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Hidden input to store selection -->
                    <input type="hidden" name="selectedPlan" id="selectedPlan" value="system" required>

                    <div class="form-actions-recovery">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= htmlspecialchars($base) ?>/auth/onboarding/step3'">Back</button>
                        <button type="submit" class="form-submit-btn">Continue to Dashboard</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectPlan(planType, cardElement) {
            // Update hidden input
            document.getElementById('selectedPlan').value = planType;

            // Remove 'selected' class from all cards
            const cards = document.querySelectorAll('.plan-card');
            cards.forEach(card => card.classList.remove('selected'));

            // Add 'selected' class to clicked card
            cardElement.classList.add('selected');
        }
    </script>
</body>

</html>
