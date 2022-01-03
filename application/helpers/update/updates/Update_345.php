<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_345 extends DatabaseUpdateBase
{
    public function up()
    {
        $fruityConf = $this->db
           ->createCommand()
           ->select('*')
           ->from('{{template_configuration}}')
           ->where('template_name=:template_name', ['template_name' => 'fruity'])
           ->queryRow();
        if ($fruityConf) {
            // Brute force way. Just have to hope noone changed the default
            // config yet.
            $this->db->createCommand()->update(
                '{{template_configuration}}',
                [
                    'files_css' => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
                    'files_js' => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css' => '{"add":["css/print_theme.css"]}',
                    'options' => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3","showpopups":"1", "showclearall":"off", "questionhelptextposition":"top"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css' => '{}',
                    'cssframework_js' => '',
                    'packages_to_load' => '{"add":["pjax","font-noto","moment"]}',
                ],
                "template_name = 'fruity'"
            );
        } else {
            $fruityConfData = [
                'template_name' => 'fruity',
                'sid' => null,
                'gsid' => null,
                'uid' => null,
                'files_css' => '{"add":["css/ajaxify.css","css/animate.css","css/variations/sea_green.css","css/theme.css","css/custom.css"]}',
                'files_js' => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                'files_print_css' => '{"add":["css/print_theme.css"]}',
                'options' => '{"ajaxmode":"off","brandlogo":"on","brandlogofile":"./files/logo.png","container":"on","backgroundimage":"off","backgroundimagefile":"./files/pattern.png","animatebody":"off","bodyanimation":"fadeInRight","bodyanimationduration":"1.0","animatequestion":"off","questionanimation":"flipInX","questionanimationduration":"1.0","animatealert":"off","alertanimation":"shake","alertanimationduration":"1.0","font":"noto","bodybackgroundcolor":"#ffffff","fontcolor":"#444444","questionbackgroundcolor":"#ffffff","questionborder":"on","questioncontainershadow":"on","checkicon":"f00c","animatecheckbox":"on","checkboxanimation":"rubberBand","checkboxanimationduration":"0.5","animateradio":"on","radioanimation":"zoomIn","radioanimationduration":"0.3","showpopups":"1", "showclearall":"off", "questionhelptextposition":"top"}',
                'cssframework_name' => 'bootstrap',
                'cssframework_css' => '{}',
                'cssframework_js' => '',
                'packages_to_load' => '{"add":["pjax","font-noto","moment"]}',
                'packages_ltr' => null,
                'packages_rtl' => null
            ];
            $this->db->createCommand()->insert('{{template_configuration}}', $fruityConfData);
        }
        $bootswatchConf = $this->db
           ->createCommand()
           ->select('*')
           ->from('{{template_configuration}}')
           ->where('template_name=:template_name', ['template_name' => 'bootswatch'])
           ->queryRow();
        if ($bootswatchConf) {
            $this->db->createCommand()->update(
                '{{template_configuration}}',
                [
                    'files_css' => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                    'files_js' => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                    'files_print_css' => '{"add":["css/print_theme.css"]}',
                    'options' => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
                    'cssframework_name' => 'bootstrap',
                    'cssframework_css' => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
                    'cssframework_js' => '',
                    'packages_to_load' => '{"add":["pjax","font-noto"]}',
                ],
                "template_name = 'bootswatch'"
            );
        } else {
            $bootswatchConfData = [
                'template_name' => 'bootswatch',
                'sid' => null,
                'gsid' => null,
                'uid' => null,
                'files_css' => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
                'files_js' => '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
                'files_print_css' => '{"add":["css/print_theme.css"]}',
                'options' => '{"ajaxmode":"off","brandlogo":"on","container":"on","brandlogofile":"./files/logo.png"}',
                'cssframework_name' => 'bootstrap',
                'cssframework_css' => '{"replace":[["css/bootstrap.css","css/variations/flatly.min.css"]]}',
                'cssframework_js' => '',
                'packages_to_load' => '{"add":["pjax","font-noto"]}',
                'packages_ltr' => null,
                'packages_rtl' => null
            ];
            $this->db->createCommand()->insert('{{template_configuration}}', $bootswatchConfData);
        }
    }
}
