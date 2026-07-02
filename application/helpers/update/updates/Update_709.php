<?php

namespace LimeSurvey\Helpers\Update;

use CException;

/**
 * Add 'remove' entries for survey-theme-global assets to template_configuration rows
 * for fruity_twentythree and all themes that (directly or transitively) extend it.
 *
 * fruity_twentythree embeds the global CSS via SCSS @import and the global JS via
 * browserify, so the standalone Yii package (survey-theme-global-ltr/rtl) must not
 * also be loaded or its assets will appear twice on survey pages.
 *
 * The config.xml for fruity_twentythree already declares the removes; this migration
 * syncs those declarations into the template_configuration DB rows so that the
 * removeFiles() mechanism in TemplateConfig::prepareTemplateRendering() can act on them.
 *
 * Rules applied per row:
 *  - If files_js  is valid JSON: merge 'build/survey-theme-global.js' into remove[]
 *  - If files_css is valid JSON: merge 'build/survey-theme-global.css' and
 *                                      'build/survey-theme-global-rtl.css' into remove[]
 *  - Rows with 'inherit' are left untouched (they inherit from the updated parent row).
 *  - Already-present remove entries are not duplicated (idempotent).
 */
class Update_709 extends DatabaseUpdateBase
{
    /** @throws CException */
    public function up()
    {
        // 1. Collect fruity_twentythree and every theme that transitively extends it.
        //    $seen guards against cycles in the extends graph (e.g. A extends B extends A):
        //    we never enqueue a theme name more than once.
        $seen           = ['fruity_twentythree' => true];
        $affectedThemes = ['fruity_twentythree'];
        $toProcess      = ['fruity_twentythree'];

        while (!empty($toProcess)) {
            $children = $this->db->createCommand()
                ->select('name')
                ->from('{{templates}}')
                ->where(['IN', 'extends', $toProcess])
                ->queryColumn();

            $toProcess = [];
            foreach ($children as $child) {
                if (isset($seen[$child])) {
                    // Already visited — skip to prevent infinite loop on circular extends.
                    continue;
                }
                $seen[$child]     = true;
                $affectedThemes[] = $child;
                $toProcess[]      = $child;
            }
        }

        if (empty($affectedThemes)) {
            return;
        }

        // 2. Fetch all template_configuration rows for the affected themes.
        $rows = $this->db->createCommand()
            ->select('id, files_js, files_css')
            ->from('{{template_configuration}}')
            ->where(['IN', 'template_name', $affectedThemes])
            ->queryAll();

        foreach ($rows as $row) {
            $updates = [];

            // --- files_js ---
            if ($row['files_js'] !== 'inherit' && !empty($row['files_js'])) {
                $data = json_decode($row['files_js'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (!isset($data['remove'])) {
                        $data['remove'] = [];
                    }
                    if (!in_array('build/survey-theme-global.js', $data['remove'], true)) {
                        $data['remove'][] = 'build/survey-theme-global.js';
                        $updates['files_js'] = json_encode($data);
                    }
                }
            }

            // --- files_css ---
            if ($row['files_css'] !== 'inherit' && !empty($row['files_css'])) {
                $data = json_decode($row['files_css'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (!isset($data['remove'])) {
                        $data['remove'] = [];
                    }
                    $cssToRemove = [
                        'build/survey-theme-global.css',
                        'build/survey-theme-global-rtl.css',
                    ];
                    $changed = false;
                    foreach ($cssToRemove as $cssFile) {
                        if (!in_array($cssFile, $data['remove'], true)) {
                            $data['remove'][] = $cssFile;
                            $changed = true;
                        }
                    }
                    if ($changed) {
                        $updates['files_css'] = json_encode($data);
                    }
                }
            }

            if (!empty($updates)) {
                $this->db->createCommand()->update(
                    '{{template_configuration}}',
                    $updates,
                    'id = :id',
                    [':id' => $row['id']]
                );
            }
        }
    }
}