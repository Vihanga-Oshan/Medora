<?php

$clientId = (int) (Request::get('id') ?? 0);
if ($clientId <= 0) {
    Response::redirect('/pharmacist/patients');
}

$clientProfile = CounselorClientProfileModel::getProfile((int) ($user['counselorId'] ?? 0), $clientId);
if (!$clientProfile) {
    Response::redirect('/pharmacist/patients');
}
