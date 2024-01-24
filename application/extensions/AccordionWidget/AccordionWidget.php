<?php

class AccordionWidget extends CWidget
{
    public $id = null;
    public $items = [];
    public $class = null;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->render('accordion', [
            'id' => $this->id ?? 'accordion_' . bin2hex(random_bytes(2)),
            'items' => $this->normaliseItems($this->items),
            'class' => $this->class
        ]);
    }

    public function normaliseItems($items)
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'] ?? $this->id . '_item_' . bin2hex(random_bytes(2)),
                'title' => $item['title'] ?? null,
                'content' => $item['content'] ?? null,
                'open' => $item['open'] ?? true,
                'style' => $item['style'] ?? ''
            ];
        }, $items);
    }
}
