<?php declare(strict_types=1);

namespace App\Livewire\Docs;

use App\Actions\Docs\RenderDocument;
use App\Actions\Docs\ResolveDocumentPath;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Renders a single documentation page.
 *
 * Slugs are resolved against config/docs.php so that new pages can be
 * added simply by dropping a markdown file into resources/docs and
 * registering it in the sidebar tree. Heavy lifting (markdown parsing,
 * table of contents, front matter) is delegated to RenderDocument.
 *
 * @phpstan-type TocEntry array{id: string, title: string, level: int}
 * @phpstan-type NavItem array{title: string, slug: string, badge?: string, hidden?: bool}
 */
#[Title('Documentation')]
final class Show extends Component
{
    public string $slug = '';

    /**
     * @throws Exception
     */
    public function mount(?string $slug = null): void
    {
        $default = (string) config('docs.default', 'introduction');
        $this->slug = mb_trim(str_replace('\\', '/', $slug ?? $default), '/');

        if ($this->absolutePath() === null) {
            abort(404);
        }
    }

    /**
     * Render the current page through the markdown pipeline.
     *
     * @return array{html: string, toc: list<TocEntry>, title: string, meta: array<string, mixed>}
     *
     * @throws Exception
     */
    #[Computed]
    public function document(): array
    {
        /** @var string $absolutePath */
        $absolutePath = $this->absolutePath();

        return app(RenderDocument::class)->handle($absolutePath);
    }

    /**
     * Previous and next sibling pages within the flattened sidebar order,
     * used by the footer of the content column to walk the documentation
     * sequentially.
     *
     * @return array{prev: ?NavItem, next: ?NavItem}
     */
    #[Computed]
    public function pager(): array
    {
        $flat = $this->flattenNavigation();

        $index = null;

        foreach ($flat as $position => $item) {
            if ($item['slug'] === $this->slug) {
                $index = $position;
                break;
            }
        }

        if ($index === null) {
            return ['prev' => null, 'next' => null];
        }

        return [
            'prev' => $flat[$index - 1] ?? null,
            'next' => $flat[$index + 1] ?? null,
        ];
    }

    public function render(): View
    {
        return view('livewire.docs.show');
    }

    /**
     * Flatten the sidebar tree into a linear list of navigable items so
     * that both the pager and the current-page lookup share one source
     * of truth for page ordering.
     *
     * @return list<NavItem>
     */
    private function flattenNavigation(): array
    {
        $items = [];

        foreach ((array) config('docs.sections', []) as $section) {
            foreach ((array) ($section['items'] ?? []) as $item) {
                if ($item['hidden'] ?? false) {
                    continue;
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Resolve the slug to an absolute markdown file path via the shared
     * resolver so the raw markdown route and Livewire view stay in sync.
     *
     * @throws Exception
     */
    private function absolutePath(): ?string
    {
        return app(ResolveDocumentPath::class)->handle($this->slug);
    }
}
