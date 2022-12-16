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


$versionNum = !empty($argv[1]) ? ltrim($argv[1], 'v') : '1';
$version = 'v' . $versionNum;

$rest = include(__DIR__ . '/application/config/rest/' . $version . '.php');
$outputFile = __DIR__ . '/docs/open-api/' . $version . '.json';

$apiConfig = isset($rest[$version]) ? $rest[$version] : [];

$info = Info::create()
    ->title(
        !empty($apiConfig['title']) ? $apiConfig['title'] : 'Title'
    )
    ->version($versionNum)
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

    $tagsConfig = !empty($rest[$version]['tags']) ? $rest[$version]['tags'] : [];

    $operations = [];
    foreach ($config as $method => $methodConfig) {
        $oaMethod = strtolower($method);
        $oaOpId = !empty($id)
            ? $oaMethod . '.' . $entity . '.id'
            : $oaMethod . '.' . $entity;


        $tagId = !empty($methodConfig['tag']) ? $methodConfig['tag'] : $entity;
        $tagConfig = isset($tagsConfig[$tagId])? $tagsConfig[$tagId] : null;
        if ($tagConfig) {
            $tags[$tagId] = Tag::create($tagId)
                ->name(
                    !empty($tagConfig['name']) ? $tagConfig['name'] : ucfirst($entity)
                )
                ->description(
                    !empty($tagConfig['description']) ? $tagConfig['description'] : ''
                );
            $openApi = $openApi->tags(...$tags);
        }

        $oaOperation = Operation::$oaMethod();

        if (isset($tags[$tagId])) {
            $oaOperation = $oaOperation->tags($tags[$tagId]);
        }

        $oaOperation = $oaOperation->summary(
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

        $operations[] = $oaOperation;
    }

    $oaPath = PathItem::create()
        ->route($oaPathString)
        ->operations(...$operations);

    $paths[] = $oaPath;
}

$openApi = $openApi->paths(...$paths);

file_put_contents($outputFile, $openApi->toJson());

echo 'Created ' . $outputFile;
