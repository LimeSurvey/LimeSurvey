<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyThemeConfiguration;

/**
 * Tests for the inherited values shown on the theme options page.
 */
class SurveyThemeConfigurationTest extends TestBaseClass
{
    /** @var string Dropdown options like the "Variations" option of the bootswatch theme */
    private const BOOTSWATCH_OPTIONS = '<optgroup>'
        . '<option data-mode="replace" value="css/variations/basic.min.css">Basic Bootstrap</option>'
        . '<option data-mode="replace" value="css/variations/cerulean.min.css">Cerulean</option>'
        . '<option data-mode="replace" value="css/variations/flatly.min.css">Flatly</option>'
        . '</optgroup>';

    /** @var string Dropdown options like the "Theme color" option of the fruity_twentythree theme */
    private const FRUITY_OPTIONS = '<optgroup>'
        . '<option data-value="css/variations/theme_apple.css" value="apple">Apple</option>'
        . '<option data-value="css/variations/theme_grape.css" value="grape">Grape</option>'
        . '</optgroup>';

    /**
     * Calls a private method of the service.
     *
     * @param string $method method name
     * @param array $args method arguments
     * @return mixed
     */
    private function callPrivate(string $method, array $args)
    {
        $service = new SurveyThemeConfiguration(new \Permission());
        $reflection = new \ReflectionMethod($service, $method);
        return $reflection->invokeArgs($service, $args);
    }

    /**
     * Creates a theme configuration with the given css settings.
     *
     * @param string $cssFrameworkCss value of cssframework_css
     * @param string $filesCss value of files_css
     * @return \TemplateConfiguration
     */
    private function getThemeConfiguration(string $cssFrameworkCss, string $filesCss): \TemplateConfiguration
    {
        $theme = new \TemplateConfiguration();
        $theme->cssframework_css = $cssFrameworkCss;
        $theme->files_css = $filesCss;
        return $theme;
    }

    /**
     * The default bootswatch variation is only set as framework css replacement.
     */
    public function testVariationFromCssFrameworkReplacement()
    {
        $theme = $this->getThemeConfiguration(
            '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
            '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}'
        );
        $this->assertSame(
            'css/variations/flatly.min.css',
            $this->callPrivate('getVariationFromCssFiles', [$theme, self::BOOTSWATCH_OPTIONS])
        );
    }

    /**
     * A variation selected in the theme options is added to files_css and wins over the framework css.
     */
    public function testVariationFromFilesCssWinsOverFramework()
    {
        $theme = $this->getThemeConfiguration(
            '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
            '{"add":["css/ajaxify.css"],"replace":["css\/variations\/cerulean.min.css"]}'
        );
        $this->assertSame(
            'css/variations/cerulean.min.css',
            $this->callPrivate('getVariationFromCssFiles', [$theme, self::BOOTSWATCH_OPTIONS])
        );
    }

    /**
     * The css file can be the data-value of the option, the option value is returned.
     */
    public function testVariationFromDataValue()
    {
        $theme = $this->getThemeConfiguration('', '{"add":["css/variations/theme_grape.css","css/base.css"]}');
        $this->assertSame('grape', $this->callPrivate('getVariationFromCssFiles', [$theme, self::FRUITY_OPTIONS]));
    }

    /**
     * No variation without matching css file, invalid json or dropdown options.
     */
    public function testNoVariation()
    {
        $theme = $this->getThemeConfiguration('inherit', '{"add":["css/base.css"]}');
        $this->assertSame('', $this->callPrivate('getVariationFromCssFiles', [$theme, self::FRUITY_OPTIONS]));
        $this->assertSame('', $this->callPrivate('getVariationFromCssFiles', [$theme, '']));
    }

    /**
     * Dropdown options get the label of the inherited value, matched case-insensitively.
     */
    public function testInheritedValueLabels()
    {
        $optionAttributes = [
            'optionAttributes' => [
                'cssframework' => ['type' => 'dropdown', 'dropdownoptions' => self::FRUITY_OPTIONS],
                'variation' => ['type' => 'dropdown', 'dropdownoptions' => self::BOOTSWATCH_OPTIONS],
                'unknown' => ['type' => 'dropdown', 'dropdownoptions' => self::FRUITY_OPTIONS],
                'fontcolor' => ['type' => 'colorpicker'],
            ],
        ];
        $parentOptions = [
            'cssframework' => 'Grape',
            'variation' => 'css/variations/flatly.min.css',
            'unknown' => 'mango',
            'fontcolor' => '#444444',
        ];
        $method = new \ReflectionMethod(SurveyThemeConfiguration::class, 'addInheritedValueLabels');
        $method->invokeArgs(new SurveyThemeConfiguration(new \Permission()), [&$optionAttributes, $parentOptions]);

        $this->assertSame('Grape', $optionAttributes['optionAttributes']['cssframework']['inheritedvaluelabel']);
        $this->assertSame('Flatly', $optionAttributes['optionAttributes']['variation']['inheritedvaluelabel']);
        $this->assertArrayNotHasKey('inheritedvaluelabel', $optionAttributes['optionAttributes']['unknown']);
        $this->assertArrayNotHasKey('inheritedvaluelabel', $optionAttributes['optionAttributes']['fontcolor']);
    }

    /**
     * Changing the theme color replaces the variation file, whether the survey ID is an int or a string,
     * and repairs an empty entry saved before.
     */
    public function testUpdateThemeColorReplacesVariationFile()
    {
        \Yii::app()->session['loginID'] = 1;
        $survey = \Survey::model()->insertNewSurvey(['language' => 'en', 'template' => 'fruity_twentythree', 'admin' => 'test']);
        $criteria = ['sid' => $survey->sid, 'template_name' => 'fruity_twentythree'];
        $service = new SurveyThemeConfiguration(new \Permission());
        try {
            foreach (['blueberry' => (int)$survey->sid, 'grape' => (string)$survey->sid] as $color => $sid) {
                \Template::resetInstance();
                \TemplateConfiguration::$aPreparedToRender = [];
                $service->updateThemeOption($sid, ['templateName' => 'fruity_twentythree', 'cssframework' => $color]);
                $filesCss = json_decode(\TemplateConfiguration::model()->findByAttributes($criteria)->files_css, true);
                $this->assertSame("css/variations/theme_$color.css", $filesCss['add'][0]);
            }

            $broken = str_replace('css/variations/theme_grape.css', '', \TemplateConfiguration::model()->findByAttributes($criteria)->files_css);
            \TemplateConfiguration::model()->updateAll(['files_css' => $broken], 'sid = :sid', [':sid' => $survey->sid]);
            $service->updateThemeOption((int)$survey->sid, ['templateName' => 'fruity_twentythree', 'cssframework' => 'mango']);
            $filesCss = json_decode(\TemplateConfiguration::model()->findByAttributes($criteria)->files_css, true);
            $this->assertSame('css/variations/theme_mango.css', $filesCss['add'][0]);
        } finally {
            $survey->delete();
            \Template::resetInstance();
        }
    }
}
