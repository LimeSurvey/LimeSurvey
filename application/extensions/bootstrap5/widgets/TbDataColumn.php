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
     * Renders the header cell.
     * Adds the aria-sort attribute to the header cell of the currently sorted column,
     * so assistive technologies can announce the sort state (WCAG 4.1.2).
     *
     * @return void
     */
    public function renderHeaderCell()
    {
        $ariaSort = $this->getAriaSortValue();
        if ($ariaSort !== null) {
            $this->headerHtmlOptions['aria-sort'] = $ariaSort;
        } else {
            unset($this->headerHtmlOptions['aria-sort']);
        }
        parent::renderHeaderCell();
    }

    /**
     * Returns the aria-sort value for this column's header cell.
     *
     * @return string|null 'ascending' or 'descending' if the grid is currently sorted by this column, null otherwise
     */
    protected function getAriaSortValue()
    {
        if (!$this->grid->enableSorting || !$this->sortable || $this->name === null) {
            return null;
        }
        $sort = $this->grid->dataProvider->getSort();
        if ($sort === false || $sort->resolveAttribute($this->name) === false) {
            return null;
        }
        $isDescending = $sort->getDirection($this->name);
        if ($isDescending === null) {
            return null;
        }
        return $isDescending ? 'descending' : 'ascending';
    }

    /**
     * Renders the header cell content.
     * This method will render a link that can trigger the sorting if the column is sortable.
     *
     * @return void
     */
    protected function renderHeaderCellContent()
    {
        if ($this->grid->enableSorting && $this->sortable && $this->name !== null) {
            $sort = $this->grid->dataProvider->getSort();
            $label = isset($this->header) ? $this->header : $sort->resolveLabel($this->name);

            if ($sort->resolveAttribute($this->name) !== false) {
                $isAscending = $sort->getDirection($this->name);
                if ($isAscending) {
                    $label .= '<i class="ri-sort-asc ms-2" aria-hidden="true"></i>';
                }
                if (!$isAscending) {
                    $label .= '<i class="ri-sort-desc ms-2" aria-hidden="true"></i>';
                }
            }

            echo $sort->link($this->name, $label, [
                'class'               => 'sort-link',
                'role'                => 'button',
                'data-sort-attribute' => $this->name,
            ]);
        } elseif ($this->name !== null && $this->header === null) {
            if ($this->grid->dataProvider instanceof CActiveDataProvider) {
                echo CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
            } else {
                echo CHtml::encode($this->name);
            }
        } else {
            parent::renderHeaderCellContent();
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
            if (
                $this->filter !== false && $this->grid->filter !== null && $this->name !== null && strpos(
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
                $this->applyDefaultFilterAriaLabel($filterInputOptions);
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

    /**
     * Adds an aria-label to filter controls when none is provided, for accessibility (e.g. WCAG / axe).
     *
     * @param array $filterInputOptions
     * @return void
     */
    protected function applyDefaultFilterAriaLabel(array &$filterInputOptions)
    {
        if (!empty($filterInputOptions['aria-label']) || !empty($filterInputOptions['aria-labelledby'])) {
            return;
        }
        if (!($this->grid->filter instanceof CModel) || $this->name === null) {
            return;
        }
        $filterInputOptions['aria-label'] = $this->grid->filter->getAttributeLabel($this->name);
    }
}
