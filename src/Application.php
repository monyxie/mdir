<?php

namespace Monyxie\Mdir;

use Pimple\Container;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;

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
        ErrorHandler::register();

        $services = require __DIR__ . '/services.php';
        $this->container = new Container($services);

        $this->config = $this->container['config'];

        if ($this->config['debug']) {
            Debug::enable();
        } else {
            ini_set('display_errors', 'off');
        }

        if (!is_dir($this->config['markdown_dir'])) {
            die('Change "markdown_dir" to point to your markdown directory in app.php.');
        }

        $this->router = $this->container['router'];
        $this->template = $this->container['template'];
    }

    public function handle(Request $request): Response
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

        return $instance->{$method}($request, $parameters);
    }

    /**
     * @return Response
     */
    private function show404(): Response
    {
        return new Response($this->template->render('404.php'));
    }

}