<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\AcceptanceTester;
use Codeception\Util\HttpCode;
use Yiisoft\Json\Json;

final class AuthCest
{
    public function auth(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/auth/',
            [
                'login' => 'Opal1144',
                'password' => 'Opal1144',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'success',
                'error_message' => '',
                'error_code' => null,
            ]
        );

        $response = Json::decode($I->grabResponse());
        $I->seeInDatabase(
            'user',
            [
                'id' => 1,
                'token' => $response['data']['token'],
            ]
        );
    }

    public function logout(AcceptanceTester $I): void
    {
        $I->haveHttpHeader(
            'X-Api-Key',
            'lev1ZsWCzqrMlXRI2sT8h4ApYpSgBMl1xf6D4bCRtiKtDqw6JN36yLznargilQ_rEJz9zTfcUxm53PLODCToF9gGin38Rd4NkhQPOVeH5VvZvBaQlUg64E6icNCubiAv'
        );

        $I->sendPOST(
            '/logout/'
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'success',
                'error_message' => '',
                'error_code' => null,
            ]
        );

        $I->dontSeeInDatabase(
            'user',
            [
                'id' => 1,
                'token' => 'lev1ZsWCzqrMlXRI2sT8h4ApYpSgBMl1xf6D4bCRtiKtDqw6JN36yLznargilQ_rEJz9zTfcUxm53PLODCToF9gGin38Rd4NkhQPOVeH5VvZvBaQlUg64E6icNCubiAv',
            ]
        );
    }

    public function logoutWithBadToken(AcceptanceTester $I): void
    {
        $I->haveHttpHeader(
            'X-Api-Key',
            'bad-token'
        );

        $I->haveHttpHeader(
            'Accept',
            'application/json'
        );

        $I->sendPOST(
            '/logout/'
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'failed',
                'error_message' => 'Unauthorised request',
                'error_code' => HttpCode::UNAUTHORIZED,
                'data' => null,
            ]
        );
    }
}
