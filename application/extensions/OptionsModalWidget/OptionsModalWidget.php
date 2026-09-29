<?php

class OptionsModalWidget extends CWidget
{
    public $id = null;
    public $modalTitle = '';
    public $options = [];

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->render('modal', [
            'id' => $this->getModalId(),
            'options' => $this->normaliseOptions($this->options),
            'modalTitle' => $this->modalTitle ?? gT("Select an option")
        ]);

        $this->registerScript();
    }

    public function normaliseOptions($options)
    {
        return array_map(function ($option) {
            return [
                'key' => $option['key'] ?? 'option_' . bin2hex(random_bytes(2)),
                'linkClass' => $option['linkClass'] ?? '',
                'href' => $option['href'] ?? '#',
                'text' => $option['text'] ?? '',
                'target' => $option['target'] ?? null,
            ];
        }, $options);
    }

    public function getModalId()
    {
        if (!isset($this->id)) {
            $this->id = 'modal_' . bin2hex(random_bytes(2));
        }

        return $this->id;
    }

    public function registerScript()
    {
        $modalId = $this->getModalId();
        Yii::app()->clientScript->registerScript(
            'moveModalToBody_' . $modalId,
            '$("#' . $modalId . '").appendTo("body");',
            CClientScript::POS_END
        );
    }
}
