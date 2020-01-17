<?php

ini_set('display_errors', 'stderr');
require __DIR__ . '/vendor/autoload.php';

$relay = new Spiral\Goridge\StreamRelay(STDIN, STDOUT);
$psr7 = new Spiral\RoadRunner\PSR7Client(new Spiral\RoadRunner\Worker($relay));

$app = new Monyxie\Mdir\Application();
while ($request = $psr7->acceptRequest()) {
    try {
        $symfonyResponse = $app->handle(Symfony\Component\HttpFoundation\Request::create(
            $request->getUri(),
            $request->getMethod(),
            $request->getQueryParams(),
            $request->getCookieParams(),
            $request->getUploadedFiles(),
            $request->getServerParams(),
            $request->getBody()
        ));

        $resp = new Zend\Diactoros\Response('php://memory', $symfonyResponse->getStatusCode(), $symfonyResponse->headers->all());
        $resp->getBody()->write($symfonyResponse->getContent());

        $psr7->respond($resp);
    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
}
