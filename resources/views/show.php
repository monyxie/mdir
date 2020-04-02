<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lib/purecss/pure-min.css">
    <link rel="stylesheet" href="/lib/github-markdown-css/github-markdown.min.css">
    <link rel="stylesheet" href="/css/sidemenu.css">
    <link rel="stylesheet" href="/css/style.css">
    <title><?= ($title ? $view->escape($title) . ' - ' : '') . $app_name ?></title>
</head>
<body>

<div id="layout">
    <!-- Menu toggle -->
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>

    <div id="menu">
        <div class="pure-menu">
            <a class="pure-menu-heading branding" href="<?= $view->escape('/') ?>"><?= $view->escape($app_name) ?></a>
            <?php if (!empty($ups)) { ?>
                <?php array_shift($ups);
                foreach ($ups as $up) { ?>
                    <a class="pure-menu-heading breadcrumb"
                       href="<?= $view->escape($up['link']) ?>">&raquo; <?= $view->escape($up['base_name']) ?></a>
                <?php } ?>
            <?php } ?>

            <ul class="pure-menu-list">

                <?php if (!empty($files)) { ?>
                    <?php foreach ($files as $file) { ?>
                        <li class="pure-menu-item <?= $file['is_current'] ? 'pure-menu-selected' : '' ?> ">
                            <a class="pure-menu-link file-link"
                               href="<?= $view->escape($file['link']) ?>"><?= $view->escape($file['base_name']) ?></a>
                        </li>
                    <?php } ?>
                <?php } ?>

                <?php if (!empty($directories)) { ?>
                    <?php foreach ($directories as $directory) { ?>
                        <li class="pure-menu-item">
                            <a class="pure-menu-link directory-link" href="<?= $view->escape($directory['link']) ?>">
                                [<?= $view->escape($directory['base_name']) ?>]
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div id="main">
        <?php if ($title ?? false) { ?>
            <div class="header">
                <h1><?= $title ?></h1>
                <?php if ($subtitle ?? false) { ?><h2><?= $subtitle ?></h2><?php } ?>
            </div>
        <?php } ?>

        <div id="content" class="markdown-body content">
            <?= $content ?>
        </div>
    </div>
</div>

<script src="/js/ui.js"></script>

</body>
</html>