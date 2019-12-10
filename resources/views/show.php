<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lib/purecss/pure-min.css">
    <link rel="stylesheet" href="/lib/purecss/grids-responsive-min.css">
    <link rel="stylesheet" href="/lib/github-markdown-css/github-markdown.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <title><?= $view->escape($title) ?></title>
</head>
<body>
<div id="layout" class="pure-g">
    <div id="sidebar" class="sidebar pure-u-1 pure-u-md-1-4">
        <div class="header">
            <h1 class="brand-title"><?= $view->escape($title) ?></h1>
            <nav class="nav">
                <?php if (!empty($ups)) { ?>
                    <p>Go up to:</p>
                    <ul class="nav-list">
                        <?php foreach ($ups as $directory => $path) { ?>
                            <li class="nav-item"><a href="<?= $view->escape($path) ?>"><?= $view->escape($directory) ?></a></li>
                            <li class="nav-item">/</li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (empty($directories) && empty($files)) { ?><p>This directory is empty.</p><?php } ?>
                <?php if (!empty($directories)) { ?>
                    <p>Subdirectories:</p>
                    <ul class="nav-list">
                        <?php foreach ($directories as $directory => $path) { ?>
                            <li class="nav-item"><a style="background-color: <?= $path['color'] ?>" href="<?= $view->escape($path['path']) ?>"><?= $view->escape($directory) ?></a></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (!empty($files)) { ?>
                    <p>Files:</p>
                    <ul class="nav-list">
                        <?php foreach ($files as $file => $path) { ?>
                            <li class="nav-item"><a style="background-color: <?= $path['color'] ?>" href="<?= $view->escape($path['path']) ?>"><?= $view->escape($file) ?></a></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </nav>
        </div>
    </div>
    <div id="content" class="markdown-body content pure-u-1 pure-u-md-3-4">
        <?= $content ?>
    </div>
</div>
</body>
</html>