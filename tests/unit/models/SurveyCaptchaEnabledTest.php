<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyUseCaptcha;

class SurveyCaptchaEnabledTest extends TestBaseClass
{
    private const ORIGINS = ['survey', 'group', 'parent', 'global'];
    private const VALUES = ['Y', 'N'];
    private const SCREENS = [
        'surveyAccess' => 'surveyaccessscreen',
        'registration' => 'registrationscreen',
        'saveAndLoad' => 'saveandloadscreen',
    ];

    /** @var \SurveysGroups */
    private static $parentGroup;

    /** @var \SurveysGroups */
    private static $childGroup;

    /** @var \SurveysGroupsettings */
    private static $parentGroupSettings;

    /** @var \SurveysGroupsettings */
    private static $childGroupSettings;

    /** @var \SurveysGroupsettings */
    private static $globalGroupSettings;

    /** @var string */
    private static $originalGlobalUseCaptcha;

    /** @var SurveyUseCaptcha */
    private static $encoder;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $parentGroup = new \SurveysGroups();
        $parentGroup->name = 'captcha_parent_group';
        $parentGroup->sortorder = 0;
        $parentGroup->created_by = 1;
        $parentGroup->title = 'Captcha parent group';
        $parentGroup->description = 'Parent survey group used for Survey::isCaptchaEnabled() tests.';
        $parentGroup->owner_id = 1;
        $parentGroup->alwaysavailable = 1;
        $parentGroup->save();

        $childGroup = new \SurveysGroups();
        $childGroup->name = 'captcha_child_group';
        $childGroup->sortorder = 0;
        $childGroup->created_by = 1;
        $childGroup->title = 'Captcha child group';
        $childGroup->description = 'Child survey group used for Survey::isCaptchaEnabled() tests.';
        $childGroup->owner_id = 1;
        $childGroup->parent_id = $parentGroup->gsid;
        $childGroup->alwaysavailable = 1;
        $childGroup->save();

        $parentGroupSettings = new \SurveysGroupsettings();
        $parentGroupSettings->gsid = $parentGroup->gsid;
        $parentGroupSettings->usecaptcha = 'N';
        $parentGroupSettings->save(false);

        $childGroupSettings = new \SurveysGroupsettings();
        $childGroupSettings->gsid = $childGroup->gsid;
        $childGroupSettings->usecaptcha = 'N';
        $childGroupSettings->save(false);

        self::$parentGroup = $parentGroup;
        self::$childGroup = $childGroup;
        self::$parentGroupSettings = $parentGroupSettings;
        self::$childGroupSettings = $childGroupSettings;
        self::$globalGroupSettings = \SurveysGroupsettings::model()->findByPk(0);
        self::$originalGlobalUseCaptcha = self::$globalGroupSettings->usecaptcha;

