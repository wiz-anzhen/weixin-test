<?php

namespace Bluefin\HTML;

class CheckGroup extends SimpleComponent
{
    const COUNT_PER_LINE = 'checkbox_count_per_line';

    public $collection;
    public $stringValue;
    public $arrayValue;

    protected $_buttonType;

    public function __construct(array $collection, array $attributes = null)
    {
        parent::__construct($attributes);

        $this->collection = $collection;
        $this->stringValue = array_try_get($this->attributes, 'value', null, true);
        $this->arrayValue = explode(',', $this->stringValue);

        $this->addFirstClass('checkbox');
    }

    protected function _commitProperties()
    {
        parent::_commitProperties();
    }

    protected function _renderContent()
    {
        $content = '';
        $selected = false;

        $attributes = $this->attributes;
        $id = array_try_get($attributes, 'id', null, true);
        $name = array_try_get($attributes, 'name', '', true);
        $countPerLine = array_try_get($attributes, 'checkbox_count_per_line', 1, true);
        $attr = $this->renderAttributes($attributes);
        $index = 0;

        // 隐藏的input保存完整的value
        $content = sprintf("<input id='%s' type='hidden' name='%s' value='%s' label='%s' >",
            $id, $name, $this->stringValue, $attributes['label'] );

        $maxLen = 0;
        foreach ($this->collection as $key => $value)
        {
            $lenArr[] = strlen($value);
            $maxLen = max($lenArr);
        }

        foreach ($this->collection as $key => $value)
        {
            //$value = str_pad($value, $maxLen, '　');
            $content .= '<label style="display:inline; line-height:30px; "' . $attr . '><input style="float:none; margin-top:-5px;margin-left:8px; " ';

            if (isset($id))
            {
                $content .= sprintf(" id='%s_%s' ", $id ,  $index);
            }

            $content .= sprintf("type='checkbox' tag='check_group' hidden_id='%s' value='%s' ", $id, $key);

            if (strict_in_array($key, $this->arrayValue))
            {
                $content .= ' checked';
            }

            $content .= "> {$value}</label>\n";

            if ( $index % $countPerLine == ($countPerLine - 1) )
            {
                $content .= "<br>";
            }


            $index++;
        }

        return $content;
    }
}