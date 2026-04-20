<?php
require_once __DIR__ . '/../../profile/profile.model.php';
$patientNic = $user['nic'] ?? '';
$patientProfile = $patientNic ? ProfileModel::getByNic($patientNic) : null;
$patientPhone = $patientProfile ? trim((string) ($patientProfile['emergency_contact'] ?? '')) : '';
?>
<div class="billing-panel" id="billingPanel">
    <div class="billing-header">
        <h3>Collection Details</h3>
        <p>Provide these details to collect your medicine from the pharmacy.</p>
    </div>
    <div class="billing-grid">
        <label class="field-group">
            <span>Collection Method</span>
            <select name="delivery_method" id="deliveryMethod">
                <option value="PICKUP">Pick up from pharmacy</option>
            </select>
        </label>
        <label class="field-group">
            <span>Collector's Phone Number</span>
            <input type="tel" name="billing_phone" value="<?= htmlspecialchars($patientPhone) ?>">
        </label>
    </div>
</div>
