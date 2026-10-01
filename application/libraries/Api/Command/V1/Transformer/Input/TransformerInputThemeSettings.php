<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;
use Template;
use TemplateManifest;

class TransformerInputThemeSettings extends Transformer
{
    public function __construct()
    {
        $entity = App()->request->getParam('_entity');
        if (isset($entity) && $entity === 'survey-detail') {
            $surveyId = App()->request->getParam('_id');
            if (isset($surveyId)) {
                $surveyId = (int)$surveyId;
                $templateConfiguration = Template::getTemplateConfiguration(null, $surveyId, null, true);
                $themeCategoriesAndOptions = TemplateManifest::getOptionAttributes($templateConfiguration->path);
                $dynamicDataMap = [];
                if (isset($themeCategoriesAndOptions['optionAttributes'])) {
                    foreach ($themeCategoriesAndOptions['optionAttributes'] as $key => $attribute) {
                        $dynamicDataMap[$key] = true;
                    }
                }
            }
        }
        $dataMap = array_merge(
            [
                'templateName'             => [
                    'key'  => 'templateName',
                    'type' => 'string',
                    'required'
                ]
            ],
            $dynamicDataMap ?? []
        );
        $this->setDataMap($dataMap);
    }
}
