<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

final readonly class UpdateTablePaginationPreferences
{
    /**
     * Persist a user's per-table records-per-page choice.
     *
     * Filament keeps the per-page selection in the session by default, which
     * resets on session expiry (and never crosses devices). Storing it per
     * user in the DB makes the chosen page size stick across sessions and
     * browsers, mirroring the table column manager state.
     *
     * @param  array<string, int|string>  $preferences
     */
    public function execute(User $user, array $preferences): void
    {
        $user->update(['table_pagination_preferences' => $preferences]);
    }
}
