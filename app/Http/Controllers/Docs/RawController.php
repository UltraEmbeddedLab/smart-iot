<?php declare(strict_types=1);

namespace App\Http\Controllers\Docs;

use App\Actions\Docs\ResolveDocumentPath;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Serves the raw markdown source of a documentation page so that it can
 * be opened in a browser tab as plain text or copied by LLM-facing tools.
 */
final class RawController extends Controller
{
    public function __invoke(ResolveDocumentPath $resolver, ?string $slug = null): Response
    {
        $slug ??= (string) config('docs.default', 'introduction');

        $path = $resolver->handle($slug);

        if ($path === null) {
            abort(404);
        }

        $contents = (string) file_get_contents($path);

        return response($contents, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
