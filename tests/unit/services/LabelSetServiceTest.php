<?php

namespace ls\tests;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\BadRequestException;
use LimeSurvey\Models\Services\Exception\ConflictException;
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
     * A label set as returned by getLabelSet(), with the translations in a fixed order.
     *
     * @param array $labelSet
     * @return array
     */
    private function normaliseLabelSet(array $labelSet): array
    {
        foreach ($labelSet['labels'] as &$label) {
            ksort($label['l10ns']);
        }
        return $labelSet;
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
            'too short' => [['en', 'x']],
            'too long' => [[str_repeat('a', LabelSetService::MAX_LANGUAGE_LENGTH + 1)]],
        ];
    }

    /**
     * An overwrite with invalid languages is rejected before anything is deleted.
     *
     * @dataProvider invalidLanguagesProvider
     * @param array $languages
     * @return void
     */
    public function testOverwriteWithInvalidLanguagesKeepsTheOldLabels(array $languages): void
    {
        $labelSet = $this->createLabelSet('keep');
        $before = $this->getService()->getLabelSet((int)$labelSet->lid);

        try {
            $this->getService()->overwrite((int)$labelSet->lid, $languages, [
                ['code' => 'B1', 'l10ns' => ['en' => 'Maybe', 'x' => 'X']],
            ]);
            $this->fail('The invalid languages were accepted');
        } catch (BadRequestException $exception) {
            // Expected
        }

        $this->assertSame(
            $this->normaliseLabelSet($before),
            $this->normaliseLabelSet($this->getService()->getLabelSet((int)$labelSet->lid))
        );
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
            'translation too long' => [[[
                'code' => 'A1',
                'l10ns' => ['en' => str_repeat('a', LabelSetService::MAX_TITLE_BYTES + 1)],
            ]]],
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
        $before = $this->getService()->getLabelSet((int)$labelSet->lid);

        try {
            $this->getService()->overwrite((int)$labelSet->lid, ['en'], $labels);
            $this->fail('The invalid labels were accepted');
        } catch (BadRequestException $exception) {
            // Expected
        }

        // Languages, codes, translations and assessment values are all unchanged
        $this->assertSame(
            $this->normaliseLabelSet($before),
            $this->normaliseLabelSet($this->getService()->getLabelSet((int)$labelSet->lid))
        );
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
     * An overwrite with the revision the user reviewed replaces the set and gives it a new revision.
     *
     * @return void
     */
    public function testOverwriteWithCurrentRevision(): void
    {
        $labelSet = $this->createLabelSet('current revision');
        $reviewed = $this->getService()->getLabelSet((int)$labelSet->lid);

        $this->getService()->overwrite((int)$labelSet->lid, ['en'], [
            ['code' => 'B1', 'l10ns' => ['en' => 'Maybe']],
        ], $reviewed['revision']);

        $result = $this->getService()->getLabelSet((int)$labelSet->lid);
        $this->assertSame('B1', $result['labels'][0]['code']);
        $this->assertNotSame($reviewed['revision'], $result['revision']);
    }

    /**
     * An overwrite with an outdated revision fails and keeps what someone else saved meanwhile.
     *
     * @return void
     */
    public function testOverwriteWithOutdatedRevisionKeepsTheChanges(): void
    {
        $labelSet = $this->createLabelSet('outdated revision', ['en']);
        $lid = (int)$labelSet->lid;
        $reviewed = $this->getService()->getLabelSet($lid);
        // Someone else adds German and an assessment value after the user reviewed the set
        $this->getService()->overwrite($lid, ['en', 'de'], [
            ['code' => 'A1', 'assessmentValue' => 5, 'l10ns' => ['en' => 'Yes', 'de' => 'Ja']],
        ]);
        $changed = $this->getService()->getLabelSet($lid);

        try {
            $this->getService()->overwrite($lid, ['en'], [
                ['code' => 'B1', 'l10ns' => ['en' => 'Maybe']],
            ], $reviewed['revision']);
            $this->fail('The overwrite with an outdated revision must fail');
        } catch (ConflictException $exception) {
            $this->assertSame(
                $this->normaliseLabelSet($changed),
                $this->normaliseLabelSet($this->getService()->getLabelSet($lid))
            );
        }
    }

    /**
     * The revision also changes when the labels are saved without the service, e.g. by the label set editor.
     *
     * @return void
     */
    public function testRevisionChangesWithEveryWriter(): void
    {
        $labelSet = $this->createLabelSet('revision');
        $lid = (int)$labelSet->lid;
        $before = $this->getService()->getLabelSet($lid)['revision'];

        $label = \Label::model()->findByAttributes(['lid' => $lid, 'code' => 'A1']);
        \LabelL10n::model()->updateAll(
            ['title' => 'Yes indeed'],
            'label_id = :id AND language = :language',
            [':id' => $label->id, ':language' => 'en']
        );

        $this->assertNotSame($before, $this->getService()->getLabelSet($lid)['revision']);
    }

    /**
     * On MySQL, an overwrite doesn't run while another overwrite of the same set holds its lock,
     * and releases the lock when it is done.
     *
     * @group mysql
     * @return void
     */
    public function testOverwriteWaitsForTheLockOfTheSet(): void
    {
        $labelSet = $this->createLabelSet('lock');
        $lid = (int)$labelSet->lid;
        $before = $this->getService()->getLabelSet($lid);
        // Don't wait 10 seconds for the lock in the test
        $service = new class (\LabelSet::model(), \Permission::model(), \Yii::app()) extends LabelSetService {
            protected int $lockTimeout = 0;

            /**
             * The lock name of a label set.
             *
             * @param int $lid Label set id
             * @return string Lock name
             */
            public function lockName(int $lid): string
            {
                return $this->getLockName($lid);
            }
        };
        $db = \Yii::app()->db;
        $otherRequest = new \CDbConnection($db->connectionString, $db->username, $db->password);
        $otherRequest->active = true;
        $lockName = $service->lockName($lid);

        try {
            $this->assertSame(
                '1',
                (string)$otherRequest->createCommand('SELECT GET_LOCK(:name, 0)')->queryScalar([':name' => $lockName])
            );
            try {
                $service->overwrite($lid, ['en'], [['code' => 'B1', 'l10ns' => ['en' => 'Maybe']]]);
                $this->fail('The overwrite must not run while another request holds the lock');
            } catch (ConflictException $exception) {
                $this->assertSame($before, $this->getService()->getLabelSet($lid));
            }
            $otherRequest->createCommand('SELECT RELEASE_LOCK(:name)')->queryScalar([':name' => $lockName]);

            $service->overwrite($lid, ['en'], [['code' => 'B1', 'l10ns' => ['en' => 'Maybe']]]);
            $this->assertSame('B1', $this->getService()->getLabelSet($lid)['labels'][0]['code']);
            $this->assertSame(
                '1',
                (string)$otherRequest->createCommand('SELECT GET_LOCK(:name, 0)')->queryScalar([':name' => $lockName]),
                'The overwrite must release the lock'
            );
        } finally {
            $otherRequest->createCommand('SELECT RELEASE_LOCK(:name)')->queryScalar([':name' => $lockName]);
            $otherRequest->active = false;
        }
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
        // Sorted by name; created in the reverse order, so this checks the sorting
        $this->assertSame(['en', 'en de'], array_keys($this->onlyTestSets($service->getLabelSets(['en']))));
        $this->assertSame(['en de'], array_keys($this->onlyTestSets($service->getLabelSets(['en', 'de']))));
        $this->assertSame([], $this->onlyTestSets($service->getLabelSets(['en', 'fr'])));

        $sets = $this->onlyTestSets($service->getLabelSets());
        $this->assertSame(2, $sets['en de']['labelCount']);
        $this->assertTrue($sets['en de']['hasAssessmentValues']);
        $this->assertSame(1, $sets['en']['labelCount']);
        $this->assertFalse($sets['en']['hasAssessmentValues']);
    }

    /**
     * An invalid language in the filter matches no set instead of being dropped; empty entries mean no filter.
     *
     * @return void
     */
    public function testListWithInvalidLanguageFilter(): void
    {
        $this->createLabelSet('filter');
        $service = $this->getService();

        $this->assertSame([], $service->getLabelSets(['123']));
        $this->assertSame([], $service->getLabelSets(['en', 'en_GB']));
        $this->assertSame([], $service->getLabelSets([['en']]));
        $this->assertSame(['filter'], array_keys($this->onlyTestSets($service->getLabelSets(['']))));
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
        $this->assertFalse($service->hasUpdatableLabelSet());
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
        $this->assertTrue($service->hasUpdatableLabelSet());
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
