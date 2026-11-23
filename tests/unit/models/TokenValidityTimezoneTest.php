<?php

namespace ls\tests;

use SettingsUser;
use Token;
use TokenDynamic;

/**
 * The "valid from" and "valid until" dates of a survey participant are stored in UTC, and entered and shown
 * in the display timezone of the administrator (#20859).
 */
class TokenValidityTimezoneTest extends TestBaseClass
{
    private const UID = 1;

    /** @var int Id of the test participant */
    private static $tokenId;

    /** @var string Access code of the test participant */
    private static $tokenString;

    /** @var string|null Original global displayTimezone config value */
    private $originalDisplayTimezone;

    /** @var string|null Original personal displayTimezone setting of the test user, null if not set */
    private $originalUserTimezone;

    /** @var mixed Original user id of the web user */
    private $originalUserId;

    /** @var mixed Original date format of the session */
    private $originalDateFormat;

    /**
     * Imports a survey with a participant table and adds a participant with validity dates.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/survey_archive_265831.lsa');
        \Yii::import('application.controllers.admin.Tokens', true);

        $token = Token::create(self::$surveyId);
        $token->firstname = 'Validity';
        $token->lastname = 'Timezone';
        $token->email = 'validity@example.com';
        $token->token = 'validitytimezone';
        // Stored in UTC: 2026-01-15 17:00 and 2026-01-16 02:00 are 12:00 and 21:00 on 2026-01-15 in Toronto (UTC-5)
        $token->validfrom = '2026-01-15 17:00:00';
        $token->validuntil = '2026-01-16 02:00:00';
        self::assertTrue($token->encryptSave(true), 'Could not save the test participant');
        self::$tokenId = (int) $token->tid;
        self::$tokenString = $token->token;
    }

    /**
     * Sets America/Toronto as display timezone of the test admin and remembers the original settings.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDisplayTimezone = \Yii::app()->getConfig('displayTimezone');
        $setting = SettingsUser::getUserSetting('displayTimezone', self::UID);
        $this->originalUserTimezone = $setting ? $setting->stg_value : null;
        $this->originalUserId = \Yii::app()->user->getId();
        $this->originalDateFormat = \Yii::app()->session['dateformat'];

        \Yii::app()->user->setId(self::UID);
        SettingsUser::setUserSetting('displayTimezone', 'America/Toronto', self::UID);
        // Resolved display timezone of the request, as set by LSYii_Controller
        \Yii::app()->setConfig('displayTimezone', 'America/Toronto');
    }

    /**
     * Restores the original settings.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->setConfig('displayTimezone', $this->originalDisplayTimezone);
        if ($this->originalUserTimezone === null) {
            SettingsUser::deleteUserSetting('displayTimezone', self::UID);
        } else {
            SettingsUser::setUserSetting('displayTimezone', $this->originalUserTimezone, self::UID);
        }
        \Yii::app()->user->setId($this->originalUserId);
        \Yii::app()->session['dateformat'] = $this->originalDateFormat;
        parent::tearDown();
    }

    /**
     * @return array Date format id, posted value, expected stored UTC value
     */
    public static function postedValueProvider(): array
    {
        return [
            'dd.mm.yyyy' => [1, '15.01.2026 12:00', '2026-01-15 17:00:00'],
            'dd/mm/yyyy' => [5, '03/01/2026 12:00', '2026-01-03 17:00:00'],
            'mm/dd/yyyy' => [11, '01/15/2026 12:00', '2026-01-15 17:00:00'],
            'yyyy-mm-dd' => [6, '2026-01-15 12:00', '2026-01-15 17:00:00'],
            'next day in UTC' => [6, '2026-01-15 21:00', '2026-01-16 02:00:00'],
            'summer time' => [6, '2026-07-15 12:00', '2026-07-15 16:00:00'],
            'surrounding spaces' => [1, ' 15.01.2026 12:00 ', '2026-01-15 17:00:00'],
            'dd/mm/yyyy without time' => [5, '03/01/2026', '2026-01-03 05:00:00'],
            'invalid date without time' => [5, '31/02/2026', '31/02/2026'],
            'empty' => [1, '', null],
            'null' => [1, null, null],
            'not a date' => [1, 'foo', 'foo'],
        ];
    }

