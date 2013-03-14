<?php
/**
 * TbPager class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap pager.
 * @see http://twitter.github.com/bootstrap/components.html#pagination
 */
class TbPager extends CLinkPager
{
	// Pager alignments.
	const ALIGNMENT_CENTER = 'centered';
	const ALIGNMENT_RIGHT = 'right';

	/**
	 * @var string the pager alignment. Valid values are 'centered' and 'right'.
	 */
	public $alignment;
	/**
	 * @var string the text shown before page buttons.
	 * Defaults to an empty string, meaning that no header will be displayed.
	 */
	public $header = '';
	/**
	 * @var string the URL of the CSS file used by this pager.
	 * Defaults to false, meaning that no CSS will be included.
	 */
	public $cssFile = false;
	/**
	 * @var boolean whether to display the first and last items.
	 */
	public $displayFirstAndLast = false;

	/**
	 * Initializes the pager by setting some default property values.
	 */
	public function init()
	{
		if ($this->nextPageLabel === null)
			$this->nextPageLabel = '&rarr;';

		if ($this->prevPageLabel === null)
			$this->prevPageLabel = '&larr;';

		$classes = array();

		$validAlignments = array(self::ALIGNMENT_CENTER, self::ALIGNMENT_RIGHT);

		if (in_array($this->alignment, $validAlignments))
			$classes[] = 'pagination-'.$this->alignment;

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] = ' '.$classes;
			else
				$this->htmlOptions['class'] = $classes;
		}

		parent::init();
	}

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons()
	{
		if (($pageCount = $this->getPageCount()) <= 1)
			return array();

		list ($beginPage, $endPage) = $this->getPageRange();

		$currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()

		$buttons = array();

		// first page
		if ($this->displayFirstAndLast)
			$buttons[] = $this->createPageButton($this->firstPageLabel, 0, 'first', $currentPage <= 0, false);

		// prev page
		if (($page = $currentPage - 1) < 0)
			$page = 0;

		$buttons[] = $this->createPageButton($this->prevPageLabel, $page, 'previous', $currentPage <= 0, false);

		// internal pages
		for ($i = $beginPage; $i <= $endPage; ++$i)
			$buttons[] = $this->createPageButton($i + 1, $i, '', false, $i == $currentPage);

		// next page
		if (($page = $currentPage+1) >= $pageCount-1)
			$page = $pageCount-1;

		$buttons[] = $this->createPageButton($this->nextPageLabel, $page, 'next', $currentPage >= ($pageCount - 1), false);

		// last page
		if ($this->displayFirstAndLast)
			$buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount - 1, 'last', $currentPage >= ($pageCount - 1), false);

		return $buttons;
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * @param string $label the text label for the button
	 * @param integer $page the page number
	 * @param string $class the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean $hidden whether this page button is visible
	 * @param boolean $selected whether this page button is selected
	 * @return string the generated button
	 */
	protected function createPageButton($label, $page, $class, $hidden, $selected)
	{
		if ($hidden || $selected)
			$class .= ' '.($hidden ? 'disabled' : 'active');

		return CHtml::tag('li', array('class'=>$class), CHtml::link($label, $this->createPageUrl($page)));
	}
}
