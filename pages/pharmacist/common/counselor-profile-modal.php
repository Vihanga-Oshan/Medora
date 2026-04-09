<?php
$profilePicture = $currentCounselor['profilePictureUrl'] ?? '/assets/img/avatar.png';
$savedAvailability = json_decode($currentCounselor['availabilitySchedule'] ?? '{}', true);
if (!is_array($savedAvailability)) {
    $savedAvailability = [];
}
?>
<div class="profile-modal-overlay" id="counselorProfileModalOverlay" style="display: none;">
    <div class="profile-modal">
        <div class="profile-modal-header">
            <h3 id="counselorProfileModalTitle">Edit Profile</h3>
            <button type="button" class="profile-modal-close" id="counselorProfileModalClose">&times;</button>
        </div>
        <div class="profile-modal-body">
            <p class="profile-modal-subtitle">Update your professional profile.</p>

            <form id="counselorProfileForm" action="#" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="counselorProfilePicture">Profile Picture</label>
                    <div class="profile-picture-upload">
                        <div class="current-profile-picture">
                            <img id="counselorCurrentProfilePic" src="<?= htmlspecialchars($profilePicture) ?>" alt="Current Profile Picture">
                        </div>
                        <input type="file" id="counselorProfilePicture" name="profilePicture" accept="image/*" class="form-input" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('counselorProfilePicture').click()">
                            <i data-lucide="camera" stroke-width="1"></i>
                            Change Picture
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="counselorDisplayName">Display Name</label>
                        <input type="text" class="form-input" id="counselorDisplayName" name="displayName" value="<?= htmlspecialchars($currentCounselor['displayName'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="counselorTitle">Title</label>
                        <input type="text" class="form-input" id="counselorTitle" name="title" value="<?= htmlspecialchars($currentCounselor['title'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="counselorSpecialty">Specialization</label>
                    <select class="form-input" id="counselorSpecialty" name="specialty">
                        <?php
                        $specialties = [
                            '',
                            'Addiction Recovery',
                            'Substance Abuse',
                            'Alcohol Addiction',
                            'Behavioral Therapy',
                            'Mental Health',
                            'Trauma & PTSD',
                            'Family Therapy',
                            'Other',
                        ];
                        $selectedSpecialty = $currentCounselor['specialty'] ?? '';
                        foreach ($specialties as $specialty):
                        ?>
                            <option value="<?= htmlspecialchars($specialty) ?>" <?= $selectedSpecialty === $specialty ? 'selected' : '' ?>>
                                <?= $specialty === '' ? 'Select Specialization' : htmlspecialchars($specialty) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="counselorBio">Bio</label>
                    <textarea class="form-input" id="counselorBio" name="bio" rows="4" placeholder="Tell clients about your background and approach..."><?= htmlspecialchars($currentCounselor['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="counselorEmail">Email</label>
                        <input type="email" class="form-input" id="counselorEmail" name="email" value="<?= htmlspecialchars($currentCounselor['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="counselorPhone">Phone Number</label>
                        <input type="tel" class="form-input" id="counselorPhone" name="phoneNumber" value="<?= htmlspecialchars($currentCounselor['phoneNumber'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="counselorFee">Consultation Fee ($)</label>
                    <input type="number" class="form-input" id="counselorFee" name="consultationFee" min="0" step="0.01" value="<?= htmlspecialchars((string) ($currentCounselor['consultationFee'] ?? '')) ?>">
                </div>

                <div class="form-divider"></div>
                <h4 class="form-section-title">Availability Schedule</h4>
                <p class="form-help">Set your available hours for each day. Toggle the day to enable or disable it.</p>

                <div class="availability-list">
                    <?php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                    foreach ($days as $day):
                        $dayConfig = $savedAvailability[$day] ?? [];
                    ?>
                        <div class="availability-row">
                            <label class="day-toggle">
                                <input type="checkbox" name="<?= $day ?>_enabled" id="<?= $day ?>_enabled" <?= !empty($dayConfig) ? 'checked' : '' ?>>
                                <span class="day-label"><?= ucfirst($day) ?></span>
                            </label>
                            <div class="time-range">
                                <select name="<?= $day ?>_start" class="time-select">
                                    <?php foreach (['09:00', '10:00', '11:00', '12:00', '13:00', '14:00'] as $time): ?>
                                        <option value="<?= $time ?>" <?= ($dayConfig['start'] ?? '') === $time ? 'selected' : '' ?>><?= date('g:i A', strtotime($time)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span>to</span>
                                <select name="<?= $day ?>_end" class="time-select">
                                    <?php foreach (['12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'] as $time): ?>
                                        <option value="<?= $time ?>" <?= (($dayConfig['end'] ?? '17:00') === $time) ? 'selected' : '' ?>><?= date('g:i A', strtotime($time)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="profile-modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelCounselorProfile">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('counselorProfileModalOverlay');
    const closeBtn = document.getElementById('counselorProfileModalClose');
    const cancelBtn = document.getElementById('cancelCounselorProfile');
    const fileInput = document.getElementById('counselorProfilePicture');
    const previewImg = document.getElementById('counselorCurrentProfilePic');

    const closeModal = () => {
        if (!overlay) return;
        overlay.classList.remove('show');
        overlay.style.display = 'none';
    };

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', (event) => {
        if (event.target === overlay) closeModal();
    });

    fileInput?.addEventListener('change', function () {
        if (!this.files || !this.files[0] || !previewImg) return;
        const reader = new FileReader();
        reader.onload = (event) => {
            previewImg.src = event.target?.result || previewImg.src;
        };
        reader.readAsDataURL(this.files[0]);
    });
});

function openCounselorProfileModal() {
    const overlay = document.getElementById('counselorProfileModalOverlay');
    if (!overlay) return;
    overlay.style.display = 'flex';
    overlay.classList.add('show');
}
</script>
