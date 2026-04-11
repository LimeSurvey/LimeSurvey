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
     * @dataProvider provideCaptchaInheritanceCombinations
     */
    public function testIsCaptchaEnabledAcrossAllOptionAndInheritanceCombinations(array $encodedValues, array $expectedValues, string $label)
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
     * Build the full matrix of effective CAPTCHA origins:
     * survey fixed, direct group inheritance, parent group inheritance, and global inheritance,
     * with both Y and N at the selected origin for each of the three CAPTCHA components.
     */
    public static function provideCaptchaInheritanceCombinations(): array
    {
        $cases = [];

        foreach (self::buildComponentOptions() as $surveyAccess) {
            foreach (self::buildComponentOptions() as $registration) {
                foreach (self::buildComponentOptions() as $saveAndLoad) {
                    $componentSelections = [
                        'surveyAccess' => $surveyAccess,
                        'registration' => $registration,
                        'saveAndLoad' => $saveAndLoad,
                    ];
                    $levels = [
                        'survey' => [],
                        'group' => [],
                        'parent' => [],
                        'global' => [],
                    ];
                    $expectedValues = [];
                    $labelParts = [];

                    foreach ($componentSelections as $component => $selection) {
                        $levelValues = self::buildLevelValuesForSelection($selection['origin'], $selection['value']);

                        foreach ($levelValues as $level => $levelValue) {
                            $levels[$level][$component] = $levelValue;
                        }

                        $expectedValues[$component] = $selection['value'] === 'Y';
                        $labelParts[] = sprintf('%s=%s:%s', $component, $selection['origin'], $selection['value']);
                    }

                    $label = implode(', ', $labelParts);
                    $cases[$label] = [
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
            }
        }

        return $cases;
    }

    private static function buildComponentOptions(): array
    {
        $options = [];

        foreach (self::ORIGINS as $origin) {
            foreach (self::VALUES as $value) {
                $options[] = [
                    'origin' => $origin,
                    'value' => $value,
                ];
            }
        }

        return $options;
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
