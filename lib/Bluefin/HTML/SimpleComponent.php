<?php

namespace Bluefin\HTML;

class SimpleComponent
{
    private static $__counters;
    public static $scripts;

    protected $_type;
    protected $_view;

    public $visible;
    public $dataContext;
    public $tag;
    public $attributes;
    public $label;
    public $clientSide;
    public $viewProperties;

    public function __construct(array $attributes = null, $tag = null)
    {

        if (isset($tag))
        {
            $this->tag = $tag;
        }
        else
        {
            $type = get_class($this);

            $pos = mb_strrpos($type, "\\");
            if (false !== $pos)
            {
                $type = mb_substr($type, $pos+1);
            }

            $this->_type = $type;
        }
        $this->attributes = isset($attributes) ? $attributes : [];
        $this->label = array_try_get($this->attributes, Form::FIELD_LABEL, null, true);
        $this->visible = array_try_get($this->attributes, 'visible', true, true);

        $this->_view = \Bluefin\App::getInstance()->gateway()->getController()->getView();
        $staticFileVersionConfig = _C('config.static_file_version');

        $this->_view->set('third_lib_version', $staticFileVersionConfig['third_lib']);
        $this->_view->set('our_lib_version', $staticFileVersionConfig['our_lib']);


        $required = array_try_get($this->attributes, Form::FIELD_REQUIRED, false, true);
        $message = array_try_get($this->attributes, Form::FIELD_MESSAGE, null, true);
        $hint = array_try_get($this->attributes, Form::FIELD_HINT, null, true);
        $prepend = array_try_get($this->attributes, Form::FIELD_PREPEND, false, true);
        $alt = array_try_get($this->attributes, Form::FIELD_ALT_NAME, null, true);
        $inline = array_try_get($this->attributes, Form::FIELD_INLINE, false, true);
        $confirm = array_try_get($this->attributes, Form::FIELD_CONFIRM, null, true);
        $excluded = array_try_get($this->attributes, Form::FIELD_EXCLUDED, false, true);

        $this->clientSide = [];
        $this->viewProperties = [];

        if ($prepend)
        {
            $this->viewProperties[Form::FIELD_PREPEND] = true;
        }

        if (isset($alt))
        {
            $this->viewProperties[Form::FIELD_ALT_NAME] = $alt;
        }

        // 增加label，方便在form表单中通过js提示用户填写必填字段
        if (isset($this->label))
        {
            $l = strip_tags($this->label);
            $this->attributes[Form::FIELD_LABEL] = $l;
        }

        if ($inline)
        {
            $this->viewProperties[Form::FIELD_INLINE] = true;
        }

        if ($required)
        {
            $this->clientSide[Form::FIELD_REQUIRED] = $required;
        }

        if (isset($message))
        {
            $this->clientSide[Form::FIELD_MESSAGE] = $message;
        }

        if (isset($hint))
        {
            $this->clientSide[Form::FIELD_HINT] = $hint;
        }

        if (isset($confirm))
        {
            $this->clientSide[Form::FIELD_CONFIRM] = $confirm;
        }

        if ($excluded)
        {
            $this->clientSide[Form::FIELD_EXCLUDED] = true;
        }

        $this->clientSide['avoid_empty_client_side'] = 'void';

    }

    public function addClass($class)
    {
        if (isset($this->attributes['class']))
        {
            $this->attributes['class'] .= ' ' . $class;
        }
        else
        {
            $this->attributes['class'] = $class;
        }

        return $this;
    }

    public function addFirstClass($class)
    {
        if (isset($this->attributes['class']))
        {
            $this->attributes['class'] = $class . ' ' . $this->attributes['class'];
        }
        else
        {
            $this->attributes['class'] = $class;
        }

        return $this;
    }

    public function generateID()
    {
        isset(self::$__counters) || (self::$__counters = []);

        $name = $this->_type;
        $name[0] = mb_strtolower($name[0]);

        if (array_key_exists($name, self::$__counters))
        {
            $this->attributes['id'] = $name . ++self::$__counters[$name];
        }
        else
        {
            self::$__counters[$name] = 1;
            $this->attributes['id'] = $name . '1';
        }
    }

    public function renderAttributes(array $attributes = null)
    {
        isset($attributes) || ($attributes = $this->attributes);

        $result = '';

        foreach ($attributes as $key => $value)
        {
            if (is_int($key))
            {
                $result .= " {$value}";
            }
            else if (isset($value))
            {
                $result .= " {$key}=\"{$value}\"";
            }
        }

        return $result;
    }

    public function __toString()
    {
        if (is_object($this->visible))
        {
            $this->visible = call_user_func($this->visible, $this->dataContext);
        }

        if ($this->visible)
        {
            $this->_commitProperties();
            return $this->_renderContent();
        }

        return '';
    }

    protected function _registerScript($script)
    {
        isset(self::$scripts) || (self::$scripts = '');
        self::$scripts .= $script . "\n";
    }

    protected function _commitProperties()
    {
        if (isset($this->attributes['class']))
        {
            $classes = explode(' ', array_try_get($this->attributes, 'class', ''));
            $classes = array_unique($classes);
            $this->attributes['class'] = implode(' ', $classes);
        }
    }

    protected function _renderContent()
    {
        return isset($this->tag) ? ("<{$this->tag}" . $this->renderAttributes() . '>' ) : '';
    }
}
