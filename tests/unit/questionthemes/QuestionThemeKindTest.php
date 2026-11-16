<?php

namespace ls\tests;

use QuestionTheme;

/**
 * Kind of a question theme (question type, core theme or user theme) as shown and filtered in the question theme list.
 */
class QuestionThemeKindTest extends TestBaseClass
{
    /**
     * Each combination of core_theme and extends gets its own label.
     *
     * @return void
     */
    public function testThemeKindLabel()
    {
        $labels = QuestionTheme::getThemeKindLabels();

        $questionType = new QuestionTheme();
        $questionType->core_theme = 1;
        $questionType->extends = '';
        $this->assertSame($labels[QuestionTheme::THEME_TYPE_CORE], $questionType->getThemeKindLabel());

        $coreTheme = new QuestionTheme();
        $coreTheme->core_theme = 1;
        $coreTheme->extends = 'L';
        $this->assertSame($labels[QuestionTheme::THEME_TYPE_CUSTOM], $coreTheme->getThemeKindLabel());

        $userTheme = new QuestionTheme();
        $userTheme->core_theme = 0;
        $userTheme->extends = 'L';
        $this->assertSame($labels[QuestionTheme::THEME_TYPE_USER], $userTheme->getThemeKindLabel());

        $this->assertCount(3, array_unique($labels));
    }

    /**
     * Filtering the list by kind only returns themes of that kind.
     *
     * @return void
     */
    public function testSearchFiltersByThemeKind()
    {
        $labels = QuestionTheme::getThemeKindLabels();
        foreach ([QuestionTheme::THEME_TYPE_CORE, QuestionTheme::THEME_TYPE_CUSTOM] as $themeKind) {
            $themes = $this->searchByThemeKind($themeKind);
            $this->assertNotEmpty($themes, $themeKind);
            foreach ($themes as $theme) {
                $this->assertSame($labels[$themeKind], $theme->getThemeKindLabel(), $theme->name);
            }
        }
        foreach ($this->searchByThemeKind(QuestionTheme::THEME_TYPE_USER) as $theme) {
            $this->assertSame($labels[QuestionTheme::THEME_TYPE_USER], $theme->getThemeKindLabel(), $theme->name);
        }
    }

    /**
     * Returns all question themes found by search() for the given kind.
     *
     * @param string $themeKind One of the QuestionTheme::THEME_TYPE_* constants
     * @return QuestionTheme[]
     */
    private function searchByThemeKind($themeKind)
    {
        $model = new QuestionTheme('search');
        $model->unsetAttributes();
        $model->themeKind = $themeKind;
        $dataProvider = $model->search();
        $dataProvider->setPagination(false);
        return $dataProvider->getData();
    }
}
