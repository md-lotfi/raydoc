<?php

namespace App\Services;

use App\Models\BillingCode;
use App\Models\TherapySession;

class BillingService
{
    /**
     * Finds the appropriate BillingCode based on the actual duration of a therapy session.
     *
     * @param  int  $sessionId  The ID of the TherapySession record.
     * @return BillingCode|null The matching BillingCode model instance, or null if none is found.
     *
     * @throws \Exception If the session is not billable (e.g., cancelled, not started).
     */
    public static function getBillingCodeForSession(int $sessionId): ?BillingCode
    {
        // 1. Fetch the Session and Validate State
        $session = TherapySession::find($sessionId);

        if (! $session) {
            return null; // Session not found
        }

        // --- Determine Actual Duration ---

        // Ensure session is complete and has start/end times
        if (
            $session->status !== 'Completed' ||
            ! $session->duration_minutes
        ) {
            // Check for specific billable non-completed statuses (like 'No Show') if your rules allow.
            // For simplicity, we only allow 'Completed' with actual times here.
            throw new \Exception("Session ID {$sessionId} is not in a billable state (must be Completed with actual times).");
        }

        // Calculate the actual duration in minutes
        $actualDurationMinutes = $session->duration_minutes; // $end->diffInMinutes($start);

        // 2. Find the Matching Billing Code

        // Query the active billing codes that match the duration criteria.
        // The query looks for codes where the duration fits *between* the min/max fields.
        $billingCode = BillingCode::query()
            ->where('is_active', true)
            // Rule 1: Code must have min_duration_minutes <= actual duration OR min_duration_minutes is null
            ->where(function ($query) use ($actualDurationMinutes) {
                $query->whereNull('min_duration_minutes')
                    ->orWhere('min_duration_minutes', '<=', $actualDurationMinutes);
            })
            // Rule 2: Code must have max_duration_minutes >= actual duration OR max_duration_minutes is null
            ->where(function ($query) use ($actualDurationMinutes) {
                $query->whereNull('max_duration_minutes')
                    ->orWhere('max_duration_minutes', '>=', $actualDurationMinutes);
            })
            // Optional: Order by most specific match (e.g., shortest range first) if multiple codes overlap
            // ->orderByRaw('max_duration_minutes - min_duration_minutes ASC')
            ->first();

        // 3. Return Result
        if (! $billingCode) {
            throw new \Exception("No active billing code found for an actual session duration of {$actualDurationMinutes} minutes.");
        }

        return $billingCode;
    }
}
