<?php

namespace LimeSurvey\Libraries\FormExtension\Inputs;

class GlobalSettingsRenderer extends DefaultBaseRenderer
{
    /**
     * @param RawHtmlInput|BaseInput|FileInput $input
     */
    public function run($input): string
    {
        switch (true) {
            case $input instanceof FileInput:
                $id = $input->getId() ? 'id="' . $input->getId() . '"' : '';
                $disabled = $input->isDisabled() ? "disabled" : '';
                [$tooltipText, $tooltipTrigger] = $this->bakeTooltip($input->getTooltip());
                $helpDiv = $this->bakeHelpDiv($input->getHelp());
                return <<<HTML
<div class="row ls-space margin top-10">
    <div class="form-group col-xs-12">
        <label class="col-sm-12 control-label">{$input->getLabel()}</label>
        <div class="col-sm-12">
            <input
                class="form-control"
                {$id} {$input->getAcceptHtml()} {$disabled} {$tooltipTrigger} {$tooltipText}
                name="{$input->getName()}"
                type="file"
            />
        </div>
        {$helpDiv}
    </div>
</div>
HTML;
            case $input instanceof TextInput:
                $disabled = $input->isDisabled() ? "disabled" : "";
                [$tooltipText, $tooltipTrigger] = $this->bakeTooltip($input->getTooltip());
                $helpDiv = $this->bakeHelpDiv($input->getHelp());
                return <<<HTML
<div class="row ls-space margin top-10">
    <div class="form-group col-xs-12">
        <label class="col-sm-12 control-label">{$input->getLabel()}</label>
        <div class="col-sm-12">
            <input {$disabled} {$tooltipTrigger} {$tooltipText} class="form-control" type="text" name="{$input->getName()}" value="{$input->getValue()}">
        </div>
        {$helpDiv}
    </div>
</div>
HTML;
            case $input instanceof ButtonSwitchInput:
                $helpDiv = $this->bakeHelpDiv($input->getHelp());
                [$tooltipText, $tooltipTrigger] = $this->bakeTooltip($input->getTooltip());
                $widget = App()->getController()->widget(
                    'yiiwheels.widgets.switch.WhSwitch',
                    [
                        'name'        => $input->getName(),
                        'id'          => $input->getName(),
                        'value'       => $input->getValue(),
                        'onLabel'     => gT('On'),
                        'offLabel'    => gT('Off'),
                        'htmlOptions' => [
                            'disabled' => $input->isDisabled(),
                            //'data-bs-toggle' => $input->getTooltip() ? 'tooltip' : null,
                            //'title' => $input->getTooltip()
                        ]
                    ],
                    true
                );
                return <<<HTML
<div class="row ls-space margin top-10">
    <div class="form-group col-xs-12">
        <label class="col-sm-12 control-label" for="{$input->getName()}">{$input->getLabel()}</label>
        <div {$tooltipTrigger} {$tooltipText} class="col-sm-12">{$widget}</div>
        {$helpDiv}
    </div>
</div>
HTML;
            default:
                return parent::run($input);
        }
    }

    /** @param ?string $help */
    protected function bakeHelpDiv($help): string
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
