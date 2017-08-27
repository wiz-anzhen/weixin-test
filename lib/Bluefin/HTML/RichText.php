<?php

namespace Bluefin\HTML;

class RichText extends SimpleComponent
{
    const WIDTH = 'rich_text_width';
    const HEIGHT = 'rich_text_height';

    protected $_richTextID;
    protected $_name;

    public function __construct(array $attributes = null)
    {
//        log_debug("attributes :",$attributes);

        parent::__construct($attributes);


        $action = array_try_get($this->attributes, 'action', null, true);
        $richText = $this->_view->get('_richText');
        isset($richText) || $richText = [];
        $this->_richTextID = array_try_get($this->attributes, Form::FIELD_ID, null, false);
        $this->_name = array_try_get($this->attributes, Form::FIELD_NAME, null, false);

        if (!isset($this->_richTextID))
        {
            throw new \Bluefin\Exception\InvalidOperationException("Missing id attribute!");
        }

        $richText[$this->_richTextID] = $action;
        $this->_view->set('_richText', $richText);
        log_debug("this->attributes:",$this->attributes);

    }

    // 一些变量后缀方便用于jQuery访问，参见 default.html.twig, default.script.twig
    public function _renderContent()
    {
        $defaultWidth = 400;
        $defaultHeight = 300;
        $width  = array_try_get($this->attributes, self::WIDTH,$defaultWidth);
        $height = array_try_get($this->attributes, self::HEIGHT,$defaultHeight);


        return sprintf('<textarea id="%s" name="%s" label="%s" style="width: %dpx;height: %dpx">%s</textarea>',
             $this->_richTextID , $this->_name, $this->attributes['label'],
             $width,$height, $this->attributes['value']);
    }
}
