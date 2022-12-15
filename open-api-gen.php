<?php

include './vendor/autoload.php';

use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;


$version = !empty($argv[1]) ? $argv[1] : 'v1';

$rest = include(__DIR__ . '/application/config/rest/' . $version . '.php');

$apiConfig = isset($rest[$version]) ? $rest[$version] : [];

$info = Info::create()
    ->title(
        !empty($apiConfig['title']) ? $apiConfig['title'] : 'Title'
    )
    ->version($version)
    ->description(
        !empty($apiConfig['description']) ? $apiConfig['description'] : 'description'
    );

$openApi = OpenApi::create()
    ->openapi(OpenApi::OPENAPI_3_0_2)
    ->info($info);

$tags = [];
$schemas = [];
$paths = [];

foreach ($rest as $path => $config) {
    $pathParts = explode('/', $path);
    $pathPartsCount = count($pathParts);

    $version = null;
    $entity = null;
    $id = null;
    if ($pathPartsCount == 3) {
        [$version, $entity, $id] = $pathParts;
    } elseif ($pathPartsCount == 2) {
        [$version, $entity] = $pathParts;
    } elseif ($pathPartsCount < 2) {
        continue;
    }

    $entities = !empty($rest[$version]['entity']) ? $rest[$version]['entity'] : [];
    $entityConfig = !empty($entities[$entity]) ? $entities[$entity] : [];

    if (!isset($tags[$entity])) {
        $tags[$entity] = Tag::create($entity)
            ->name(
                !empty($entityConfig['name']) ? $entityConfig['name'] : ucfirst($entity)
            )
            ->description(
                !empty($entityConfig['description']) ? $entityConfig['description'] : ''
            );
        $openApi = $openApi->tags(...$tags);
    }

    foreach ($config as $method => $methodConfig) {

        $oaMethod = strtolower($method);
        $oaOpId = !empty($id)
            ? $oaMethod . '.' . $entity . '.id'
            : $oaMethod . '.' . $entity;

        $oaOperation = Operation::get()
            ->tags($tags[$entity])
            ->summary(
                !empty($methodConfig['description']) ? $methodConfig['description'] : ''
            )->operationId($oaOpId);

        $responsesConfig = !empty($methodConfig['responses']) ? $methodConfig['responses'] : [];
        $responses = [];
        foreach ($responsesConfig as $responseId => $responseConfig) {
            $responses[] = Response::create()
                ->statusCode(
                    !empty($responseConfig['code']) ? $responseConfig['code'] : 200
                )
                ->description(
                    !empty($responseConfig['description']) ? $responseConfig['description'] : ''
                );
            $oaOperation = $oaOperation->responses(...$responses);
        }

        $oaPathString = '/rest/' . implode('/', [$version, $entity]);
        if (!empty($id)) {
            $oaPathString = $oaPathString . '/{id}';
        }

        $oaPath = PathItem::create()
            ->route($oaPathString)
            ->operations($oaOperation);

        $paths[] = $oaPath;

        $openApi = $openApi->paths(...$paths);
    }
}

header('Content-Type: application/json');
echo $openApi->toJson();
