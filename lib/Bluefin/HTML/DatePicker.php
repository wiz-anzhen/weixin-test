<?php

namespace Bluefin\HTML;

class DatePicker extends Component
{
    const TYPE_DATE_ONLY = 'date';
    const TYPE_TIME_ONLY = 'time';
    const TYPE_DATETIME = 'datetime';

    protected $_uploadID;
    public $date;
 //   public $time;
    public $hasDate;
 //   public $hasTime;

    public function __construct($type = self::TYPE_DATETIME, array $attributes = null)
    {
        parent::__construct($attributes);

        $datetime = array_try_get($this->attributes, 'value', null, true);

        if(!empty($datetime))
        {
            $y = substr($datetime,0,4);
            $m = substr($datetime,4,2);
            $d = substr($datetime,6,2);
            $datetime = mktime(0,0,0,$m,$d,$y);
            //$datetime = strtotime($y."-".$m."-".$d);
        }

        $this->_uploadID = array_try_get($this->attributes, Form::FIELD_ID, null, false);

        isset($datetime) || ($datetime = time());

        $dateFormat = array_try_get($this->attributes, 'dateFormat', 'Y-m-d', true);
 //       $timeFormat = array_try_get($this->attributes, 'timeFormat', 'H:i:s', true);


        $this->hasTime = ($type != self::TYPE_DATE_ONLY);
        $this->hasDate = ($type != self::TYPE_TIME_ONLY);


/*
        if ($this->hasTime)
        {
            $this->time = date($timeFormat, $datetime);
            $this->_view->set('_timePicker', true);
        }
*/
        if ($this->hasDate)
        {
            $this->date = date($dateFormat, $datetime);
            $this->_view->set('_datePicker', true);
        }


    }
}
