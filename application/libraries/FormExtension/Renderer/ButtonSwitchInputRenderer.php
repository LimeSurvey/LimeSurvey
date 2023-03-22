<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;

class ButtonSwitchInputRenderer implements RendererInterface
{
    /**
     * @param InputInterface $input
     */
    public function render(InputInterface $input): string
    {
        return App()->getController()->widget(
            'yiiwheels.widgets.switch.WhSwitch',
            [
                'name'        => $input->getName(),
                'id'          => $input->getId() ?? $input->getName(),
                'value'       => $input->getValue(),
                'onLabel'     => gT('On'),
                'offLabel'    => gT('Off'),
                'htmlOptions' => [
                    'disabled' => $input->isDisabled(),
                    //'data-toggle' => $input->getTooltip() ? 'tooltip' : null,
                    //'title' => $input->getTooltip()
                ]
            ],
            true
        );
    }
}
