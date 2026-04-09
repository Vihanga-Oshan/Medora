<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorApplicationsModel
{
    public static function submit(array $input): array
    {
        return CounselorData::createApplication($input);
    }
}
