<!DOCTYPE html>
<html lang="en">
<?php $base = APP_BASE ?: ''; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as a Pharmacist | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/auth.css">
    <style>
        .application-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .application-header { text-align: center; margin-bottom: 2rem; }
        .application-header h1 { color: #2c3e50; margin-bottom: 0.5rem; }
        .application-header p { color: #666; font-size: 1.1rem; }
        .form-section { margin-bottom: 2rem; }
        .section-title { font-size: 1.3rem; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .form-group { flex: 1; }
        .required { color: #e74c3c; }
        input, textarea, select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; transition: border-color 0.3s; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #3498db; }
        textarea { resize: vertical; min-height: 100px; }
        .submit-btn { width: 100%; background: #3498db; color: white; padding: 1rem; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #3498db; text-decoration: none; }
        @media (max-width: 768px) { .application-container { margin: 1rem; padding: 1rem; } .form-row { flex-direction: column; } }
    </style>
</head>
<body>
<div class="application-container">
    <a href="<?= htmlspecialchars($base) ?>/" class="back-link">&larr; Back to Home</a>
    <div class="application-header">
        <h1>Apply as a Counselor</h1>
        <p>Join our team of professional counselors and make a difference in people's lives</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="form-section">
            <h3 class="section-title">Personal Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">Full Name <span class="required">*</span></label>
                    <input type="text" id="fullName" name="fullName" required value="<?= htmlspecialchars(Request::post('fullName') ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="phoneNumber">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" required value="<?= htmlspecialchars(Request::post('phoneNumber') ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="title">Professional Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars(Request::post('title') ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Professional Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="specialty">Specialty <span class="required">*</span></label>
                    <select id="specialty" name="specialty" required>
                        <option value="">Select Specialty</option>
                        <?php foreach (['Addiction Counseling','Mental Health Counseling','Family Therapy','Cognitive Behavioral Therapy','Trauma Counseling','Youth Counseling','Group Therapy','Other'] as $specialty): ?>
                            <option value="<?= htmlspecialchars($specialty) ?>" <?= Request::post('specialty') === $specialty ? 'selected' : '' ?>><?= htmlspecialchars($specialty) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="experienceYears">Years of Experience</label>
                    <input type="number" id="experienceYears" name="experienceYears" min="0" max="50" value="<?= htmlspecialchars(Request::post('experienceYears') ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="consultationFee">Consultation Fee (per session)</label>
                    <input type="number" id="consultationFee" name="consultationFee" min="0" step="0.01" value="<?= htmlspecialchars(Request::post('consultationFee') ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="languagesSpoken">Languages Spoken</label>
                    <input type="text" id="languagesSpoken" name="languagesSpoken" value="<?= htmlspecialchars(Request::post('languagesSpoken') ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="bio">Professional Bio <span class="required">*</span></label>
                <textarea id="bio" name="bio" required><?= htmlspecialchars(Request::post('bio') ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Education & Credentials</h3>
            <div class="form-group">
                <label for="education">Education <span class="required">*</span></label>
                <textarea id="education" name="education" required><?= htmlspecialchars(Request::post('education') ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="certifications">Certifications & Licenses</label>
                <textarea id="certifications" name="certifications"><?= htmlspecialchars(Request::post('certifications') ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="documentsUrl">Supporting Documents (URL)</label>
                <input type="url" id="documentsUrl" name="documentsUrl" value="<?= htmlspecialchars(Request::post('documentsUrl') ?? '') ?>">
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">Availability</h3>
            <div class="form-group">
                <label for="availabilitySchedule">Availability Schedule</label>
                <textarea id="availabilitySchedule" name="availabilitySchedule"><?= htmlspecialchars(Request::post('availabilitySchedule') ?? '') ?></textarea>
            </div>
        </div>

        <button type="submit" class="submit-btn">Submit Application</button>
    </form>
</div>
</body>
</html>
