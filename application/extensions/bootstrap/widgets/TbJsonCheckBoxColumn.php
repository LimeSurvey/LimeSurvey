<?php
/**
 * TbJsonCheckBoxColumn class
 * Works in conjunction with TbJsonGridView. Renders HTML or returns JSON containing checkbox
 * according to the request to the Grid.
 *
 * @author: Mikhail Kuklin <mikhail@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
class TbJsonCheckBoxColumn extends CCheckBoxColumn
{
    /**
     * Renders the header cell.
     */
    public function renderHeaderCell()
    {
        if ($this->grid->json)
        {
            $this->headerHtmlOptions['id']=$this->id;
            if ($this->grid->json)
            {
                return CMap::mergeArray(
                    $this->headerHtmlOptions,
                    array('content' => $this->renderHeaderCellContent())
                );
            }
        }
        parent::renderHeaderCell();
    }

    /**
     * Renders the header cell content.
     * This method will render a checkbox in the header when {@link selectableRows} is greater than 1
     * or in case {@link selectableRows} is null when {@link CGridView::selectableRows} is greater than 1.
     */
    protected function renderHeaderCellContent()
    {
        if ($this->grid->json)
        {
            if(trim($this->headerTemplate)==='')
            {
                return $this->grid->blankDisplay;
            }

            $item = '';
            if($this->selectableRows===null && $this->grid->selectableRows>1)
                $item = CHtml::checkBox($this->id.'_all',false,array('class'=>'select-on-check-all'));
            else if($this->selectableRows>1)
                $item = CHtml::checkBox($this->id.'_all',false);
            else
            {
                ob_start();
                parent::renderHeaderCellContent();
                $item = ob_get_clean();
            }

            return strtr($this->headerTemplate,array(
                '{item}'=>$item,
            ));
        }
        parent::renderHeaderCellContent();
    }

    /**
     * Renders|returns the data cell.
     * @param int $row
     * @return array|void
     */
    public function renderDataCell($row)
    {
        $data = $this->grid->dataProvider->data[$row];
        $options = $this->htmlOptions;
        if ($this->cssClassExpression !== null)
        {
            $class = $this->evaluateExpression($this->cssClassExpression, array('row' => $row, 'data' => $data));
            if (!empty($class))
            {
                if (isset($options['class']))
                    $options['class'] .= ' ' . $class;
                else
                    $options['class'] = $class;
            }
        }

        if ($this->grid->json)
        {
            return CMap::mergeArray(
                $options,
                array('content' => $this->renderDataCellContent($row, $data))
            );
        }

        parent::renderDataCell($row);
    }

    /**
     * Renders|returns the data cell content
     * @param int $row
     * @return array|void
     */
    protected function renderDataCellContent($row, $data)
    {
        ob_start();
        parent::renderDataCellContent($row, $data);
        $html = ob_get_contents();
        ob_end_clean();

        if ($this->grid->json)
            return $html;

        echo $html;
    }
}
