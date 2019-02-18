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
    $uniqString = json_encode([
      'timestamp' => $timestamp,
      'ipAddress' => $ipAddress,
      'filename' => $file->getClientFilename(),
      'description' => $request->getParam('description'),
    ]);

    var_export($uniqString);

    $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $uniqString);

    if (!empty($uniqString['filename'])) {
      $file->moveTo("{$storePath}/${uuid}.upload");
    }
    else {
      echo file_put_contents("{$storePath}/${uuid}.text", $uniqString);
    }
  } 

  return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/{uuid}', function (Request $request, Response $response, array $args) use ($storePath, $app) {
  $uuid = $args['uuid'];

  $uploadTarget = "{$storePath}/{$uuid}.upload";
  $textTarget = "{$storePath}/{$uuid}.text";

  if (!file_exists($uploadTarget) && !file_exists($textTarget)) {
    return $response->withRedirect('/', 302);
  } 

  // Render index view
  return $this->renderer->render($response, 'download.phtml', [
    'uuid' => $uuid,
  ]);
});
