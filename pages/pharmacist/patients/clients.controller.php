<?php

$search = trim((string) (Request::get('nic') ?? ''));
$pharmacyId = (int) ($currentPharmacyId ?? 0);
$patientList = PatientsClientsModel::getAll($search, $pharmacyId);
