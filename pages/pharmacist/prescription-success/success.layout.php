<!DOCTYPE html>
<html lang="en">
<?php $base = APP_BASE ?: ''; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/common.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 3rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2.5rem;
        }

        .success-title {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .next-steps {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .home-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 1rem 2rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">&#10003;</div>
        <h1 class="success-title">Application Submitted Successfully!</h1>
        <p class="success-message">Thank you for your application. It has been received and is now under review by our
            administrative team.</p>
        <div class="next-steps">
            <h3>What happens next?</h3>
            <ul style="text-align:left; color:#666;">
                <li><strong>Review Process:</strong> Our team will carefully review your application and credentials
                </li>
                <li><strong>Email Notification:</strong> You will receive an email update within 3-5 business days</li>
                <li><strong>Interview:</strong> If approved for the next step, we'll schedule an interview</li>
                <li><strong>Account Setup:</strong> Upon approval, we'll create your pharmacist account with temporary
                    login credentials</li>
            </ul>
        </div>
        <a href="<?= htmlspecialchars($base) ?>/" class="home-btn">Return to Home</a>
    </div>
</body>

</html>