    /**
     * A validity date entered by the admin is converted from their date format and timezone to UTC.
     * @dataProvider postedValueProvider
     * @param int $dateFormat Date format id, see getDateFormatData()
     * @param mixed $value Posted value
     * @param string|null $expected Expected stored value
     * @return void
     */
    public function testConvertValidityDateToDbFormat(int $dateFormat, $value, ?string $expected)
    {
        \Yii::app()->session['dateformat'] = $dateFormat;
        $controller = new \Tokens(new DummyController('dummyid'));
        $method = new \ReflectionMethod(\Tokens::class, 'convertValidityDateToDbFormat');
        $this->assertSame($expected, $method->invoke($controller, $value));
    }

    /**
     * The participant list shows the validity dates in the display timezone.
     * @return void
     */
    public function testParticipantListShowsDisplayTimezone()
    {
        \Yii::app()->session['dateformat'] = 6;
        $token = TokenDynamic::model(self::$surveyId)->findByPk(self::$tokenId);
        $this->assertStringContainsString('2026-01-15', $token->getValidfromFormated());
        // Stored as the 16th in UTC, but still the 15th in Toronto
        $this->assertStringContainsString('2026-01-15', $token->getValiduntilFormated());
        $this->assertStringNotContainsString('2026-01-16', $token->getValiduntilFormated());
    }

    /**
     * The "valid from"/"valid until" filters of the participant list are entered in the display timezone.
     * @return void
     */
    public function testParticipantListFiltersUseDisplayTimezone()
    {
        \Yii::app()->session['dateformat'] = 6;
        $filteredIds = function (array $filter) {
            $model = new TokenDynamic(self::$surveyId);
            $model->unsetAttributes();
            $model->setAttributes($filter, false);
            return array_map('intval', array_column(
                array_map(function ($token) {
                    return $token->attributes;
                }, $model->search()->getData()),
                'tid'
            ));
        };
        // Valid from 12:00 Toronto time: matches a "valid from" filter of 12:00, not 11:59
        $this->assertContains(self::$tokenId, $filteredIds(['validfrom' => '2026-01-15 12:00']));
        $this->assertNotContains(self::$tokenId, $filteredIds(['validfrom' => '2026-01-15 11:59']));
        // Valid until 21:00 Toronto time: matches a "valid until" filter of 21:00, not 21:01
        $this->assertContains(self::$tokenId, $filteredIds(['validuntil' => '2026-01-15 21:00']));
        $this->assertNotContains(self::$tokenId, $filteredIds(['validuntil' => '2026-01-15 21:01']));
    }

    /**
     * The {VALIDFROM} and {VALIDUNTIL} email placeholders are in the display timezone of the sending admin.
     * @return void
     */
    public function testEmailPlaceholdersUseDisplayTimezone()
    {
        $mailer = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
        $mailer->setSurvey(self::$surveyId);
        $mailer->replaceTokenAttributes = true;
        $mailer->setToken(self::$tokenString);
        $replacements = $mailer->getTokenReplacements();
        $this->assertSame('2026-01-15 12:00:00', $replacements['VALIDFROM']);
        $this->assertSame('2026-01-15 21:00:00', $replacements['VALIDUNTIL']);
    }

    /**
     * The validity check of the participant compares the stored UTC dates with the current UTC time.
     * @return void
     */
    public function testStoredUtcDateIsUsedForValidityCheck()
    {
        $token = Token::create(self::$surveyId);
        $token->token = 'validitycheck';
        $token->validuntil = gmdate('Y-m-d H:i:s', time() + 3600);
        $this->assertTrue($token->encryptSave(true), 'Could not save the participant');
        $this->assertNotNull(Token::model(self::$surveyId)->usable()->findByPk($token->tid));

        $token->validuntil = gmdate('Y-m-d H:i:s', time() - 3600);
        $this->assertTrue($token->encryptSave(true), 'Could not save the participant');
        $this->assertNull(Token::model(self::$surveyId)->usable()->findByPk($token->tid));
    }
}
