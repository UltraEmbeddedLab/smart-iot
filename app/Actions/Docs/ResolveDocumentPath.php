<?php declare(strict_types=1);

namespace App\Actions\Docs;

/**
 * Resolves a documentation slug to an absolute markdown file path.
 *
 * Shared between the docs Livewire component and the raw markdown
 * route so both use the exact same safety rules (no directory
 * traversal, must live beneath the configured docs root).
 */
final class ResolveDocumentPath
{
    public function handle(string $slug): ?string
    {
        $normalized = mb_trim(str_replace('\\', '/', $slug), '/');

        if ($normalized === '' || str_contains($normalized, '..')) {
            return null;
        }

        $base = (string) config('docs.path', resource_path('docs'));
        $path = $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized).'.md';

        return is_file($path) ? $path : null;
    }
}
