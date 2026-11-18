<?php

namespace ls\tests;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\BadRequestException;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\LabelSetService;

/**
 * Tests for LabelSetService, which both question editors use to list, read and save label sets.
 */
class LabelSetServiceTest extends TestBaseClass
{
    private const NAME_PREFIX = 'LabelSetServiceTest ';

    /** @var \User[] Test users keyed by role */
    private static $users = [];

    /** @var int[] Ids of the label sets created by a test */
    private $createdLids = [];

    /**
     * Create the users without label set rights and with the create right.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->user->setId(1);
        $userPermissions = [
            'none' => [],
            'creator' => ['labelsets' => ['create' => 'on', 'read' => 'on']],
            'setUpdater' => [],
        ];
        foreach ($userPermissions as $key => $permissions) {
            self::$users[$key] = self::createUserWithPermissions(
                [
                    'users_name' => 'labelSetService' . $key,
                    'full_name' => 'Label set service ' . $key,
                    'email' => 'labelsetservice' . strtolower($key) . '@example.org',
                    'lang' => 'auto',
                    'password' => 'testpassword123',
                ],
                $permissions
            );
        }
    }

    /**
     * Delete the test users.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->user->setId(1);
        foreach (self::$users as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            \User::model()->deleteByPk($user->uid);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Delete the label sets of the test and log in as the superadmin again.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->user->setId(1);
        foreach ($this->createdLids as $lid) {
            \Yii::app()->db->createCommand()->delete(
                '{{label_l10ns}}',
                'label_id IN (SELECT id FROM {{labels}} WHERE lid = :lid)',
                [':lid' => $lid]
            );
            \Label::model()->deleteAllByAttributes(['lid' => $lid]);
            \Permission::model()->deleteAllByAttributes(['entity' => 'labelset', 'entity_id' => $lid]);
            \LabelSet::model()->deleteByPk($lid);
        }
        $this->createdLids = [];
        parent::tearDown();
    }

    /**
     * The service as the application wires it.
     *
     * @return LabelSetService
     */
    private function getService(): LabelSetService
    {
        return DI::getContainer()->get(LabelSetService::class);
    }

    /**
     * Create a label set through the service and remember it for the clean-up.
     *
     * @param string $name Name without the test prefix
     * @param string[] $languages
     * @param array|null $labels Defaults to two labels in English and German
     * @return \LabelSet
     */
    private function createLabelSet(string $name, array $languages = ['en', 'de'], ?array $labels = null): \LabelSet
    {
        $labelSet = $this->getService()->create(
            self::NAME_PREFIX . $name,
            $languages,
            $labels ?? [
                ['code' => 'A1', 'assessmentValue' => 1, 'l10ns' => ['en' => 'Yes', 'de' => 'Ja']],
                ['code' => 'A2', 'assessmentValue' => 0, 'l10ns' => ['en' => 'No', 'de' => 'Nein']],
            ]
        );
        $this->createdLids[] = (int)$labelSet->lid;
        return $labelSet;
    }

    /**
     * The label sets of this test in a list returned by getLabelSets().
     *
     * @param array $labelSets
     * @return array<string, array> Keyed by name without the test prefix
     */
    private function onlyTestSets(array $labelSets): array
    {
        $result = [];
        foreach ($labelSets as $labelSet) {
            if (in_array($labelSet['lid'], $this->createdLids, true)) {
                $result[substr($labelSet['name'], strlen(self::NAME_PREFIX))] = $labelSet;
            }
        }
        return $result;
    }

    /**
     * A created label set comes back with its labels, translations and order.
     *
     * @return void
     */
    public function testCreateAndRead(): void
    {
        $labelSet = $this->createLabelSet('create');

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);

