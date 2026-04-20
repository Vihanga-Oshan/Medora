<?php

Auth::clearTokenCookie('pharmacist');
PharmacyContext::clearSelectedPharmacy();
Response::redirect('/pharmacist/login');
