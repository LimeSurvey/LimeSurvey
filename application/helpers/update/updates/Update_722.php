<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_722 extends DatabaseUpdateBase
{
    /** The bundled question themes that allow adding and removing subquestions after the survey was activated */
    private const ACTIVE_EDIT_THEMES = ['ranking', 'ranking_advanced'];

    /**
     * Add the new subquestionsactiveedit setting (from the question theme XML) to the stored question theme settings.
     * Only the bundled ranking themes set it, all other themes (including custom themes without the tag) get "0".
     * The bundled themes get the new minor version of their XML files.
     *
     * @inheritDoc
     * @throws CException
     */
    #[\Override]
    public function up()
    {
        $this->updateSettings();
        $this->updateBundledThemeVersions();
    }

    /**
     * Add the subquestionsactiveedit setting to all question themes that don't have it yet
     * @return void
     */
    private function updateSettings()
    {
        $questionThemes = $this->db->createCommand()
            ->select('id, name, question_type, core_theme, settings')
            ->from('{{question_themes}}')
            ->queryAll();
        foreach ($questionThemes as $questionTheme) {
            $settings = json_decode((string) $questionTheme['settings'], true);
            if (!is_array($settings) || array_key_exists('subquestionsactiveedit', $settings)) {
                continue;
            }
            $isActiveEditTheme = !empty($questionTheme['core_theme'])
                && $questionTheme['question_type'] === 'R'
                && in_array($questionTheme['name'], self::ACTIVE_EDIT_THEMES, true);
            $value = $isActiveEditTheme ? '1' : '0';
            $this->db->createCommand()->update(
                '{{question_themes}}',
                ['settings' => json_encode($this->addSetting($settings, $value))],
                'id = :id',
                [':id' => $questionTheme['id']]
            );
        }
    }

    /**
     * Set the versions of the bundled question themes (core_theme = 1) to the versions of their XML files
     * @return void
     */
    private function updateBundledThemeVersions()
    {
        $this->db->createCommand()->update(
            '{{question_themes}}',
            ['version' => '1.2.0'],
            "core_theme = :core_theme AND name = 'bootstrap_buttons_multi' AND version IN ('1.0', '1.1')",
            // Bound parameter: core_theme is a boolean on Postgres
            [':core_theme' => true]
        );
        $this->db->createCommand()->update(
            '{{question_themes}}',
            ['version' => '1.1.0'],
            "core_theme = :core_theme AND version = '1.0'",
            [':core_theme' => true]
        );
    }

    /**
     * Insert the subquestionsactiveedit setting after answerscales, like the XML loader does
     * @param array $settings
     * @param string $value
     * @return array
     */
    private function addSetting(array $settings, string $value)
    {
        $newSettings = [];
        foreach ($settings as $key => $setting) {
            $newSettings[$key] = $setting;
            if ($key === 'answerscales') {
                $newSettings['subquestionsactiveedit'] = $value;
            }
        }
        // Settings without answerscales
        $newSettings['subquestionsactiveedit'] = $value;
        return $newSettings;
    }
}
