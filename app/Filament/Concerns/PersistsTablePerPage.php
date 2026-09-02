<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Actions\User\UpdateTablePaginationPreferences;
use App\Models\User;

/**
 * Persist the Filament records-per-page selection per user in the database
 * instead of the session.
 *
 * Filament's CanPaginateRecords stores the chosen page size under
 * `tables.{md5(class)}_per_page` in the session by default, so it dies with
 * the session (120 min TTL) and never crosses devices. This trait swaps the
 * storage backend for the same value, keyed by user + table.
 *
 * Add to any list page that should remember the page size:
 *
 *     use PersistsTablePerPage;
 *
 * Requires the `table_pagination_preferences` jsonb column on `users` and the
 * `UpdateTablePaginationPreferences` action (both shipped alongside this trait).
 */
trait PersistsTablePerPage
{
    public function getDefaultTableRecordsPerPageSelectOption(): int | string
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $key = $this->getTablePerPageSessionKey();
            $stored = $user->table_pagination_preferences[$key] ?? null;

            $pageOptions = $this->getTable()->getPaginationPageOptions();

            if ($stored !== null && in_array($stored, $pageOptions, strict: true)) {
                // Re-seed the session so Filament's own reset paths agree.
                session()->put($key, $stored);

                return $stored;
            }
        }

        return parent::getDefaultTableRecordsPerPageSelectOption();
    }

    public function updatedTableRecordsPerPage(): void
    {
        parent::updatedTableRecordsPerPage();

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $preferences = $user->table_pagination_preferences ?? [];
        $preferences[$this->getTablePerPageSessionKey()] = $this->getTableRecordsPerPage();

        app(UpdateTablePaginationPreferences::class)->execute($user, $preferences);
    }
}
