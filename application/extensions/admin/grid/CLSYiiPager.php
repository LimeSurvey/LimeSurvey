<?php

class CLSYiiPager extends CLinkPager
{
    /**
     * @var string the text label for the next page button.
     */
    public $nextPageLabel = '&rsaquo;';
    /**
     * @var string the text label for the previous page button.
     */
    public $prevPageLabel = '&lsaquo;';
    /**
     * @var string the text label for the first page button.
     */
    public $firstPageLabel = '&laquo;';
    /**
     * @var string the text label for the last page button.
     */
    public $lastPageLabel = '&raquo;';

    /**
     * @return void
     */
    public function init()
    {
        //$this->cssFile = false;
        $this->header = '';
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->getId();
        }
        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = 'pagination';
        }
        $this->maxButtonCount = 5;
        $this->hiddenPageCssClass = 'disabled';
    }

    /**
     * Creates the page buttons.
     * @return array a list of page buttons (in HTML code).
     */
    protected function createPageButtons()
    {
        if (($pageCount = $this->getPageCount()) <= 1) {
            return array();
        }

        list($beginPage, $endPage) = $this->getPageRange();
        $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
        $buttons = array();

        // first page
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->createPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }
        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->createPageButton($this->prevPageLabel, $page, $this->previousPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->createPageButton($i + 1, $i, '', false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->createPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        // last page
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        return $buttons;
    }

    /**
     * Creates a page button
     * @param string $label the text label for the button
     * @param integer $page the page number
     * @param string $class the CSS class for the page button.
     * @param boolean $hidden whether this page button is visible
     * @param boolean $selected whether this page button is selected
     * @return string the generated button
     */
    protected function createPageButton($label, $page, $class, $hidden, $selected)
    {
        $ariaLabel = '';
        if (is_numeric($label)) {
            if ($selected) {
                $ariaLabel = sprintf(gT('Page %s, current page'), $label);
            } else {
                $ariaLabel = sprintf(gT('Go to page %s'), $label);
            }
        } elseif ($label === $this->firstPageLabel) {
            $ariaLabel = $selected ? gT('First page, current page') : gT('Go to first page');
        } elseif ($label === $this->prevPageLabel) {
            $ariaLabel = $selected ? gT('Previous page, current page') : gT('Go to previous page');
        } elseif ($label === $this->nextPageLabel) {
            $ariaLabel = $selected ? gT('Next page, current page') : gT('Go to next page');
        } elseif ($label === $this->lastPageLabel) {
            $ariaLabel = $selected ? gT('Last page, current page') : gT('Go to last page');
        }

        if ($hidden || $selected) {
            $class .= ' ' . ($hidden ? $this->hiddenPageCssClass : 'active');
            $attrs = ['class' => 'page-link'];
            if ($selected) {
                $attrs['aria-current'] = 'page';
            }
            if ($hidden) {
                $attrs['aria-disabled'] = 'true';
                $attrs['tabindex'] = '-1';
            }
            if ($ariaLabel !== '') {
                $attrs['aria-label'] = $ariaLabel;
            }
            return '<li class="page-item ' . $class . '">' . CHtml::tag('span', $attrs, $label) . '</li>';
        } else {
            $linkOptions = ['class' => 'page-link'];
            if ($ariaLabel !== '') {
                $linkOptions['aria-label'] = $ariaLabel;
            }
            return '<li class="page-item ' . $class . '">' . CHtml::link($label, $this->createPageUrl($page), $linkOptions) . '</li>';
        }
    }
} 

