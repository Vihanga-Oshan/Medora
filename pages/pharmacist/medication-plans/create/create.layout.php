<?php $activePage = 'recovery'; $prefilledUserId = (int) (Request::get('userId') ?? 0); ?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Create Recovery Plan'; $pageStyle = ['counselor/createRecoveryPlan']; require __DIR__ . '/../../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />
        <div class="main-content-header"><div class="main-content-header-text"><a href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans" class="back-btn"><i data-lucide="arrow-left" class="back-icon" stroke-width="1"></i></a><h2>Create Recovery Plan</h2></div></div>
        <div class="main-content-body">
            <div class="inner-body-content row">
                <div class="createPlan-container">
                    <?php if (!empty($errorMessage)): ?><div class="error-message"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
                    <div class="ai-assistant-section">
                        <div class="ai-assistant-header"><i data-lucide="sparkles" class="ai-icon" stroke-width="1"></i><span>AI Plan Assistant</span></div>
                        <div class="ai-assistant-body">
                            <textarea id="aiPrompt" placeholder="Describe the recovery plan you want to create..."></textarea>
                            <button type="button" id="generatePlanBtn" class="btn btn-primary"><i data-lucide="sparkles" stroke-width="1"></i> Generate Plan</button>
                        </div>
                    </div>
                    <form id="createPlan-form" action="" method="post">
                        <h3>Plan Overview</h3>
                        <div class="createPlan-group">
                            <label>Assigned to</label>
                            <button type="button" class="btn btn-secondary add-client-btn" onclick="showClientDropdown()">+ Add Client</button>
                            <select id="assignedTo" name="userId" style="display: none">
                                <option value="" disabled <?= $prefilledUserId <= 0 ? 'selected' : '' ?>>Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= (int) $client['id'] ?>" <?= $prefilledUserId === (int) $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div id="selectedClient" class="selected-client" style="<?= $prefilledUserId > 0 ? 'display:flex' : 'display:none' ?>">
                                <?php if ($prefilledUserId > 0): $selected = array_values(array_filter($clients, fn($c) => (int) $c['id'] === $prefilledUserId)); if (!empty($selected)): ?><span><?= htmlspecialchars($selected[0]['name']) ?></span> <span class="remove-btn" onclick="removeClient()">×</span><?php endif; endif; ?>
                            </div>
                        </div>
                        <div class="createPlan-group"><label for="title">Plan Title</label><input id="title" name="title" type="text" placeholder="Enter plan title" /></div>
                        <div class="createPlan-group"><label for="planGoal">Plan Goal</label><input id="planGoal" name="planGoal" type="text" placeholder="Enter the main goal of this plan" /></div>
                        <input type="hidden" id="planType" name="planType" value="counselor" />
                        <input type="hidden" id="planStatus" name="planStatus" value="draft" />
                        <div class="createPlan-group"><label>Time Period</label><div class="column"><input type="date" id="startDate" name="startDate" /><input type="date" id="endDate" name="targetCompletionDate" /></div></div>
                        <div class="createPlan-group"><label for="description">Description</label><textarea id="description" name="description" placeholder="Describe the recovery plan..."></textarea></div>
                        <h3>Phases</h3>
                        <div id="phases-container">
                            <?php foreach ([1 => 'Stabilization', 2 => 'Reduction', 3 => 'Maintenance'] as $phaseNum => $phaseName): ?>
                                <div class="phase" data-phase="<?= $phaseNum ?>"><span class="phase-name">Phase <?= $phaseNum ?>: <?= htmlspecialchars($phaseName) ?></span><div class="phase-actions"><button type="button" class="btn btn-link phase-btn" onclick="addTask(<?= $phaseNum ?>)">+ Add Task</button><button type="button" class="btn btn-link phase-btn" onclick="addMilestone(<?= $phaseNum ?>)">+ Add Milestone</button></div></div>
                                <div class="phase-tasks" id="phase-<?= $phaseNum ?>-tasks"></div>
                            <?php endforeach; ?>
                        </div>
                        <h3>Recovery Goals</h3>
                        <div class="goals-inputs">
                            <div class="goal-input-group"><label>Short-term Goal</label><input type="text" name="shortTermGoalTitle" placeholder="e.g., Complete first week sober" /><div class="goal-days-input"><label>Target Days:</label><input type="number" name="shortTermGoalDays" placeholder="7" min="1" max="365" /></div></div>
                            <div class="goal-input-group"><label>Long-term Goal</label><input type="text" name="longTermGoalTitle" placeholder="e.g., Complete full recovery program" /><div class="goal-days-input"><label>Target Days:</label><input type="number" name="longTermGoalDays" placeholder="90" min="1" max="365" /></div></div>
                        </div>
                        <h3>Care Notes</h3>
                        <div class="createPlan-group"><label for="notes">Notes</label><textarea id="notes" name="customNotes" placeholder="Add any additional notes..."></textarea></div>
                        <div class="form-actions"><button type="submit" class="btn btn-primary">Save Changes</button><button type="button" class="btn btn-secondary" onclick="exportPDF()">Export PDF</button></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/counselor/createRecoveryPlan.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
