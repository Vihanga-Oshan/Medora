<footer class="patient-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>Medora</h4>
            <p>Your trusted assistant for smart prescription tracking and medication reminders.</p>
        </div>

        <div class="footer-links-row">
            <a href="#">Help Center</a>
            <a href="#">Contact Us</a>
            <a href="#">Privacy Policy</a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Medora. All rights reserved.</p>
    </div>
</footer>

<style>
    .patient-footer {
        margin-top: 50px;
        background-color: #007dca;
        color: #ffffff;
        padding: 32px 20px 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-section {
        flex: 1 1 300px;
        min-width: 240px;
    }

    .footer-section h4 {
        margin-bottom: 8px;
        font-weight: 600;
    }

    .footer-section p {
        font-size: 0.9rem;
        line-height: 1.5;
        color: #d9f1ff;
    }

    .footer-links-row {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }

    .footer-links-row a {
        color: #e2f4ff;
        text-decoration: none;
        font-size: 0.92rem;
        transition: color 0.2s ease;
    }

    .footer-links-row a:hover {
        color: #ffffff;
        text-decoration: underline;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 16px;
        font-size: 0.85rem;
        color: #c7eaff;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        margin-top: 16px;
    }

    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
            text-align: center;
        }

        .footer-links-row {
            justify-content: center;
            margin-top: 8px;
        }
    }
</style>
