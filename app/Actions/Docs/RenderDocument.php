<?php declare(strict_types=1);

namespace App\Actions\Docs;

use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use RuntimeException;

/**
 * Converts a markdown documentation file into rendered HTML plus a
 * structured table of contents and front-matter metadata that the
 * documentation Livewire component can consume.
 *
 * @phpstan-type TocEntry array{id: string, title: string, level: int}
 * @phpstan-type RenderedDocument array{
 *     html: string,
 *     toc: list<TocEntry>,
 *     title: string,
 *     meta: array<string, mixed>,
 * }
 */
final class RenderDocument
{
    /**
     * Render the markdown file located at the given absolute path.
     *
     * @return RenderedDocument
     */
    public function handle(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('Documentation file not found: '.$absolutePath);
        }

        if (! config('docs.cache.enabled', true)) {
            return $this->render($absolutePath);
        }

        $key = 'docs:'.md5($absolutePath).':'.filemtime($absolutePath);

        return Cache::remember(
            $key,
            (int) config('docs.cache.ttl', 86400),
            fn (): array => $this->render($absolutePath),
        );
    }

    /**
     * @return RenderedDocument
     */
    private function render(string $absolutePath): array
    {
        $markdown = (string) file_get_contents($absolutePath);

        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'docs-anchor',
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'fragment_prefix' => '',
                'insert' => 'after',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'title' => 'Permalink',
                'symbol' => '#',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addExtension(new FrontMatterExtension);

        try {
            $converter = new MarkdownConverter($environment);
            $rendered = $converter->convert($markdown);
        } catch (CommonMarkException $exception) {
            throw new RuntimeException(
                'Failed to parse markdown file ['.$absolutePath.']: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $meta = $rendered instanceof RenderedContentWithFrontMatter
            ? (array) $rendered->getFrontMatter()
            : [];

        $html = $rendered->getContent();

        return [
            'html' => $html,
            'toc' => $this->extractToc($html),
            'title' => $this->resolveTitle($markdown, $meta),
            'meta' => $meta,
        ];
    }

    /**
     * Parse the rendered HTML for heading elements and build a flat list
     * the right sidebar can render as an "On this page" menu. Parsing
     * the output (rather than the markdown source) guarantees IDs stay
     * in sync with the HeadingPermalink extension.
     *
     * @return list<array{id: string, title: string, level: int}>
     */
    private function extractToc(string $html): array
    {
        preg_match_all(
            '/<h([23])\b[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/h\1>/is',
            $html,
            $matches,
            PREG_SET_ORDER,
        );

        $entries = [];

        foreach ($matches as $match) {
            $level = (int) $match[1];
            $id = $match[2];
            $innerHtml = $match[3];

            $withoutAnchors = (string) preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $innerHtml);
            $title = mb_trim(html_entity_decode(strip_tags($withoutAnchors), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($title === '') {
                continue;
            }

            $entries[] = [
                'id' => $id,
                'title' => $title,
                'level' => $level,
            ];
        }

        return $entries;
    }

    /**
     * Resolve the document title, preferring explicit front-matter then
     * the first level 1 heading of the source document.
     *
     * @param  array<string, mixed>  $meta
     */
    private function resolveTitle(string $markdown, array $meta): string
    {
        if (isset($meta['title']) && is_string($meta['title']) && $meta['title'] !== '') {
            return $meta['title'];
        }

        $stripped = $this->stripFrontMatter($markdown);

        if (preg_match('/^#\s+(.+?)\s*$/m', $stripped, $matches) === 1) {
            return mb_trim($matches[1]);
        }

        return 'Documentation';
    }

    private function stripFrontMatter(string $markdown): string
    {
        if (str_starts_with($markdown, '---')) {
            return (string) preg_replace('/^---.*?---\s*/s', '', $markdown);
        }

        return $markdown;
    }
}
