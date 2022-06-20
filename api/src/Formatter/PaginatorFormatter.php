<?php

declare(strict_types=1);

namespace App\Formatter;

use Yiisoft\Data\Paginator\OffsetPaginator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="Paginator",
 *      @OA\Property(example="10", property="pageSize", format="int"),
 *      @OA\Property(example="1", property="currentPage", format="int"),
 *      @OA\Property(example="3", property="totalPages", format="int"),
 * )
 */
final class PaginatorFormatter
{
    public function format(OffsetPaginator $paginator): array
    {
        return [
            'pageSize' => $paginator->getPageSize(),
            'currentPage' => $paginator->getCurrentPage(),
            'totalPages' => $paginator->getTotalPages(),
        ];
    }
}
