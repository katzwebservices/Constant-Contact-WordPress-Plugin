<?php

/**
 * Wrapper for ivaynberg jQuery select2 (https://github.com/ivaynberg/select2)
 * 
 * @author Anggiajuang Patria <anggiaj@gmail.com>
 * @link http://git.io/Mg_a-w
 * @license http://www.opensource.org/licenses/apache2.0.php
 */
class ESelect2 extends CInputWidget
{

    /**
     * @var array select2 options
     */
    public $options = array();

    /**
     * @var array CHtml::dropDownList $data param
     */
    public $data = array();

    /**
     * @var string html element selector
     */
    public $selector;

    /**
     * @var array javascript event handlers
     */
    public $events = array();

	/**
	 * @var boolean should the items of a multiselect list be sortable using jQuery UI
	 */
	public $sortable = false;
    
    protected $defaultOptions = array();

    public function init()
    {
        $this->defaultOptions = array(
            'formatNoMatches' => 'js:s2helper.noMatches',
            'formatInputTooShort' => 'js:s2helper.inputTooShort',
			'formatInputTooLong' => 'js:s2helper.inputTooLong',
            'formatSelectionTooBig' => 'js:s2helper.selectionTooBig',
            'formatLoadMore' => 'js:s2helper.loadMore',
            'formatSearching' => 'js:s2helper.searching',
        );
    }

    public function run()
    {
        if ($this->selector == null) {
            list($this->name, $this->id) = $this->resolveNameId();
            $this->selector = '#' . $this->id;

            if (isset($this->htmlOptions['placeholder']))
                $this->options['placeholder'] = $this->htmlOptions['placeholder'];

            if (!isset($this->htmlOptions['multiple'])) {
                $data = array();
                if (isset($this->options['placeholder']))
                    $data[''] = '';
                $this->data = $data + $this->data;
            }

            if ($this->hasModel()) {
                if (isset($this->options['ajax'])) {
                    if (!isset($this->htmlOptions['value'])) {
                        $value = CHtml::resolveValue($this->model, $this->attribute);
                        $values = array();
                        if ($this->model instanceof CActiveRecord) {
                            $relations = $this->model->relations();
                            if (isset($relations[$this->attribute])
                                && $relations[$this->attribute][0] != CActiveRecord::BELONGS_TO
                                && $relations[$this->attribute][0] != CActiveRecord::HAS_ONE)
                            {
                                foreach($value as $object) {
                                    $values[] = $object->getPrimaryKey();
                                }
                                $this->htmlOptions['value'] = implode(',', $values);
                            }
                        } else if (is_array($value)) {
                            foreach($value as $object) {
                                $values[] = is_object($object) ? $object->__toString() : $object;
                            }
                            $this->htmlOptions['value'] = implode(',', $values);
                        }
                    }
                    echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
                } else {
                    echo CHtml::activeDropDownList($this->model,$this->attribute,$this->data,$this->htmlOptions);
                }
            } else {
                $this->htmlOptions['id'] = $this->id;
                if (isset($this->options['ajax'])) {
                    echo CHtml::textField($this->name,$this->value,$this->htmlOptions);
                } else {
                    echo CHtml::dropDownList($this->name,$this->value,$this->data,$this->htmlOptions);
                }
            }  
        }

        $bu = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/');
        $cs = Yii::app()->clientScript;
        $cs->registerCssFile($bu . '/select2.css');

        if (YII_DEBUG)
            $cs->registerScriptFile($bu . '/select2.js');
        else
            $cs->registerScriptFile($bu . '/select2.min.js');

		if ($this->sortable) {
			$cs->registerCoreScript('jquery.ui');
		}

        $options = CJavaScript::encode(CMap::mergeArray($this->defaultOptions, $this->options));
        ob_start();
        echo "jQuery('{$this->selector}').select2({$options})";
        foreach ($this->events as $event => $handler)
            echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";
		echo ';';
		if ($this->sortable) {
			echo <<<JavaScript
jQuery('{$this->selector}').select2("container").find("ul.select2-choices").sortable({
	containment: 'parent',
	start: function() { jQuery('{$this->selector}').select2("onSortStart"); },
	update: function() { jQuery('{$this->selector}').select2("onSortEnd"); }
});
JavaScript;
		}

        $cs->registerScript(__CLASS__ . '#' . $this->id, ob_get_clean());
        $strings = array(
            'noMatches' => Yii::t('ESelect2.select2','No matches found'),
            'inputTooShort' => Yii::t('ESelect2.select2','Please enter {chars} more characters', array('{chars}'=>'"+(min-input.length)+"')),
			'inputTooLong' => Yii::t('ESelect2.select2','Please enter {chars} less characters', array('{chars}'=>'"+(input.length-max)+"')),
            'selectionTooBig' => Yii::t('ESelect2.select2','You can only select {count} items', array('{count}'=>'"+limit+"')),
            'loadMore' => Yii::t('ESelect2.select2','Loading more results...'),
            'searching' => Yii::t('ESelect2.select2','Searching...'),
        );
        $script = <<<JavaScript
(function( s2helper, $, undefined ) {
    s2helper.noMatches = function(){return "{$strings['noMatches']}";}
    s2helper.inputTooShort = function(input,min){return "{$strings['inputTooShort']}";}
    s2helper.inputTooLong = function(input,max){return "{$strings['inputTooShort']}";}
    s2helper.selectionTooBig = function(limit){return "{$strings['selectionTooBig']}";}
    s2helper.loadMore = function(pageNumber){return "{$strings['loadMore']}";}
    s2helper.searching = function(){return "{$strings['searching']}";}
}( window.s2helper = window.s2helper || {}, jQuery ));
JavaScript;
        $cs->registerScript(__CLASS__, $script, CClientScript::POS_END);
    }

}
