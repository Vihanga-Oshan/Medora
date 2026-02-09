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
        margin-top: 80px;
        background-color: var(--navy-dark);
        color: #ffffff;
        padding: 60px 20px 30px;
        font-family: inherit;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 40px;
    }

    .footer-section {
        flex: 1;
        min-width: 250px;
    }

    .footer-section h4 {
        color: #fff;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .footer-section p {
        font-size: 15px;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.8);
    }

    .footer-links-row {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }

    .footer-links-row a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .footer-links-row a:hover {
        color: #ffffff;
        text-decoration: underline;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.6);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
            text-align: center;
        }

        .footer-links-row {
            justify-content: center;
            margin-top: 20px;
        }
    }
</style>