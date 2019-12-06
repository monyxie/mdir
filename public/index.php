<?php
require __DIR__ . '/../vendor/autoload.php';

$app = (new \Monyxie\Mdir\Application());
$app->handle(Symfony\Component\HttpFoundation\Request::createFromGlobals())->send();