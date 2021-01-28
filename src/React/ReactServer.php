<?php


namespace Monyxie\Mdir\React;


use Monyxie\Mdir\Application;
use Monyxie\Mdir\Filesystem\Jail;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Http\Response as ReactResponse;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

use function RingCentral\Psr7\mimetype_from_extension;

class ReactServer
{

    private $jail;
    private $app;

    private function showUsage()
    {
        echo "Usage: php react-server.php [address:port]\n";
    }

    private function parseArguments($argv)
    {
        if (count($argv) > 2) {
            $this->showUsage();
            exit(-1);
        }

        $args = [
            'listen' => '127.0.0.1:8080'
        ];

        if (isset($argv[1])) {
            if (!preg_match('/^\d{1,3}(\.\d{1,3}){3}:\d{1,5}$/', $argv[1])) {
                $this->showUsage();
                exit(-1);
            }

            $args['listen'] = $argv[1];
        }

        return $args;
    }

    /**
     * @return HttpServer
     */
    private function initializeServer(): HttpServer
    {
        return new HttpServer(
            function (ServerRequestInterface $request) {
                return $this->createStaticResponse($request) ?? $this->createDynamicResponse($request);
            }
        );
    }

    public function run(array $argv)
    {
        $this->jail = new Jail(__DIR__ . '/../../public');
        $this->app = new Application();

        $loop = LoopFactory::create();
        $args = $this->parseArguments($argv);
        $this->initializeServer()->listen(new SocketServer($args['listen'], $loop));
        echo "Server listening on port {$args['listen']}...\n";
        $loop->run();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ReactResponse
     */
    private function createDynamicResponse(ServerRequestInterface $request): ReactResponse
    {
        $symfonyResponse = $this->app->handle(
            SymfonyRequest::create(
                $request->getUri(),
                $request->getMethod(),
                $request->getQueryParams(),
                $request->getCookieParams(),
                $request->getUploadedFiles(),
                $request->getServerParams(),
                $request->getBody()
            )
        );
        return new ReactResponse(
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all(),
            $symfonyResponse->getContent()
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ReactResponse
     */
    private function createStaticResponse(ServerRequestInterface $request): ?ReactResponse
    {
        $urlPath = parse_url($request->getUri(), PHP_URL_PATH);
        if ($urlPath && $urlPath !== '/') {
            if ($path = $this->jail->resolveRelative($urlPath)) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if ($extension != 'php') {
                    return $this->serveFile($request, $extension, $path);
                }
            }
        }

        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $extension
     * @param $path
     * @return ReactResponse
     */
    private function serveFile(ServerRequestInterface $request, string $extension, $path): ReactResponse
    {
        $modified = filemtime($path);

        if ($ifModifiedSince = $request->getHeader('If-Modified-Since')) {
            $ts = strtotime($ifModifiedSince[0]);
            if ($ts == $modified) {
                return new ReactResponse(304);
            }
        }

        return new ReactResponse(
            200,
            [
                'Cache-Control' => 'max-age=2592000',
                'Last-Modified' => gmdate('D, d M Y H:i:s T', $modified),
                'Content-Type' => mimetype_from_extension($extension)
            ],
            file_get_contents($path)
        );
    }
}