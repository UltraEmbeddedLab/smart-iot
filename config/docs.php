<?php declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation root directory
    |--------------------------------------------------------------------------
    |
    | Absolute path to the directory that holds the markdown source files.
    | Slugs configured below are resolved relative to this directory.
    |
    */

    'path' => resource_path('docs'),

    /*
    |--------------------------------------------------------------------------
    | Default document
    |--------------------------------------------------------------------------
    |
    | The slug that is displayed when the user visits /docs without passing
    | any explicit page. This is the "landing" entry of the documentation.
    |
    */

    'default' => 'introduction',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Rendered markdown is cached by file path and modification timestamp so
    | that unchanged documents never have to be re-parsed. Disable this in
    | local development if you want to see edits reflect instantly.
    |
    */

    'cache' => [
        'enabled' => env('DOCS_CACHE', true),
        'ttl' => 60 * 60 * 24, // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar navigation tree
    |--------------------------------------------------------------------------
    |
    | Each section contains a list of items. Items reference a slug that maps
    | to a markdown file on disk (e.g. "getting-started/introduction" maps to
    | resources/docs/getting-started/introduction.md). To extend the docs,
    | drop a new markdown file into place and register it here.
    |
    | Optional per-item keys:
    |   - badge: short label displayed next to the title (e.g. "new", "beta")
    |   - hidden: when true, the page is reachable by slug but hidden in the tree
    |
    */

    'sections' => [
        [
            'title' => 'Getting Started',
            'items' => [
                ['title' => 'Introduction', 'slug' => 'introduction'],
            ],
        ],
    ],

];
