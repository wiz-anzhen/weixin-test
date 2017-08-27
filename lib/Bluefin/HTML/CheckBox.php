<?php

namespace Bluefin\HTML;

class CheckBox extends SimpleComponent
{
    public $collection;
    public $value;

    protected $_buttonType;

    public function __construct(array $attributes = null)
    {
        log_debug("attributes:",$attributes);
        parent::__construct($attributes);
        $this->value = array_try_get($this->attributes, 'value',0, true);
        $this->addFirstClass('checkbox');
    }

    protected function _commitProperties()
    {
        parent::_commitProperties();
    }

    protected function _renderContent()
    {
        $attributes = $this->attributes;
        $onClick         = array_try_get($attributes, 'onClick', null, true);
        if(empty($onClick))
        {
            $onClick         = array_try_get($attributes, 'onclick', null, true);
        }
        log_debug("attributes=================",$attributes);

        $id         = array_try_get($attributes, 'id', null, true);
        $name       = array_try_get($attributes, 'name', '', true);
        $attr       = $this->renderAttributes($attributes);

        // 隐藏的input保存完整的value
        $content = sprintf("<input id='%s' type='hidden' name='%s' value='%s'  label='%s' >", $id, $name, $this->value, $attributes['label']);


        $content .= '<label style="display:inline; line-height:30px; "' . $attr . '><input style="float:none; margin-top:-5px; " ';
        log_debug("attr=================",$attr);
        $onClickContent = "";
        if(!empty($onClick))
        {
           $onClickContent =  sprintf(" onClick = '%s' ",$onClick);
        }
        $content .= sprintf("type='checkbox' tag='bool_check_box' id='%s' hidden_id='%s' value='%d' %s",$name, $id, $this->value,$onClickContent);

        if ($this->value)
        {
            $content .= ' checked';
        }

        $content .= "> </label>\n";

        return $content;
    }
}