<?php

use Monyxie\Mdir\Config\ConfigurationLoader;
use Monyxie\Mdir\Filesystem\Jail;
use Monyxie\Mdir\Filesystem\Lister;
use Monyxie\Mdir\Http\Controller;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

return [
    'config' => function () {
        $configDirectories = [__DIR__ . '/../config'];
        $fileLocator = new FileLocator($configDirectories);
        $filepath = $fileLocator->locate('app.php', null, true);
        $loader = new ConfigurationLoader($fileLocator);
        $config = $loader->load($filepath);
        return $config;
    },
    'jail' => function () {
        return new Jail($this->config['markdown_dir']);
    },
    'router' => function ($c) {
        $routes = new RouteCollection();
        $routes->add('index', new Route('/', ['_controller' => ['controller', 'show']]));
        $routes->add('show', new Route('/{path}', ['_controller' => ['controller', 'show']], ['path' => '.+']));
        return new UrlMatcher($routes, new RequestContext('/'));
    },
    'template' => function () {
        $filesystemLoader = new FilesystemLoader(__DIR__ . '/../resources/views/%name%');
        return new PhpEngine(new TemplateNameParser(), $filesystemLoader);
    },
    'markdown' => function () {
        return new Parsedown();
    },
    'lister' => function ($c) {
        return new Lister($c['jail'], $c['config']['markdown_extensions'], $c['config']['extra_extensions']);
    },
    'controller' => function ($c) {
        return new Controller(
            $c['lister'],
            $c['jail'],
            $c['template'],
            $c['markdown'],
            $c['config']
        );
    }
];
