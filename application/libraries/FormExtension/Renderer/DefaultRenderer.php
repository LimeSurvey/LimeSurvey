<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;

class DefaultRenderer implements RendererInterface
{
    /**
     * @param BaseInput $input
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
    </div>
</div>
HTML;
    }
}
