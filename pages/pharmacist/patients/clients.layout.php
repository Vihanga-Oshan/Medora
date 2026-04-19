<?php
$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Patients - Medora</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/patient-list.css">
</head>
<body>
<div class="container">
  <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

  <div class="main-content">
    <header class="header">
      <div class="user-info">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
        <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
      </div>
      <div class="greeting">
        <span class="greeting-icon">&#129505;</span>
        <div>
          <span class="greeting-text">All Patients</span>
        </div>
      </div>
    </header>

    <div class="table-section">
      <div class="table-header">
        <h2>Registered Patients</h2>
        <div class="search-bar">
          <form method="get" action="<?= htmlspecialchars($base) ?>/pharmacist/patients">
            <input type="text" name="nic" placeholder="Search by NIC, name, or email..." value="<?= htmlspecialchars($search ?? '') ?>" />
            <button type="submit">Search</button>
          </form>
        </div>
      </div>

      <?php if (empty($patientList)): ?>
        <p class="no-data-msg">No patients found.</p>
      <?php else: ?>
        <table class="patient-table">
          <thead>
          <tr>
            <th>Name</th>
            <th>NIC</th>
            <th>Email</th>
            <th>Emergency Contact</th>
            <th>Action</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($patientList as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['nic'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['emergency_contact'] ?? '') ?></td>
              <td>
                <a href="<?= htmlspecialchars($base) ?>/pharmacist/view-schedule?nic=<?= urlencode((string)($p['nic'] ?? '')) ?>" class="btn-view">View Schedule</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>

