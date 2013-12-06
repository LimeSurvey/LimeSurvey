<?php
/**
 * TbTabView class file.
 *
 * Use TbTabView as replacement for Yii CTabView
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright Copyright &copy; Joe Blocher 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbTabs');

class TbTabView extends TbTabs
{

    /**
     * Additional data submitted to the views
     *
     * @var array
     */
    public $viewData;

    /**
     * Override from TbTabs
     *
     * @param array $tabs the tab configuration
     * @param array $panes a reference to the panes array
     * @param integer $i the current index
     * @return array the items
     */
    protected function normalizeTabs($tabs, &$panes, &$i = 0)
    {
        $id = $this->getId();
        $items = array();

       //---------------- new -------------------
        //Check if has an active item
        $hasActiveItem = false;
        foreach ($tabs as $tab)
        {
            $hasActiveItem = isset($tab['active']) ? $tab['active'] : false;
            if($hasActiveItem)
                break;
        }
        //---------------- end new -------------------

        foreach ($tabs as $tab)
        {
            $item = $tab;

            if (isset($item['visible']) && $item['visible'] === false)
                continue;

            //---------------- new -------------------
            //check first active
            if(!$hasActiveItem && $i == 0)
                $item['active'] = true;

            //title -> label
            if (isset($item['title']))
            {
                if(!isset($item['label']))
                  $item['label'] = $item['title'];
                unset($item['title']);
            }
            //------   end new ----------------

            if (!isset($item['itemOptions']))
                $item['itemOptions'] = array();

            $item['linkOptions']['data-toggle'] = 'tab';

            if (isset($tab['items']))
                $item['items'] = $this->normalizeTabs($item['items'], $panes, $i);
            else
            {
                if (!isset($item['id']))
                    $item['id'] = $id.'_tab_'.($i + 1);

                $item['url'] = '#'.$item['id'];

                //if (!isset($item['content'])) removed
                //	$item['content'] = '';

                //--------------- new ---------------
                if (!isset($item['content']))
                {
                    if (isset($item['view']))
                    {
                        if (isset($item['data']))
                        {
                            if (is_array($this->viewData))
                                $data = array_merge($this->viewData, $item['data']);
                            else
                                $data = $item['data'];

                            unset($item['data']);
                        } else
                            $data = $this->viewData;

                        $item['content'] = $this->getController()->renderPartial($item['view'], $data, true);

                        unset($item['view']);
                    }
                    else
                        $item['content'] = '';
                }
                //--------------- end new ---------------

                $content = $item['content'];
                unset($item['content']);

                if (!isset($item['paneOptions']))
                    $item['paneOptions'] = array();

                $paneOptions = $item['paneOptions'];
                unset($item['paneOptions']);

                $paneOptions['id'] = $item['id'];

                $classes = array('tab-pane fade');

                if (isset($item['active']) && $item['active'])
                    $classes[] = 'active in';

                $classes = implode(' ', $classes);
                if (isset($paneOptions['class']))
                    $paneOptions['class'] .= ' '.$classes;
                else
                    $paneOptions['class'] = $classes;

                $panes[] = CHtml::tag('div', $paneOptions, $content);

                $i++; // increment the tab-index
            }

            $items[] = $item;
        }

        return $items;
    }

}