<?php

namespace App\Observers;

use App\Models\Grade;

class GradeObserver
{
    /**
     * Handle the Grade "creating" event.
     */
    public function creating(Grade $grade): void
    {
        if ($grade->student_id && !$grade->student_name) {
            $grade->student_name = $grade->student?->name;
        }
    }

    /**
     * Handle the Grade "updating" event.
     */
    public function updating(Grade $grade): void
    {
        if ($grade->student_id && !$grade->student_name) {
            $grade->student_name = $grade->student?->name;
        }
    }

    /**
     * Handle the Grade "deleted" event.
     */
    public function deleted(Grade $grade): void
    {
        //
    }

    /**
     * Handle the Grade "restored" event.
     */
    public function restored(Grade $grade): void
    {
        //
    }

    /**
     * Handle the Grade "force deleted" event.
     */
    public function forceDeleted(Grade $grade): void
    {
        //
    }
}
