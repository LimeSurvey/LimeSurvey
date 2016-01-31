<?php
/**
 * TbPager class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap pager widget.
 * http://twitter.github.com/bootstrap/components.html#pagination
 */
class TbPager extends CBasePager
{
    /**
     * @var string the pager size.
     */
    public $size;
    /**
     * @var integer maximum number of page buttons that can be displayed.
     */
    public $maxButtonCount = 5;
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
     * @var boolean whether the "first" and "last" buttons should be hidden.
     * Defaults to false.
     */
    public $hideFirstAndLast = false;
    /**
     * @var array HTML attributes for the pager container tag.
     */
    public $htmlOptions = array();

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $this->attachBehavior('TbWidget', new TbWidget);
        $this->resolveId();
        if (isset($this->size)) {
            TbArray::defaultValue('size', $this->size, $this->htmlOptions);
        }
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $links = $this->createPageLinks();
        if (!empty($links)) {
            echo TbHtml::pagination($links, $this->htmlOptions);
        }
    }

    /**
     * Creates the page buttons.
     * @return array a list of page buttons (in HTML code).
     */
    protected function createPageLinks()
    {
        if (($pageCount = $this->getPageCount()) <= 1) {
            return array();
        }

        list($beginPage, $endPage) = $this->getPageRange();

        $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
        $links = array();

        // first page
        if (!$this->hideFirstAndLast) {
            $links[] = $this->createPageLink($this->firstPageLabel, 0, $currentPage <= 0, false);
        }

        // prev page
        if (($page = $currentPage - 1) < 0) {
            $page = 0;
        }

        $links[] = $this->createPageLink($this->prevPageLabel, $page, $currentPage <= 0, false);

        // internal pages
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $links[] = $this->createPageLink($i + 1, $i, false, $i == $currentPage);
        }

        // next page
        if (($page = $currentPage + 1) >= $pageCount - 1) {
            $page = $pageCount - 1;
        }

        $links[] = $this->createPageLink($this->nextPageLabel, $page, $currentPage >= $pageCount - 1, false);

        // last page
        if (!$this->hideFirstAndLast) {
            $links[] = $this->createPageLink(
                $this->lastPageLabel,
                $pageCount - 1,
                $currentPage >= $pageCount - 1,
                false
            );
        }

        return $links;
    }

    /**
     * Creates a page link.
     * @param string $label the link label text.
     * @param integer $page the page number.
     * @param boolean $visible whether the link is disabled.
     * @param boolean $active whether the link is active.
     * @return string the generated link.
     */
    protected function createPageLink($label, $page, $disabled, $active)
    {
        return array(
            'label' => $label,
            'url' => $this->createPageUrl($page),
            'disabled' => $disabled,
            'active' => $active,
        );
    }

    /**
     * @return array the begin and end pages that need to be displayed.
     */
    protected function getPageRange()
    {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getPageCount();
        $beginPage = max(0, $currentPage - (int)($this->maxButtonCount / 2));
        if (($endPage = $beginPage + $this->maxButtonCount - 1) >= $pageCount) {
            $endPage = $pageCount - 1;
            $beginPage = max(0, $endPage - $this->maxButtonCount + 1);
        }
        return array($beginPage, $endPage);
    }
}
