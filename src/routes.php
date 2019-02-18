<?php

use Slim\Http\Request;
use Slim\Http\Response;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

// Routes

$storePath = '/data/store';

$app->get('/', function (Request $request, Response $response) {
  return $this->renderer->render($response, 'index.phtml');
});

$app->post('/', function (Request $request, Response $response, array $args) use ($storePath) {
  $files = $request->getUploadedFiles();

  $ipAddress = $request->getAttribute('ip_address');
  $timestamp = time();

  foreach ($files as $file) {
    $uniqString = [
      'timestamp' => $timestamp,
      'ipAddress' => $ipAddress,
      'filename' => $file->getClientFilename(),
      'description' => $request->getParam('description'),
    ];

    $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, json_encode($uniqString));

    if (!empty($uniqString['filename'])) {
      $file->moveTo("{$storePath}/${uuid}.data");
    }
    
    file_put_contents("{$storePath}/${uuid}.text", json_encode($uniqString));

    $args['uuid'][] = $uuid;
  } 

  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/{uuid}', function (Request $request, Response $response, array $args) use ($storePath, $app) {
  $uuid = $args['uuid'];

  $uploadTarget = "{$storePath}/{$uuid}.data";
  $textTarget = "{$storePath}/{$uuid}.text";

  if (!file_exists($uploadTarget) && !file_exists($textTarget)) {
    return $response->withRedirect('/', 302);
  } 

  if (file_exists($textTarget)) {
    $resp = $response->withHeader('Content-type', 'application/json');
    $body = $resp->getBody();
    $body->write(file_get_contents($textTarget));
  }

  if (file_exists($uploadTarget)) {
    $resp = $response->withHeader('Content-type', 'application/json');

    $body = $resp->getBody();
    $body->write(file_get_contents($uploadTarget));
  }

});
