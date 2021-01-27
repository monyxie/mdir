<?php

return [
    /**
     * The name of the application. Shown in page title and the sidebar.
     */
    'app_name' => 'MDir',

    /**
     * Whether debug mode should be turned on.
     */
    'debug' => false,

    /**
     * The path to your markdown directory, where all your markdown files lie.
     */
    'markdown_dir' => '/home/xwn/work',

    /**
     * Possible file extensions of your markdown files.
     */
    'markdown_extensions' => ['md'],

    /**
     * Extra file extensions that should be rendered as plain text.
     */
    'extra_extensions' => ['txt'],

    /**
     * Directory names that should be excluded during traversal. This applies to the entire directory tree.
     */
    'excluded_dirs' => ['_v_recycle_bin', '_v_images'],

    /**
     * Binary file extensions that should be returned as-is, in binary.
     */
    'binary_extensions' => ['bmp', 'jpg', 'jpeg', 'png', 'gif'],

    /**
     * Code highlight style. Look in the directory `public/lib/highlight.js/styles` for available styles.
     */
    'code_highlight_style' => 'github',

    /**
     * Markdown parser. Possible values: 'commonmark', 'parsedown'.
     * Commonmark supports more features and thus is recommended.
     */
    'markdown_parser' => 'commonmark',
];