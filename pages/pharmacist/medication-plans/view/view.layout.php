<?php
$activePage = 'recovery';
$shortGoal = null;
$longGoal = null;
foreach ($goals as $goal) {
    if ($goal['goalType'] === 'short_term' && !$shortGoal) $shortGoal = $goal;
    if ($goal['goalType'] === 'long_term' && !$longGoal) $longGoal = $goal;
}
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Edit Recovery Plan'; $pageStyle = ['counselor/viewRecoveryPlan']; require __DIR__ . '/../../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />
        <div class="main-content-header"><div class="main-content-header-text"><a href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans" class="back-btn"><i data-lucide="arrow-left" class="back-icon" stroke-width="1"></i></a><h2><?= htmlspecialchars($plan['title']) ?> for <?= htmlspecialchars($plan['clientName']) ?></h2></div></div>
        <div class="main-content-body"><div class="inner-body-content">
            <?php if (!empty($errorMessage)): ?><div class="error-message"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <form id="updatePlan-form" action="" method="post">
                <input type="hidden" name="planId" value="<?= (int) $plan['planId'] ?>" />
                <div class="plan-overview-card">
                    <div class="createPlan-group"><label for="planGoal"><strong>Goal</strong></label><input type="text" id="planGoal" name="planGoal" value="<?= htmlspecialchars($plan['description']) ?>"></div>
                    <div class="plan-meta">Created by: <?= htmlspecialchars($currentCounselor['displayName']) ?> · Last edited: <?= htmlspecialchars($plan['updatedAt']) ?></div>
                    <div class="progress-section"><div class="progress-label"><span>Progress</span><span><?= (int) $plan['progressPercentage'] ?>%</span></div><div class="progress-bar"><div class="progress-fill" style="width: <?= (int) $plan['progressPercentage'] ?>%;"></div></div></div>
                </div>
                <div class="section-container">
                    <h3 class="section-title">Plan Overview</h3>
                    <div class="createPlan-group"><label>Assigned to</label><select id="assignedTo" name="userId"><?php foreach ($clients as $client): ?><option value="<?= (int) $client['id'] ?>" <?= (int) $plan['userId'] === (int) $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="createPlan-group"><label for="title">Plan Title</label><input id="title" name="title" type="text" value="<?= htmlspecialchars($plan['title']) ?>" required /></div>
                    <div class="createPlan-group"><label for="category">Category</label><input id="category" name="category" type="text" value="<?= htmlspecialchars($plan['category']) ?>" /></div>
                    <input type="hidden" name="planType" value="counselor" />
                    <div class="createPlan-group"><label for="planStatus">Plan Status</label><select id="planStatus" name="planStatus"><?php foreach (['draft','active','completed','paused','cancelled'] as $status): ?><option value="<?= $status ?>" <?= $plan['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
                    <div class="createPlan-group"><label>Time Period</label><div class="date-row"><input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($plan['startDate']) ?>" required /><input type="date" id="targetCompletionDate" name="targetCompletionDate" value="<?= htmlspecialchars($plan['targetCompletionDate']) ?>" required /></div></div>
                    <div class="createPlan-group"><label for="description">Description</label><textarea id="description" name="description"><?= htmlspecialchars($plan['description']) ?></textarea></div>
                </div>
                <div class="section-container">
                    <h3 class="section-title">Phases</h3>
                    <div id="phases-container">
                        <?php foreach ([1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'] as $phaseNum => $phaseName): ?>
                            <div class="phase" data-phase="<?= $phaseNum ?>"><span class="phase-name">Phase <?= $phaseNum ?>: <?= htmlspecialchars($phaseName) ?></span><div class="phase-actions"><button type="button" class="btn btn-link phase-btn" onclick="addTask(<?= $phaseNum ?>)">+ Add Task</button><button type="button" class="btn btn-link phase-btn" onclick="addMilestone(<?= $phaseNum ?>)">+ Add Milestone</button></div></div>
                            <div class="phase-tasks" id="phase-<?= $phaseNum ?>-tasks">
                                <?php foreach ($tasks as $task): if ((int) $task['phase'] !== $phaseNum) continue; ?>
                                    <div class="task-item">
                                        <input type="hidden" name="taskId[]" value="<?= (int) $task['taskId'] ?>" />
                                        <input type="hidden" name="taskPhase[]" value="<?= (int) $task['phase'] ?>" />
                                        <input type="text" name="taskTitle[]" value="<?= htmlspecialchars($task['title']) ?>" placeholder="Task description" />
                                        <select name="taskType[]"><?php foreach (['custom','journal','meditation','session','exercise'] as $taskType): ?><option value="<?= $taskType ?>" <?= $task['taskType'] === $taskType ? 'selected' : '' ?>><?= ucfirst($taskType) ?></option><?php endforeach; ?></select>
                                        <select name="recurrencePattern[]"><?php foreach (['' => 'One-time', 'daily' => 'Daily', 'weekly' => 'Weekly', 'bi-weekly' => 'Bi-weekly'] as $value => $label): ?><option value="<?= $value ?>" <?= ($task['recurrencePattern'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select>
                                        <span class="remove-btn" onclick="this.parentElement.remove()">×</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="section-container">
                    <h3 class="section-title">Recovery Goals</h3>
                    <div class="goals-inputs">
                        <div class="goal-input-group"><label>Short-term Goal</label><input type="text" name="shortTermGoalTitle" value="<?= htmlspecialchars($shortGoal['title'] ?? '') ?>" /><div class="goal-days-input"><label>Target Days:</label><input type="number" name="shortTermGoalDays" value="<?= (int) ($shortGoal['targetDays'] ?? 0) ?>" min="1" max="365" /></div></div>
                        <div class="goal-input-group"><label>Long-term Goal</label><input type="text" name="longTermGoalTitle" value="<?= htmlspecialchars($longGoal['title'] ?? '') ?>" /><div class="goal-days-input"><label>Target Days:</label><input type="number" name="longTermGoalDays" value="<?= (int) ($longGoal['targetDays'] ?? 0) ?>" min="1" max="365" /></div></div>
                    </div>
                </div>
                <div class="section-container"><h3 class="section-title">Care Notes</h3><div class="createPlan-group"><label for="customNotes">Notes</label><textarea id="customNotes" name="customNotes"><?= htmlspecialchars($plan['customNotes']) ?></textarea></div></div>
                <div class="action-buttons"><button type="submit" class="btn btn-primary">Update Plan</button><button type="button" class="btn btn-secondary" onclick="exportPDF()">Export PDF</button></div>
            </form>
        </div></div>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/counselor/createRecoveryPlan.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
