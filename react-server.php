<?php

require __DIR__ . '/vendor/autoload.php';

function show_usage()
{
    echo "Usage: php react-server.php [port]\n";
}

function parse_arguments($argv)
{
    if (count($argv) > 2) {
        show_usage();
        exit(-1);
    }

    $args = [
        'port' => 8080
    ];

    if (isset($argv[1])) {
        if (!filter_var($argv[1], FILTER_VALIDATE_INT)) {
            show_usage();
            exit(-1);
        }
        $args['port'] = $argv[1];
    }

    return $args;
}

/**
 * @return React\Http\Server
 */
function initialize_server(): React\Http\Server
{
    $jail = new Monyxie\Mdir\Filesystem\Jail(__DIR__ . '/public');
    $servesStaticContent = function (Psr\Http\Message\ServerRequestInterface $request, callable $next) use ($jail) {
        $urlPath = parse_url($request->getUri(), PHP_URL_PATH);
        if ($urlPath && $urlPath !== '/') {
            if ($path = $jail->resolveRelative($urlPath)) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if ($extension != 'php') {
                    return new React\Http\Response(
                        200,
                        ['Content-Type' => RingCentral\Psr7\mimetype_from_extension($extension)],
                        file_get_contents($path)
                    );
                }
            }
        }

        return $next($request);
    };

    $app = new Monyxie\Mdir\Application();
    $servesDynamicContent = function (Psr\Http\Message\ServerRequestInterface $request) use ($app) {
        $symfonyResponse = $app->handle(Symfony\Component\HttpFoundation\Request::create(
            $request->getUri(),
            $request->getMethod(),
            $request->getQueryParams(),
            $request->getCookieParams(),
            $request->getUploadedFiles(),
            $request->getServerParams(),
            $request->getBody()
        ));
        return new React\Http\Response(
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all(),
            $symfonyResponse->getContent()
        );
    };

    return new React\Http\Server([$servesStaticContent, $servesDynamicContent]);
}

$server = initialize_server();
$loop = React\EventLoop\Factory::create();
$args = parse_arguments($argv);
initialize_server()->listen(new React\Socket\Server($args['port'], $loop));
echo "Server listening on port {$args['port']}...\n";
$loop->run();