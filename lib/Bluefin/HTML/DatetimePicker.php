<?php

namespace Bluefin\HTML;

class DatetimePicker extends Component
{
    const TYPE_DATE_ONLY = 'date';
    const TYPE_TIME_ONLY = 'time';
    const TYPE_DATETIME = 'datetime';

    protected $_uploadID;
    public $dateTime;
    public $hasDateTime;

    public function __construct($type = self::TYPE_DATETIME, array $attributes = null)
    {
        parent::__construct($attributes);

        $datetime = array_try_get($this->attributes, 'value', null, true);

        $this->_uploadID = array_try_get($this->attributes, Form::FIELD_ID, null, false);

        isset($datetime) || ($datetime = time());

        $dateTimeFormat = array_try_get($this->attributes, 'dateTimeFormat', 'Y-m-d H:i:s', true);

        $this->hasDateTime = ($type == self::TYPE_DATETIME);

        if ($this->hasDateTime)
        {
            $this->dateTime = date($dateTimeFormat, $datetime);
            $this->_view->set('_dateTimePicker', true);
        }

    }
}