        self::$encoder = new SurveyUseCaptcha();
    }

    public static function tearDownAfterClass(): void
    {
        self::$globalGroupSettings->usecaptcha = self::$originalGlobalUseCaptcha;
        self::$globalGroupSettings->save(false);

        self::$childGroupSettings->delete();
        self::$parentGroupSettings->delete();
        self::$childGroup->delete();
        self::$parentGroup->delete();
        self::$encoder = null;

        parent::tearDownAfterClass();
    }

    /**
     * Tests each screen's inheritance chain independently (24 cases).
     * One screen varies across 4 origins (survey, group, parent, global) × 2 values (Y, N),
     * while the other two screens are pinned to a fixed survey-level value.
     * Exercises the full DB-backed resolution: survey → group → parent → global.
     *
     * @dataProvider provideSingleScreenInheritanceCases
     */
    public function testIsCaptchaEnabledResolvesSingleScreenInheritance(array $encodedValues, array $expectedValues, string $label)
    {
        $this->assertCaptchaResolution($encodedValues, $expectedValues, $label);
    }

    /**
     * Tests that screens can resolve from different inheritance origins simultaneously (8 cases).
     * Each case assigns a different origin to each screen (e.g. surveyAccess from survey,
     * registration from global, saveAndLoad from parent) and verifies the packed single-char
     * encoding doesn't cause cross-screen interference during DB-backed resolution.
     *
     * @dataProvider provideMixedInheritanceCases
     */
    public function testIsCaptchaEnabledResolvesMixedOriginsAcrossScreens(array $encodedValues, array $expectedValues, string $label)
    {
        $this->assertCaptchaResolution($encodedValues, $expectedValues, $label);
    }

    private function assertCaptchaResolution(array $encodedValues, array $expectedValues, string $label): void
    {
        self::$globalGroupSettings->usecaptcha = $encodedValues['global'];
        self::$globalGroupSettings->save(false);

        self::$parentGroupSettings->usecaptcha = $encodedValues['parent'];
        self::$parentGroupSettings->save(false);

        self::$childGroupSettings->usecaptcha = $encodedValues['group'];
        self::$childGroupSettings->save(false);

        $survey = new \Survey();
        $survey->gsid = (int) self::$childGroup->gsid;
        $survey->usecaptcha = $encodedValues['survey'];

        foreach (self::SCREENS as $component => $screen) {
            $this->assertSame(
                $expectedValues[$component],
                $survey->isCaptchaEnabled($screen),
                sprintf(
                    '%s | survey=%s group=%s parent=%s global=%s | screen=%s',
                    $label,
                    $encodedValues['survey'],
                    $encodedValues['group'],
                    $encodedValues['parent'],
                    $encodedValues['global'],
                    $screen
                )
            );
        }
    }

    /**
     * Cover each screen's full inheritance chain independently.
     */
    public static function provideSingleScreenInheritanceCases(): array
    {
        $cases = [];

        foreach (array_keys(self::SCREENS) as $component) {
            foreach (self::ORIGINS as $origin) {
                foreach (self::VALUES as $value) {
                    $label = sprintf('%s=%s:%s', $component, $origin, $value);
                    $cases[$label] = self::buildCase(
                        self::buildSelections([
                            $component => [
                                'origin' => $origin,
                                'value' => $value,
                            ],
                        ]),
                        $label
                    );
                }
            }
        }

        return $cases;
    }

    /**
     * Ensure screens can resolve from different origins at the same time.
     */
    public static function provideMixedInheritanceCases(): array
    {
        $cases = [];
        $definitions = [
            'survey-group-parent mix' => [
                'surveyAccess' => ['origin' => 'survey', 'value' => 'Y'],
                'registration' => ['origin' => 'group', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'parent', 'value' => 'Y'],
            ],
            'group-parent-global mix' => [
                'surveyAccess' => ['origin' => 'group', 'value' => 'N'],
                'registration' => ['origin' => 'parent', 'value' => 'Y'],
                'saveAndLoad' => ['origin' => 'global', 'value' => 'N'],
            ],
            'global-parent-group mix' => [
                'surveyAccess' => ['origin' => 'global', 'value' => 'Y'],
                'registration' => ['origin' => 'parent', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'group', 'value' => 'Y'],
            ],
            'parent-global-survey mix' => [
                'surveyAccess' => ['origin' => 'parent', 'value' => 'N'],
                'registration' => ['origin' => 'global', 'value' => 'Y'],
                'saveAndLoad' => ['origin' => 'survey', 'value' => 'Y'],
            ],
            'all-global mix' => [
                'surveyAccess' => ['origin' => 'global', 'value' => 'Y'],
                'registration' => ['origin' => 'global', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'global', 'value' => 'Y'],
            ],
            'all-survey Y mix' => [
                'surveyAccess' => ['origin' => 'survey', 'value' => 'Y'],
                'registration' => ['origin' => 'survey', 'value' => 'Y'],
                'saveAndLoad' => ['origin' => 'survey', 'value' => 'Y'],
            ],
            'all-survey N mix' => [
                'surveyAccess' => ['origin' => 'survey', 'value' => 'N'],
                'registration' => ['origin' => 'survey', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'survey', 'value' => 'N'],
            ],
            'survey-global-parent alternating mix' => [
                'surveyAccess' => ['origin' => 'survey', 'value' => 'N'],
                'registration' => ['origin' => 'global', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'parent', 'value' => 'N'],
            ],
        ];

        foreach ($definitions as $label => $componentSelections) {
            $cases[$label] = self::buildCase(self::buildSelections($componentSelections), $label);
        }

        return $cases;
    }

    private static function buildSelections(array $overrides): array
    {
        return array_replace(
            [
                'surveyAccess' => ['origin' => 'survey', 'value' => 'N'],
                'registration' => ['origin' => 'survey', 'value' => 'N'],
                'saveAndLoad' => ['origin' => 'survey', 'value' => 'N'],
            ],
            $overrides
        );
    }

    private static function buildCase(array $componentSelections, string $label): array
    {
        $levels = [
            'survey' => [],
            'group' => [],
            'parent' => [],
            'global' => [],
        ];
        $expectedValues = [];

        foreach ($componentSelections as $component => $selection) {
            $levelValues = self::buildLevelValuesForSelection($selection['origin'], $selection['value']);

            foreach ($levelValues as $level => $levelValue) {
                $levels[$level][$component] = $levelValue;
            }

            $expectedValues[$component] = $selection['value'] === 'Y';
        }

        return [
            [
                'survey' => self::encodeUseCaptcha($levels['survey']),
                'group' => self::encodeUseCaptcha($levels['group']),
                'parent' => self::encodeUseCaptcha($levels['parent']),
                'global' => self::encodeUseCaptcha($levels['global']),
            ],
            $expectedValues,
            $label,
        ];
    }

    private static function buildLevelValuesForSelection(string $origin, string $value): array
    {
        $opposite = $value === 'Y' ? 'N' : 'Y';

        switch ($origin) {
            case 'survey':
                return [
                    'survey' => $value,
                    'group' => $opposite,
                    'parent' => $opposite,
                    'global' => $opposite,
                ];
            case 'group':
                return [
                    'survey' => 'I',
                    'group' => $value,
                    'parent' => $opposite,
                    'global' => $opposite,
                ];
            case 'parent':
                return [
                    'survey' => 'I',
                    'group' => 'I',
                    'parent' => $value,
                    'global' => $opposite,
                ];
            case 'global':
                return [
                    'survey' => 'I',
                    'group' => 'I',
                    'parent' => 'I',
                    'global' => $value,
                ];
        }

        throw new \InvalidArgumentException("Unsupported origin: {$origin}");
    }

    private static function encodeUseCaptcha(array $componentValues): string
    {
        return self::getEncoder()->convertUseCaptchaForDB(
            $componentValues['surveyAccess'],
            $componentValues['registration'],
            $componentValues['saveAndLoad']
        );
    }

    private static function getEncoder(): SurveyUseCaptcha
    {
        if (self::$encoder === null) {
            self::$encoder = new SurveyUseCaptcha(0, new \Survey());
        }

        return self::$encoder;
    }
}
