<?php

include './vendor/autoload.php';

use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response\Schema as ResponseSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;


$versionNum = !empty($argv[1]) ? ltrim($argv[1], 'v') : '1';
$version = 'v' . $versionNum;

$rest = include(__DIR__ . '/application/config/rest/' . $version . '.php');
$outputFile = __DIR__ . '/docs/open-api/' . $version . '.json';

$apiConfig = isset($rest[$version]) ? $rest[$version] : [];

///////////////////////////////////////////////////////////////////////////
// Main API Details
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

    ///////////////////////////////////////////////////////////////////////////
    // Path
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
        ///////////////////////////////////////////////////////////////////////////
        // Method
        $oaMethod = strtolower($method);
        $oaOpId = !empty($id)
            ? $oaMethod . '.' . $entity . '.id'
            : $oaMethod . '.' . $entity;

        $oaOperation = Operation::$oaMethod()->summary(
                        !empty($methodConfig['description']) ? $methodConfig['description'] : ''
                    )->operationId($oaOpId);

        ///////////////////////////////////////////////////////////////////////////
        // Tag
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
        if (isset($tags[$tagId])) {
            $oaOperation = $oaOperation->tags($tags[$tagId]);
        }


        ///////////////////////////////////////////////////////////////////////////
        // Params
        $params = [];

        // Entity id param
        if ($id) {
            $params[] = Parameter::path()->name('id');
        }

        // Query params
        // TODO: allow proper param type definition via config
        $paramsConfig = !empty($methodConfig['params']) ? $methodConfig['params'] : [];
        foreach ($paramsConfig as $paramName => $paramConfig) {
            $params[] = Parameter::query()->name($paramName);
        }
        $oaOperation = $oaOperation->parameters(...$params);

        ///////////////////////////////////////////////////////////////////////////
        // Request Body
        // TODO: allow proper schema definition via config
        $bodyParamsConfig = !empty($methodConfig['bodyParams']) ? $methodConfig['bodyParams'] : [];
        if (!empty($bodyParamsConfig)) {
            $props = [];

            $schemaBody = Schema::object();
            foreach ($bodyParamsConfig as $propName => $propConfig) {
                $props[] = Schema::string($propName);
            }
            $schemaBody = $schemaBody->properties(...$props);
            if (!empty($params)) {
                $oaOperation = $oaOperation->requestBody(RequestBody::create()->content(
                    MediaType::json()->schema($schemaBody)
                ));
            }
        }

        //////////////////////////////////////////////////////////////////////////
        // Responses
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
        }
        if (!empty($responses)) {
            $oaOperation = $oaOperation->responses(...$responses);
        }

        $operations[] = $oaOperation;
    }

    /////////////////////////////////////////////////////////////////////////
    // Path
    $oaPathString = '/rest/' . implode('/', [$version, $entity]);
    if (!empty($id)) {
        $oaPathString = $oaPathString . '/{id}';
    }
    $oaPath = PathItem::create()
        ->route($oaPathString);

    if (!empty($operations)) {
        $oaPath = $oaPath->operations(...$operations);
    }

    $paths[] = $oaPath;
}

$openApi = $openApi->paths(...$paths);

file_put_contents($outputFile, $openApi->toJson());

echo 'Created ' . $outputFile;
