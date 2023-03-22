<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;
use LimeSurvey\Libraries\FormExtension\Renderer\RendererInterface;

class GlobalSettingsRenderer implements RendererInterface
{
    /**
     * @param InputInterface $input
     */
    public function render(InputInterface $input): string
    {
        return <<<HTML
<div class="row ls-space margin top-10">
    <div class="form-group col-xs-12">
        <label class="col-sm-12 control-label">{$input->getLabel()}</label>
        <div class="col-sm-12">
            {$input->render()}
        </div>
        {$this->renderHelpDiv($input->getHelp())}
    </div>
</div>
HTML;
    }


    /** @param ?string $help */
    protected function renderHelpDiv($help): string
    {
        if ($help) {
            return <<<HTML
<div class="col-sm-12 control-label">
    <span class="hint">{$help}</span>
</div>
HTML;
        } else {
            return '';
        }
    }
}
