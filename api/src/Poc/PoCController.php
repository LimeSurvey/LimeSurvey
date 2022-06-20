<?php

declare(strict_types=1);

namespace App\PoC;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface as ResponseFactory;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="LimeSurvey API POC", version="1.0")
 */
class PoCController
{
    private ResponseFactory $responseFactory;

    function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OA\Get(
     *     path="/validate",
     *     summary="Returns info about a Token",
     *     description="",
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Response"),
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="data",
     *                          type="object",
     *                          @OA\Property(
     *                              property="user_id",
     *                              type="integer",
     *                              example="1234"
     *                          ),
     *                          @OA\Property(
     *                              property="user_name",
     *                              type="string",
     *                              example="admin"
     *                          ),
     *                          @OA\Property(
     *                              property="user_fullname",
     *                              type="string",
     *                              example="Administrator"
     *                          ),
     *                          @OA\Property(
     *                              property="admin_lang",
     *                              type="string",
     *                              example="en"
     *                          ),
     *                      ),
     *                  ),
     *              },
     *          )
     *    ),
     * )
     */
    public function validateToken(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(
                    [
                        'user_id' => $request->getAttribute("user")->getId(),
                        'user_name' => $request->getAttribute("user")->getUserName(),
                        'user_fullname' => $request->getAttribute("user")->getUserFullName(),
                        'admin_lang' => $request->getAttribute("user")->getAdminLang(),
                    ]
                 );
    }
}
