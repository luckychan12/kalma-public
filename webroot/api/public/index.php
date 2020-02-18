<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Kalma\Api\Core\FrontController;

$psr17Factory = new Psr17Factory();

$requestCreator =
    (
    new ServerRequestCreator
    (
        $psr17Factory, // ServerRequestFactory
        $psr17Factory, // UriFactory
        $psr17Factory, // UploadedFileFactory
        $psr17Factory  // StreamFactory
    )
    );

$request = $requestCreator->fromGlobals();

$frontController = new FrontController();
$frontController->dispatchRequest($request);
