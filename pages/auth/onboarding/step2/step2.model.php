<?php

/**
 * Step 2 Model: Substance Use Info
 */
class Step2Model
{
    /**
     * Store substance usage info into user_profiles table 
     * and increment the user's current onboarding step.
     */
    public static function saveSubstanceInfo(int $userId, string $primarySubstance, string $frequency, string $lastUsed, int $quitAttempts): bool
    {
        Database::setUpConnection();

        $safeUserId    = (int) $userId;
        $safeSubstance = Database::$connection->real_escape_string($primarySubstance);

        // Insert/Update user_profiles with substance info
        Database::iud(
            "INSERT INTO user_profiles (user_id, recovery_type) 
             VALUES ($safeUserId, '$safeSubstance') 
             ON DUPLICATE KEY UPDATE recovery_type = '$safeSubstance'"
        );

        // Advance step
        Database::iud(
            "UPDATE users SET current_onboarding_step = 3 WHERE user_id = $safeUserId"
        );

        return true;
    }
}
