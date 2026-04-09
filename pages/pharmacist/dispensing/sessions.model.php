<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorSessionsModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getSessionsByCounselor($counselorId);
    }
}
