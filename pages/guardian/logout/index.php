<?php
/**
 * /guardian/logout - clears JWT cookie and redirects to guardian login.
 */
Auth::clearTokenCookie('guardian');
PharmacyContext::clearSelectedPharmacy();
Response::redirect('/guardian/login');
