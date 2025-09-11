<?php

include_once './vendor/autoload.php';

use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Example;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response\Schema as ResponseSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;

$versionNum = !empty($argv[1]) ? ltrim($argv[1], 'v') : '1';
$version = 'v' . $versionNum;

$rest = include_once(__DIR__ . '/application/config/rest/' . $version . '.php');
$outputFile = __DIR__ . '/docs/open-api/' . $version . '.json';

$apiConfig = isset($rest[$version]) ? $rest[$version] : [];

///////////////////////////////////////////////////////////////////////////
// Main API Details
$info = Info::create()
    ->title(
        !empty($apiConfig['title'])
            ? $apiConfig['title']
            : 'Title'
    )
    ->version($versionNum)
    ->description(
        !empty($apiConfig['description'])
            ? $apiConfig['description']
            : 'description'
    );

$securityScheme = SecurityScheme::create('bearerAuth')
    ->scheme('bearer')
    ->type('http')
    ->name('Bearer Auth');
$securityRequirement = SecurityRequirement::create()
    ->securityScheme($securityScheme);

$components = Components::create()
    ->securitySchemes(
        $securityScheme
    );

$openApi = OpenApi::create()
    ->openapi(OpenApi::OPENAPI_3_0_2)
    ->info($info)
    ->components($components);

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
    } elseif ($pathPartsCount > 3) {
        // handle complex paths
        [$version, $entity] = $pathParts;
        $subPathParts = array_slice($pathParts, 2);
        $subPath = '';
        $complexPathParams = [];

        foreach ($subPathParts as $subPathPart) {
            $subPath.= '/'. $subPathPart;
            if (str_starts_with($subPathPart, '$')) {
                $paramName = ltrim($subPathPart, '$');
                $subPath = str_replace($subPathPart, '{_' . $paramName . '}', $subPath);
                array_push($complexPathParams, $paramName);
            }
        }
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

        if (!empty($methodConfig['auth'])) {
            $oaOperation = $oaOperation->security($securityRequirement);
        }

        ///////////////////////////////////////////////////////////////////////////
        // Tag
        $tagId = !empty($methodConfig['tag']) ? $methodConfig['tag'] : $entity;
        $tagConfig = isset($tagsConfig[$tagId]) ? $tagsConfig[$tagId] : null;
        if ($tagConfig) {
            $tags[$tagId] = Tag::create($tagId)
                ->name(
                    !empty($tagConfig['name']) ? $tagConfig['name'] : ucfirst(
                        $entity
                    )
                )
                ->description(
                    !empty($tagConfig['description']) ? $tagConfig['description'] : ''
                );
            $openApi = $openApi->tags(...array_values($tags));
        }
        if (isset($tags[$tagId])) {
            $oaOperation = $oaOperation->tags($tags[$tagId]);
        }

        ///////////////////////////////////////////////////////////////////////////
        // Params
        $params = [];

        if($pathPartsCount > 3) {
             if($complexPathParams && count($complexPathParams) > 0) {
                foreach ($complexPathParams as $paramName) {
                    $params[] = Parameter::path()->name('_' . $paramName);
                }
            }
            
        } else {
           // Entity id param
            if ($id) {
                $params[] = Parameter::path()->name('_id');
            }
        } 

        // Query params
        $paramsConfig = !empty($methodConfig['params']) ? $methodConfig['params'] : [];
        $formProps = [];
        foreach ($paramsConfig as $paramName => $paramConfig) {
            if ($paramConfig) {
                $src = is_array(
                    $paramConfig
                ) && !empty($paramConfig['src']) ? $paramConfig['src'] : 'query';

                $paramSchema = null;

                if (!empty($paramConfig['schema']) && $paramConfig['schema'] instanceof Schema) {
                    $paramSchema = $paramConfig['schema'];
                } else {
                    $type = !empty($paramConfig['type']) ? $paramConfig['type'] : '';
                    switch ($type) {
                        case 'int':
                            $paramSchema = Schema::integer($paramName);
                            break;
                        case 'number':
                            $paramSchema = Schema::number($paramName);
                            break;
                        case 'string':
                        default:
                            $paramSchema = Schema::string($paramName);
                            break;
                    }
                }

                if ($src == 'query') {
                    $params[] = Parameter::query()
                        ->name($paramName)->schema(
                            $paramSchema
                        );
                } elseif ($src == 'form') {
                    $formProps[] = $paramSchema;
                }
            }
        }
        $oaOperation = $oaOperation->parameters(...$params);
        $formSchema = null;
        if (!empty($formProps)) {
            $formSchema = Schema::object()->properties(...$formProps);
        }

        ///////////////////////////////////////////////////////////////////////////
        // Request Content
        $schema = !empty($methodConfig['schema']) ? $methodConfig['schema'] : null;
        $example = !empty($methodConfig['example']) ? $methodConfig['example'] : null;
        $mediaType = null;
        $requestBody = null;

        if (!empty($methodConfig['multipart']) && $methodConfig['multipart'] === true) {
            $multipartSchema = Schema::object()
                ->properties(
                    Schema::string('file')->format('binary')
                );
            $mediaType = createMultipartFormDataMediaType($multipartSchema);
        } elseif (!empty($schema) && $schema instanceof Schema) {
            $mediaType = MediaType::json();
        } elseif ($formSchema) {
            $mediaType = MediaType::formUrlEncoded();
            $schema = $formSchema;
        }

        if ($mediaType !== null) {
            if (!empty($schema) && $schema instanceof Schema) {
                $mediaType = $mediaType->schema($schema);
            }
            if ($example !== null) {
                if (is_string($example) && file_exists($example)) {
                    $example = (new Example)
                        ->create('Example')
                        ->value(
                            json_decode(
                                file_get_contents($example)
                            )
                        );
                }
                if ($example instanceof Example) {
                    $mediaType = $mediaType->examples($example);
                }
            }
            $requestBody = RequestBody::create()->content(
                $mediaType
            );
        }

        if ($requestBody instanceof RequestBody) {
            $oaOperation = $oaOperation->requestBody(
                $requestBody
            );
        }
        //////////////////////////////////////////////////////////////////////////
        // Responses
        $responsesConfig = !empty($methodConfig['responses']) ? $methodConfig['responses'] : [];
        $responses = [];
        foreach ($responsesConfig as $responseId => $responseConfig) {
            $response = Response::create()
                ->statusCode(
                    !empty($responseConfig['code']) ? $responseConfig['code'] : 200
                )
                ->description(
                    !empty($responseConfig['description']) ? $responseConfig['description'] : ''
                );

            $schema = !empty($responseConfig['schema']) ? $responseConfig['schema'] : null;
            $examples = !empty($responseConfig['examples']) ? $responseConfig['examples'] : null;
            $mediaType = MediaType::json();
            if (!empty($schema) && $schema instanceof Schema) {
                $mediaType = MediaType::json()->schema($schema);
                if (is_array($examples)) {
                    $examples = array_filter($examples, function ($example) {
                        return $example instanceof Example;
                    });
                    $mediaType = $mediaType->examples(...$examples);
                }
                $response = $response->content(
                    $mediaType
                );
            }

            $responses[] = $response;
        }
        if (!empty($responses)) {
            $oaOperation = $oaOperation->responses(...$responses);
        }

        $operations[] = $oaOperation;
    }

    /////////////////////////////////////////////////////////////////////////
    // Path
    $oaPathString = '/rest/' . implode('/', [$version, $entity]);
    if($pathPartsCount > 3) {
        $oaPathString = $oaPathString . $subPath;
        
    } else {
        if (!empty($id)) {
            $oaPathString = $oaPathString . '/{_id}';
        }

    }
    
    $oaPath = PathItem::create()
        ->route($oaPathString);

    if (!empty($operations)) {
        $oaPath = $oaPath->operations(...$operations);
    }
       var_dump($oaPathString); 
    $paths[] = $oaPath;
}

$openApi = $openApi->paths(...$paths);

file_put_contents(
    $outputFile,
    $openApi->toJson(JSON_PRETTY_PRINT)
);

function createMultipartFormDataMediaType($schema = null)
{
    $mediaType = MediaType::create()
        ->mediaType('multipart/form-data');

    if ($schema instanceof Schema) {
        $mediaType = $mediaType->schema($schema);
    }

    return $mediaType;
}

echo "\n" . 'Created ' . $outputFile . "\n";
