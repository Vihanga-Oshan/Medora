<?php
/**
 * /patient/logout - clears JWT cookie and redirects to patient login.
 */
Auth::clearTokenCookie('patient');
PharmacyContext::clearSelectedPharmacy();
Response::redirect('/patient/login');
