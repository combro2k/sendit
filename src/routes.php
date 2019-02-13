<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response) {
  return $this->renderer->render($response, 'index.phtml');
});

$app->post('/', function (Request $request, Response $response, array $args) {
  $files = $request->getUploadedFiles();

  var_dump($files);

  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/{uuid}', function (Request $request, Response $response, array $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");

  if (array_key_exists('uuid', $args)) {
    print_r(getcwd());
  }

  // Render index view
  return $this->renderer->render($response, 'download.phtml', $args);
});
