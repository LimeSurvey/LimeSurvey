<?php
/**
 * TbThumbnails class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

/**
 * Bootstrap thumbnails widget.
 * http://twitter.github.com/bootstrap/components.html#thumbnails
 */
class TbThumbnails extends TbListView
{
    /**
     * @var mixed a PHP expression that is evaluated for every item and whose result is used
     * as the URL for the thumbnail.
     */
    public $url;
    /**
     * @var integer the number of grid columns that the thumbnails spans over.
     */
    public $span;

    /**
     * Initializes the widget
     */
    public function init()
    {
        parent::init();

        if (isset($this->itemsCssClass)) {
            TbHtml::addCssClass($this->itemsCssClass, $this->htmlOptions);
        }
    }

    /**
     * Renders the data items for the view.
     * Each item is corresponding to a single data model instance.
     */
    public function renderItems()
    {
        $thumbnails = array();
        $data = $this->dataProvider->getData();

        if (!empty($data)) {
            $owner = $this->getOwner();
            $render = $owner instanceof CController ? 'renderPartial' : 'render';
            foreach ($data as $i => $row) {
                $thumbnail = array();
                $d = $this->viewData;
                $d['index'] = $i;
                $d['data'] = $row;
                $d['widget'] = $this;
                $thumbnail['caption'] = $owner->$render($this->itemView, $d, true);
                if (isset($this->url)) {
                    $thumbnail['url'] = $this->evaluateExpression($this->url, array('data' => $row));
                }
                if (isset($this->span)) {
                    $thumbnail['span'] = $this->span;
                }
                $thumbnails[] = $thumbnail;
            }
            echo TbHtml::thumbnails($thumbnails, $this->htmlOptions);
        } else {
            $this->renderEmptyText();
        }
    }
}
