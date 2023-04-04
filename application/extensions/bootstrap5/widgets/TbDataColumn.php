<?php
/**
 * TbDataColumn class file.
 * @author Antonio Ramirez <ramirez.cobos@gmail.com>
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Bootstrap grid data column.
 */
class TbDataColumn extends CDataColumn
{
    /**
     * @var array HTML options for filter input
     * @link {TbDataColumn::renderFilterCellContent()}
     */
    public $filterInputOptions;

    /**
     * Renders the header cell content.
     * This method will render a link that can trigger the sorting if the column is sortable.
     */
    protected function renderHeaderCellContent()
    {
        if ($this->grid->enableSorting && $this->sortable && $this->name !== null) {
            $sort = $this->grid->dataProvider->getSort();
            $label = isset($this->header) ? $this->header : $sort->resolveLabel($this->name);

            if ($sort->resolveAttribute($this->name) !== false) {
                $isAscending = $sort->getDirection($this->name);
                if ($isAscending) {
                    $label .= '<i class="ri-sort-asc ms-2"></i>';
                }
                if (!$isAscending) {
                    $label .= '<i class="ri-sort-desc ms-2"></i>';
                }
            }

            echo $sort->link($this->name, $label, array('class' => 'sort-link'));
        } else {
            if ($this->name !== null && $this->header === null) {
                if ($this->grid->dataProvider instanceof CActiveDataProvider) {
                    echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
                } else {
                    echo CHtml::encode($this->name);
                }
            } else {
                parent::renderHeaderCellContent();
            }
        }
    }

    /**
     * Renders the filter cell.
     */
    public function renderFilterCell()
    {
        echo CHtml::openTag('td', $this->filterHtmlOptions);
        echo '<div class="filter-container">';
        $this->renderFilterCellContent();
        echo '</div>';
        echo CHtml::closeTag('td');
    }

    /**
     * Renders the filter cell content. Here we can provide HTML options for actual filter input
     */
    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            echo $this->filter;
        } else {
            if ($this->filter !== false && $this->grid->filter !== null && $this->name !== null && strpos(
                    (string) $this->name,
                    '.'
                ) === false
            ) {
                if ($this->filterInputOptions) {
                    $filterInputOptions = $this->filterInputOptions;
                    if (empty($filterInputOptions['id'])) {
                        $filterInputOptions['id'] = false;
                    }
                } else {
                    $filterInputOptions = array();
                }
                if (is_array($this->filter)) {
                    $filterInputOptions['class'] = ' form-select ';
                    $filterInputOptions['prompt'] = '';
                    echo TbHtml::activeDropDownList(
                        $this->grid->filter,
                        $this->name,
                        $this->filter,
                        $filterInputOptions
                    );
                } else {
                    if ($this->filter === null) {
                        echo TbHtml::activeTextField($this->grid->filter, $this->name, $filterInputOptions);
                    }
                }
            } else {
                parent::renderFilterCellContent();
            }
        }
    }
}
