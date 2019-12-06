<?php

namespace Monyxie\Mdir;

use Parsedown;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class Application
{
    /**
     * @var UrlMatcher
     */
    private $router;
    /**
     * @var TemplateEngine
     */
    private $template;
    /**
     * @var array
     */
    private $config;
    /**
     * @var Parsedown
     */
    private $markdown;
    /**
     * @var DirJail
     */
    private $dirJail;

    public function __construct()
    {
        ErrorHandler::register();

        $this->config = $this->buildConfig();

        if ($this->config['debug']) {
            Debug::enable();
        } else {
            ini_set('display_errors', 'off');
        }

        if (!is_dir($this->config['markdown_dir'])) {
            die('Change "markdown_dir" to point to your markdown directory in app.php.');
        }

        $this->dirJail = new DirJail($this->config['markdown_dir']);
        $this->router = $this->buildUrlMatcher();
        $this->template = $this->buildTemplateEngine();
        $this->markdown = $this->buildParsedown();
    }

    public function handle(Request $request): Response
    {
        try {
            $parameters = $this->router->matchRequest($request);
        } catch (ResourceNotFoundException $e) {
            return new Response($this->template->render('404.php'));
        }

        $method = 'action' . ucfirst($parameters['_action']);
        return $this->{$method}($request, $parameters);
    }

    private function actionShow(Request $request, $params)
    {
        return $this->showPath($params['path'] ?? '');
    }

    /**
     * @return RouteCollection
     */
    private function buildRouteCollection(): RouteCollection
    {
        $routes = new RouteCollection();
        $routes->add('index', new Route('/', ['_action' => 'show']));
        $routes->add('show', new Route('/{path}', ['_action' => 'show'], ['path' => '.+']));

        return $routes;
    }

    /**
     * @return UrlMatcher
     */
    private function buildUrlMatcher(): UrlMatcher
    {
        return new UrlMatcher($this->buildRouteCollection(), new RequestContext('/'));
    }

    /**
     * @return TemplateEngine
     */
    private function buildTemplateEngine(): TemplateEngine
    {
        $filesystemLoader = new FilesystemLoader(__DIR__ . '/../resources/views/%name%');

        return new PhpEngine(new TemplateNameParser(), $filesystemLoader);
    }

    private function buildConfig()
    {
        $configDirectories = [__DIR__ . '/../config'];

        $fileLocator = new FileLocator($configDirectories);
        $filepath = $fileLocator->locate('app.php', null, true);

        $loader = new ConfigurationLoader($fileLocator);

        $config = $loader->load($filepath);

        return $config;
    }

    private function buildParsedown(): Parsedown
    {
        return new Parsedown();
    }

    /**
     * @return Response
     */
    private function show404(): Response
    {
        return new Response($this->template->render('404.php'));
    }

    /**
     * @param $path
     * @return false|string
     */
    private function resolvePath($path)
    {
        return $this->dirJail->resolveRelative($path);
    }

    /**
     * @param string $filename
     * @return string
     */
    private function renderFile(string $filename): string
    {
        foreach ($this->config['markdown_extensions'] as $ext) {
            if (mb_substr($filename, 0 - mb_strlen($ext) - 1) === '.' . $ext) {
                return $this->markdown->text(file_get_contents($filename));
            }
        }

        foreach ($this->config['extra_extensions'] as $ext) {
            if (mb_substr($filename, 0 - mb_strlen($ext) - 1) === '.' . $ext) {
                return '<pre><code>' . htmlspecialchars(file_get_contents($filename)) . '</code></pre>';
            }
        }

        return '';
    }

    /**
     * @param string $filename
     * @return array
     */
    private function listDirectory(string $filename): array
    {
        $exts = array_merge($this->config['markdown_extensions'], $this->config['extra_extensions']);
        $directories = $files = [];

        foreach (Finder::create()->in($filename)->depth(0)->directories() as $subdirectory) {
            $relativePath = $this->dirJail->resolveAbsolute($subdirectory, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $directories [basename($subdirectory)] = $link;
        }

        $patterns = [];
        foreach ($exts as $ext) {
            $patterns [] = '*.' . $ext;
        }
        foreach (Finder::create()->in($filename)->files()->depth(0)->name($patterns) as $file) {
            $relativePath = $this->dirJail->resolveAbsolute($file, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $files [basename($file)] = $link;
        }
        return array($files, $directories);
    }

    /**
     * @param $path
     * @return Response
     */
    private function showPath($path): Response
    {
        $path = $this->resolvePath($path ?? '');
        if (!$path) {
            return $this->show404();
        }

        if (is_file($path)) {
            $file = $path;
            $dir = dirname($file);
        } else {
            $file = $path . '/index.md';
            $dir = $path;

            if (is_file($file)) {
                $relativePath = $this->dirJail->resolveAbsolute($file, true);
                $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                return RedirectResponse::create($link);
            }
        }

        list($files, $directories) = $this->listDirectory($dir);
        $ups = $this->listUps($dir);
        $params = [
            'title' => $this->config['app_name'],
            'files' => $files,
            'directories' => $directories,
            'content' => is_file($file) ? $this->renderFile($file) : '',
            'ups' => $ups,
        ];

        return new Response($this->template->render('show.php', $params));
    }

    private function listUps(string $dir)
    {
        $ups = [];
        while ($up = $this->dirJail->resolveAbsolute(dirname($dir))) {
            $relativePath = $this->dirJail->resolveAbsolute($up, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($relativePath !== '') {
                $ups[basename($up)] = $link;
            } else {
                $ups['home'] = $link;
            }
            $dir = $up;
        }
        return array_reverse($ups, true);
    }
}