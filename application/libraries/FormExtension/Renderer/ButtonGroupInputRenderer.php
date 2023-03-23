<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;

class ButtonGroupInputRenderer implements RendererInterface
{
    /**
     * @param InputInterface $input
     */
    public function render(InputInterface $input): string
    {
        $attributes = $input->getAttributes();
        return App()->getController()->widget(
            'yiiwheels.widgets.buttongroup.WhButtonGroup',
            [
                'name'          => $input->getName(),
                'id'            => !empty($attributes['id']) ? $attributes['id'] : '',
                'value'         => $input->getValue(),
                'selectOptions' => $input->getOptions(),
                'htmlOptions'   => [
                    'disabled'  => !empty($attributes['disabled']),
                    //'data-toggle' => $input->getTooltip() ? 'tooltip' : null,
                    //'title' => $input->getTooltip()
                ]
            ],
            true
        );
    }
}
