<?php

class AccordianWidget extends CWidget
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
        $this->render('accordian', [
            'id' => $this->id ?? 'accordian_' . bin2hex(random_bytes(2)),
            'items' => $this->normaliseItems($this->items)
        ]);
    }

    public function normaliseItems($items)
    {
        return array_map(function($item){
            return [
                'id' => $item['id'] ?? $this->id . '_item_' . bin2hex(random_bytes(2)),
                'title' => $item['title'] ?? null,
                'content' => $item['content'] ?? null,
                'open' => $item['open'] ?? false,
                'style' => $item['style'] ?? '',
            ];
        },  $items);
    }
}
