<?php

$ownerId = (int) ($user['counselorId'] ?? $user['id'] ?? 0);
$plans = CounselorRecoveryPlansModel::getAll($ownerId);
