<?php
/**
 * /pharmacist/logout - clears JWT cookie and redirects to pharmacist login.
 */
Auth::clearTokenCookie('pharmacist');
PharmacyContext::clearSelectedPharmacy();
Response::redirect('/pharmacist/login');
