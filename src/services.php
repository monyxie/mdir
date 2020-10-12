<?php

use Monyxie\Mdir\Filesystem\Jail;
use Monyxie\Mdir\Filesystem\Lister;
use Monyxie\Mdir\Http\Controller;
use Monyxie\Mdir\Markdown\Commonmark;
use Monyxie\Mdir\Markdown\Parsedown;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

return [
    'jail' => function () {
        return new Jail($this->config['markdown_dir']);
    },
    'router' => function () {
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
        switch ($this->config['markdown_parser'] ?? null) {
            case 'commonmark':
                return new Commonmark();
            case 'parsedown':
                return new Parsedown();
            default:
                throw new \Exception('Invalid config "markdown_parser": ' . $this->config['markdown_parser']);
        }
    },
    'lister' => function ($c) {
        return new Lister(
            $c['jail'],
            array_merge($c['config']['markdown_extensions'], $c['config']['extra_extensions']),
            $c['config']['excluded_dirs']
        );
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
