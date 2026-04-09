<?php

$sessions = CounselorSessionsModel::getAll((int) ($user['counselorId'] ?? 0));
$searchPlaceholder = 'Search sessions';
