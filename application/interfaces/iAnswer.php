<?php
namespace ls\interfaces;

/**
 * Objects implementing this interface can represent answers for fixed choice questions.
 * @package ls\interfaces
 */
interface iAnswer {
    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getCode();
}