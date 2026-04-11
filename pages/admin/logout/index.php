<?php
/**
 * /admin/logout - clears JWT cookie and redirects to admin login.
 */
Auth::clearTokenCookie('admin');
PharmacyContext::clearSelectedPharmacy();
Response::redirect('/admin/login');
