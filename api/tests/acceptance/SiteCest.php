<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\AcceptanceTester;
use Codeception\Util\HttpCode;

final class SiteCest
{
    public function getHome(AcceptanceTester $I): void
    {
        $I->sendGET('/');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'success',
                'error_message' => '',
                'error_code' => null,
                'data' => [
                    'version' => '3.0',
                    'author' => 'yiisoft',
                ],
            ]
        );
    }

    public function testNotFoundPage(AcceptanceTester $I): void
    {
        $I->sendGET('/not_found_page');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'failed',
                'error_message' => 'Page not found',
                'error_code' => 404,
                'data' => null,
            ]
        );
    }
}
