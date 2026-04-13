<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_601 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Add button text column to boxes table
        try {
            setTransactionBookmark();
            $this->db->createCommand()->addColumn('{{boxes}}', 'buttontext', 'string(255)');
        } catch (\Exception $e) {
            // Column already exists - ignore
            rollBackToTransactionBookmark();
        }

        $this->updateCreateSurvey();
        $this->updateSurveyList();
        $this->updateGlobalSettings();
        $this->updateComfortUpdate();
        $this->updateLimeStore();
        $this->updateThemes();
    }

    public function updateCreateSurvey()
    {
        // Update existing boxes
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'desc' => 'Create a new survey from scratch. Or simply copy or import an existing survey.',
                    'buttontext' => 'Create survey'
                ],
                "title = 'Create survey' AND page = 'welcome'"
            );
    }

    public function updateSurveyList()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'desc' => 'Shows you all available surveys and survey groups.',
                    'buttontext' => 'View surveys'
                ],
                "title = 'Survey list' AND page = 'welcome'"
            );
    }


    public function updateGlobalSettings()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'ico' => 'ri-settings-5-line',
                    'buttontext' => 'View global settings'
                ],
                "title = 'Global settings' AND page = 'welcome'"
            );
    }

    public function updateComfortUpdate()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'ico' => 'ri-user-line',
                    'title' => 'Manage survey administrators',
                    'desc' => 'The user management allows you to add additional users to your survey administration.',
                    'buttontext' => 'Manage administrators',
                    'url' => 'userManagement/index'
                ],
                "title = 'ComfortUpdate' AND page = 'welcome'"
            );
    }

    public function updateLimeStore()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'ico' => 'ri-price-tag-3-line',
                    'title' => 'Label sets',
                    'desc' => 'Label sets can be used as answer options or subquestions to speed up creation of similar questions.',
                    'buttontext' => 'Edit label sets',
                    'url' => 'admin/labels/sa/view'
                ],
                "title = 'LimeStore' AND page = 'welcome'"
            );
    }

    public function updateThemes()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'ico' => 'ri-paint-brush-line',
                    'desc' => 'The themes functionality allows you to edit survey-, admin- or question themes.',
                    'buttontext' => 'Edit themes'
                ],
                "title = 'Themes' AND page = 'welcome'"
            );
    }
}
