<?php

namespace LimeSurvey\Helpers\Update;

class Update_438 extends DatabaseUpdateBase
{
    public function run()
    {

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'hidden' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'hidden' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'statistics_showgraph' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'statistics_showgraph' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'public_statistics' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'public_statistics' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'page_break' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'page_break' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'other_numbers_only' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'other_numbers_only' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'other_comment_mandatory' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'other_comment_mandatory' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'hide_tip' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'hide_tip' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'exclude_all_others_auto' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'exclude_all_others_auto' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'commented_checkbox_auto' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'commented_checkbox_auto' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'num_value_int_only' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'num_value_int_only' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'alphasort' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'alphasort' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'use_dropdown' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'use_dropdown' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'num_value_int_only' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'num_value_int_only' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_default_set' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_default_set' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_layout' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_layout' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_middlestart' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_middlestart' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_reset' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_reset' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_reversed' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_reversed' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'slider_showminmax' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'slider_showminmax' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'value_range_allows_missing' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'value_range_allows_missing' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'multiflexible_checkbox' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'multiflexible_checkbox' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'reverse' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'reverse' and value = 'N'"
            );

            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '1'),
                "attribute = 'input_boxes' and value = 'Y'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('value' => '0'),
                "attribute = 'input_boxes' and value = 'N'"
            );


    }
}