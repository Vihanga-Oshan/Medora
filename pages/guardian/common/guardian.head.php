<?php

/**
 * Guardian head guard — include at the top of every guardian page index.php.
 * 
 * 1. Verifies the JWT cookie exists and is valid
 * 2. Confirms the role is 'guardian'
 * 3. Sets $user = ['id', 'name', 'role', 'iat', 'exp']
 * 4. Redirects to /auth/login on any failure
 */
$user = Auth::requireRole('guardian');
