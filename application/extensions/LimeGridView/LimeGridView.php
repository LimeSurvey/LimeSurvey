<?php
/**
 * LimeGridView class file.
 * Needed to set Scriptfile and script to good position
 */

Yii::import('bootstrap.widgets.TbGridView');

/**
 * LimeSurvey Zii grid view.
 */
class LimeGridView extends TbGridView
{
	/**
	 * @inheritDoc
	 * But set script position to POS_PREBEGIN and POS_POSTSCRIPT
	 */
	public function registerClientScript()
	{
		$id=$this->getId();

		if($this->ajaxUpdate===false)
			$ajaxUpdate=false;
		else
			$ajaxUpdate=array_unique(preg_split('/\s*,\s*/',$this->ajaxUpdate.','.$id,-1,PREG_SPLIT_NO_EMPTY));
		$options=array(
			'ajaxUpdate'=>$ajaxUpdate,
			'ajaxVar'=>$this->ajaxVar,
			'pagerClass'=>$this->pagerCssClass,
			'loadingClass'=>$this->loadingCssClass,
			'filterClass'=>$this->filterCssClass,
			'tableClass'=>$this->itemsCssClass,
			'selectableRows'=>$this->selectableRows,
			'enableHistory'=>$this->enableHistory,
			'updateSelector'=>$this->updateSelector,
			'filterSelector'=>$this->filterSelector
		);
		if($this->ajaxUrl!==null)
			$options['url']=CHtml::normalizeUrl($this->ajaxUrl);
		if($this->ajaxType!==null) {
			$options['ajaxType']=strtoupper($this->ajaxType);
			$request=Yii::app()->getRequest();
			if ($options['ajaxType']=='POST' && $request->enableCsrfValidation) {
				$options['csrfTokenName']=$request->csrfTokenName;
				$options['csrfToken']=$request->getCsrfToken();
			}
		}
		if($this->enablePagination)
			$options['pageVar']=$this->dataProvider->getPagination()->pageVar;
		foreach(array('beforeAjaxUpdate', 'afterAjaxUpdate', 'ajaxUpdateError', 'selectionChanged') as $event)
		{
			if($this->$event!==null)
			{
				if($this->$event instanceof CJavaScriptExpression)
					$options[$event]=$this->$event;
				else
					$options[$event]=new CJavaScriptExpression($this->$event);
			}
		}

		$options=CJavaScript::encode($options);
		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');
		$cs->registerCoreScript('bbq');
		if($this->enableHistory)
			$cs->registerCoreScript('history');
		$cs->registerScriptFile($this->baseScriptUrl.'/jquery.yiigridview.js',LSYii_ClientScript::POS_PREBEGIN);
		$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#$id').yiiGridView($options);",LSYii_ClientScript::POS_POSTSCRIPT);
	}

}
