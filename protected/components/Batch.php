<?php
namespace ls\components;

use Closure;

class Batch
{

    /**
     * @var Closure
     */
    protected $callback;

    protected $defaultCategory;
    public $batchSize;
    public $commitCount = 0;
    public $recordCount = 0;
    protected $data = [];

    public function __construct(\Closure $callback, $batchSize = 5000, $defaultCategory = 'default')
    {
        $this->callback = $callback;
        $this->batchSize = $batchSize;
        $this->defaultCategory = $defaultCategory;
    }


    public function add($elements, $category = null)
    {
        if (!empty($elements) && is_scalar(reset($elements))) {
            $elements = [$elements];
        }
        if (!isset($category)) {
            $category = $this->defaultCategory;
        }
        foreach ($elements as $element) {
            $this->data[$category][] = ($element instanceof \CActiveRecord) ? $element->attributes : $element;


            if (count($this->data[$category]) > $this->batchSize) {
                $this->commitCategory($category);
            }


        }
    }

    public function commitCategory($category)
    {

        $callback = $this->callback;
        $callback($this->data[$category], $category);
        $this->commitCount++;
        $this->recordCount += count($this->data[$category]);
        unset($this->data[$category]);
    }

    public function commit()
    {
        foreach ($this->data as $key => $items) {
            $this->commitCategory($key);
        }
    }

    public function __destruct()
    {
        $this->commit();
    }
}