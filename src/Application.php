<?php

namespace Monyxie\Mdir;

use Monyxie\Mdir\Config\ConfigurationLoader;
use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;

setlocale(LC_ALL, 'C.UTF-8');

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
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $this->config = $this->loadConfiguration();

        if ($this->config['debug']) {
            Debug::enable();
        } else {
            ErrorHandler::register();
            ini_set('display_errors', 'off');
        }

        $this->container = new Container($this->loadServices());
        $this->container['config'] = $this->config;

        if (!is_dir($this->config['markdown_dir'])) {
            die('Change "markdown_dir" to point to your markdown directory in app.php.');
        }

        $this->router = $this->container['router'];
        $this->template = $this->container['template'];
    }

    public function handle(Request $request): Response
    {
        return $this->handleRequest($request)->prepare($request);
    }

    /**
     * @return Response
     */
    private function show404(): Response
    {
        return new Response($this->template->render('404.php'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function handleRequest(Request $request): Response
    {
        try {
            $parameters = $this->router->matchRequest($request);
        } catch (ResourceNotFoundException $e) {
            return $this->show404();
        }

        list($controller, $method) = $parameters['_controller'];
        if (!isset($this->container[$controller])) {
            return $this->show404();
        }

        $instance = $this->container[$controller];

        if (!method_exists($instance, $method)) {
            return $this->show404();
        }

        try {
            return $instance->{$method}($request, $parameters);
        } catch (ResourceNotFoundException $e) {
            return $this->show404();
        }
    }

    public function loadConfiguration()
    {
        $configDirectories = [__DIR__ . '/../config'];
        $fileLocator = new FileLocator($configDirectories);
        $filepath = $fileLocator->locate('app.php', null, true);
        $loader = new ConfigurationLoader($fileLocator);
        return $loader->load($filepath);
    }

    /**
     * @return mixed
     */
    private function loadServices()
    {
        return require __DIR__ . '/services.php';
    }
}