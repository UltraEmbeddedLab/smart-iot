<div>
    <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <article
            x-data="{
                copied: false,
                async copyMarkdown() {
                    try {
                        const response = await fetch(@js(route('docs.raw', ['slug' => $slug])));
                        const text = await response.text();
                        await navigator.clipboard.writeText(text);
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 1500);
                    } catch (error) {
                        console.error('Failed to copy markdown', error);
                    }
                },
            }"
        >
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">
                        {{ __('Documentation') }}
                    </p>
                    <h1 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
                        {{ $this->document['title'] }}
                    </h1>
                </div>

                <flux:dropdown position="bottom" align="end" class="shrink-0">
                    <flux:button
                        size="sm"
                        icon="document-duplicate"
                        icon:trailing="chevron-down"
                    >
                        <span x-text="copied ? @js(__('Copied!')) : @js(__('Copy page'))"></span>
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            as="button"
                            type="button"
                            icon="document-duplicate"
                            x-on:click="copyMarkdown()"
                        >
                            <div class="flex flex-col">
                                <span>{{ __('Copy page') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Copy page as Markdown for LLMs') }}</span>
                            </div>
                        </flux:menu.item>

                        <flux:menu.item
                            :href="route('docs.raw', ['slug' => $slug])"
                            target="_blank"
                            rel="noopener"
                            icon="arrow-top-right-on-square"
                        >
                            <div class="flex flex-col">
                                <span>{{ __('View as Markdown') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('View this page as plain text') }}</span>
                            </div>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div id="docs-content" class="docs-prose">
                {!! $this->document['html'] !!}
            </div>

            {{-- Pager --}}
            @php($pager = $this->pager)
            @if ($pager['prev'] || $pager['next'])
                <nav class="mt-16 flex items-center justify-between gap-4 border-t border-zinc-200 pt-8 dark:border-zinc-800" aria-label="{{ __('Page navigation') }}">
                    <div class="flex-1">
                        @if ($pager['prev'])
                            <a href="{{ route('docs.show', ['slug' => $pager['prev']['slug']]) }}" wire:navigate class="group inline-flex flex-col rounded-lg border border-zinc-200 p-4 transition hover:border-violet-300 hover:bg-violet-50/40 dark:border-zinc-800 dark:hover:border-violet-700 dark:hover:bg-violet-950/20">
                                <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Previous') }}</span>
                                <span class="mt-1 text-sm font-semibold text-zinc-900 group-hover:text-violet-700 dark:text-white dark:group-hover:text-violet-300">&larr; {{ $pager['prev']['title'] }}</span>
                            </a>
                        @endif
                    </div>
                    <div class="flex-1 text-right">
                        @if ($pager['next'])
                            <a href="{{ route('docs.show', ['slug' => $pager['next']['slug']]) }}" wire:navigate class="group inline-flex flex-col rounded-lg border border-zinc-200 p-4 text-right transition hover:border-violet-300 hover:bg-violet-50/40 dark:border-zinc-800 dark:hover:border-violet-700 dark:hover:bg-violet-950/20">
                                <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Next') }}</span>
                                <span class="mt-1 text-sm font-semibold text-zinc-900 group-hover:text-violet-700 dark:text-white dark:group-hover:text-violet-300">{{ $pager['next']['title'] }} &rarr;</span>
                            </a>
                        @endif
                    </div>
                </nav>
            @endif
        </article>
    </div>
</div>