        $this->assertSame(['en', 'de'], $result['languages']);
        $this->assertSame(1, (int)$labelSet->owner_id);
        $labels = array_map(
            static function (array $label): array {
                ksort($label['l10ns']);
                return $label;
            },
            $result['labels']
        );
        $this->assertSame(
            [
                ['code' => 'A1', 'sortOrder' => 0, 'assessmentValue' => 1, 'l10ns' => ['de' => 'Ja', 'en' => 'Yes']],
                ['code' => 'A2', 'sortOrder' => 1, 'assessmentValue' => 0, 'l10ns' => ['de' => 'Nein', 'en' => 'No']],
            ],
            $labels
        );
    }

    /**
     * Language codes LimeSurvey doesn't ship, e.g. from an imported survey, are accepted.
     *
     * @return void
     */
    public function testCreateAcceptsWellFormedUnknownLanguage(): void
    {
        $labelSet = $this->createLabelSet(
            'unknown language',
            ['en-GB'],
            [['code' => 'A1', 'l10ns' => ['en-GB' => 'Colour']]]
        );

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);
        $this->assertSame(['en-GB'], $result['languages']);
        $this->assertSame('Colour', $result['labels'][0]['l10ns']['en-GB']);
    }

    /**
     * Invalid language lists.
     *
     * @return array
     */
    public function invalidLanguagesProvider(): array
    {
        return [
            'no language' => [[]],
            'not a string' => [[['en']]],
            'number' => [[12]],
            'empty' => [['']],
            'invalid characters' => [['en_GB']],
            'too long' => [[str_repeat('a', LabelSetService::MAX_LANGUAGE_LENGTH + 1)]],
        ];
    }

    /**
     * @dataProvider invalidLanguagesProvider
     * @param array $languages
     * @return void
     */
    public function testCreateRejectsInvalidLanguages(array $languages): void
    {
        $this->expectException(BadRequestException::class);
        $this->getService()->create(self::NAME_PREFIX . 'invalid', $languages, [['code' => 'A1']]);
    }

    /**
     * Invalid label lists.
     *
     * @return array
     */
    public function invalidLabelsProvider(): array
    {
        return [
            'no labels' => [[]],
            'label not an array' => [['A1']],
            'missing code' => [[['l10ns' => ['en' => 'Yes']]]],
            'code is an array' => [[['code' => ['A1']]]],
            'code with a dash' => [[['code' => 'A-1']]],
            'code too long' => [[['code' => str_repeat('A', LabelSetService::MAX_CODE_LENGTH + 1)]]],
            'code used twice, ignoring case' => [[['code' => 'A1'], ['code' => 'a1']]],
            'assessment value is text' => [[['code' => 'A1', 'assessmentValue' => 'abc']]],
            'assessment value is a fraction' => [[['code' => 'A1', 'assessmentValue' => 1.5]]],
            'assessment value too large' => [[['code' => 'A1', 'assessmentValue' => '2147483648']]],
            'assessment value far too large' => [[['code' => 'A1', 'assessmentValue' => '99999999999999999999']]],
            'translations not an array' => [[['code' => 'A1', 'l10ns' => 'Yes']]],
            'translation is an array' => [[['code' => 'A1', 'l10ns' => ['en' => ['Yes']]]]],
        ];
    }

    /**
     * @dataProvider invalidLabelsProvider
     * @param array $labels
     * @return void
     */
    public function testCreateRejectsInvalidLabels(array $labels): void
    {
        $this->expectException(BadRequestException::class);
        $this->getService()->create(self::NAME_PREFIX . 'invalid', ['en'], $labels);
    }

    /**
     * An invalid overwrite is rejected before anything is deleted (transactions don't help on MyISAM).
     *
     * @dataProvider invalidLabelsProvider
     * @param array $labels
     * @return void
     */
    public function testOverwriteWithInvalidLabelsKeepsTheOldLabels(array $labels): void
    {
        $labelSet = $this->createLabelSet('keep');

        try {
            $this->getService()->overwrite((int)$labelSet->lid, ['en'], $labels);
            $this->fail('The invalid labels were accepted');
        } catch (BadRequestException $exception) {
            // Expected
        }

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);
        $this->assertSame(['en', 'de'], $result['languages']);
        $this->assertSame(['A1', 'A2'], array_column($result['labels'], 'code'));
    }

    /**
     * Valid assessment values, including the empty value of the classic editor.
     *
     * @return void
     */
    public function testAssessmentValues(): void
    {
        $labelSet = $this->createLabelSet('assessment', ['en'], [
            ['code' => 'A1', 'assessmentValue' => ''],
            ['code' => 'A2', 'assessmentValue' => ' -5 '],
            ['code' => 'A3', 'assessmentValue' => 2147483647],
            ['code' => 'A4', 'assessmentValue' => 3.0],
        ]);

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);
        $this->assertSame([0, -5, 2147483647, 3], array_column($result['labels'], 'assessmentValue'));
    }

    /**
     * Overwriting replaces the labels and languages and removes all old translations.
     *
     * @return void
     */
    public function testOverwriteReplacesLabelsAndTranslations(): void
    {
        $labelSet = $this->createLabelSet('overwrite');
        $oldLabelIds = array_map(
            static fn (\Label $label): int => (int)$label->id,
            \Label::model()->findAllByAttributes(['lid' => $labelSet->lid])
        );

        $this->getService()->overwrite((int)$labelSet->lid, ['en'], [
            ['code' => 'B1', 'l10ns' => ['en' => 'Maybe', 'de' => 'Vielleicht']],
        ]);

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);
        $this->assertSame(['en'], $result['languages']);
        $this->assertSame(
            [['code' => 'B1', 'sortOrder' => 0, 'assessmentValue' => 0, 'l10ns' => ['en' => 'Maybe']]],
            $result['labels']
        );
        // The subquery delete must not leave translations of the old labels behind
        $this->assertSame(0, (int)\LabelL10n::model()->countByAttributes(['label_id' => $oldLabelIds]));
    }

    /**
     * Overwriting a label set that doesn't exist.
     *
     * @return void
     */
    public function testOverwriteUnknownLabelSet(): void
    {
        $this->expectException(NotFoundException::class);
        $this->getService()->overwrite(PHP_INT_MAX, ['en'], [['code' => 'A1']]);
    }

    /**
     * Reading a label set that doesn't exist.
     *
     * @return void
     */
    public function testGetUnknownLabelSet(): void
    {
        $this->expectException(NotFoundException::class);
        $this->getService()->getLabelSet(PHP_INT_MAX);
    }

    /**
     * Only sets that contain all requested languages are listed, with their counts.
     *
     * @return void
     */
    public function testListFiltersByAllLanguages(): void
    {
        $this->createLabelSet('en de');
        $this->createLabelSet('en', ['en'], [['code' => 'A1', 'l10ns' => ['en' => 'Yes']]]);

        $service = $this->getService();
        $this->assertSame(['en de', 'en'], array_keys($this->onlyTestSets($service->getLabelSets(['en']))));
        $this->assertSame(['en de'], array_keys($this->onlyTestSets($service->getLabelSets(['en', 'de']))));
        $this->assertSame([], $this->onlyTestSets($service->getLabelSets(['en', 'fr'])));

        $sets = $this->onlyTestSets($service->getLabelSets());
        $this->assertSame(2, $sets['en de']['labelCount']);
        $this->assertTrue($sets['en de']['hasAssessmentValues']);
        $this->assertSame(1, $sets['en']['labelCount']);
        $this->assertFalse($sets['en']['hasAssessmentValues']);
    }

    /**
     * A user without label set rights can't create or overwrite, and gets an empty overwrite list.
     *
     * @return void
     */
    public function testUserWithoutRights(): void
    {
        $labelSet = $this->createLabelSet('no rights');
        \Yii::app()->user->setId(self::$users['none']->uid);
        $service = $this->getService();

        $this->assertSame([], $this->onlyTestSets($service->getLabelSets([], true)));
        // Using a label set doesn't need a permission
        $this->assertSame(['no rights'], array_keys($this->onlyTestSets($service->getLabelSets())));

        try {
            $service->create(self::NAME_PREFIX . 'denied', ['en'], [['code' => 'A1']]);
            $this->fail('A user without the create right created a label set');
        } catch (PermissionDeniedException $exception) {
            // Expected
        }
        $this->expectException(PermissionDeniedException::class);
        $service->overwrite((int)$labelSet->lid, ['en'], [['code' => 'A1']]);
    }

    /**
     * Update rights on a single label set allow overwriting that set only.
     *
     * @return void
     */
    public function testUserWithUpdateRightOnOneSet(): void
    {
        $allowed = $this->createLabelSet('allowed');
        $other = $this->createLabelSet('other');
        $permission = new \Permission();
        $permission->entity = 'labelset';
        $permission->entity_id = $allowed->lid;
        $permission->uid = self::$users['setUpdater']->uid;
        $permission->permission = 'labelset';
        $permission->read_p = 1;
        $permission->update_p = 1;
        $this->assertTrue($permission->save());

        \Yii::app()->user->setId(self::$users['setUpdater']->uid);
        $service = $this->getService();

        $this->assertSame(['allowed'], array_keys($this->onlyTestSets($service->getLabelSets([], true))));
        $service->overwrite((int)$allowed->lid, ['en'], [['code' => 'B1']]);
        $this->assertSame(['B1'], array_column($service->getLabelSet((int)$allowed->lid)['labels'], 'code'));

        $this->expectException(PermissionDeniedException::class);
        $service->overwrite((int)$other->lid, ['en'], [['code' => 'B1']]);
    }

    /**
     * The creator owns the new set, so they may overwrite it without the global update right.
     *
     * @return void
     */
    public function testCreatorOwnsAndMayOverwriteTheirSet(): void
    {
        \Yii::app()->user->setId(self::$users['creator']->uid);
        $labelSet = $this->createLabelSet('own');

        $this->assertSame((int)self::$users['creator']->uid, (int)$labelSet->owner_id);
        $this->assertSame(['own'], array_keys($this->onlyTestSets($this->getService()->getLabelSets([], true))));
        $this->getService()->overwrite((int)$labelSet->lid, ['en'], [['code' => 'B1']]);
        $this->assertSame(['en'], $this->getService()->getLabelSet((int)$labelSet->lid)['languages']);
    }
}
