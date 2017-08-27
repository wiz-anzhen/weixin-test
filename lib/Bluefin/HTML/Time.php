<?php

namespace Bluefin\HTML;

class Time extends SimpleComponent
{
    public $collection;
    public $stringValue;
    public $arrayValue;

    protected $_buttonType;

    public function __construct(array $collection, array $attributes = null)
    {
        parent::__construct($attributes);

        $this->collection = $collection;
        $this->stringValue = array_try_get($this->attributes, 'value', null, true);
        $this->arrayValue = explode(':', $this->stringValue);

        log_debug("id00===================11==============",$this->stringValue);
        log_debug("id00===================11==============",$this->arrayValue);
    }

    protected function _commitProperties()
    {
        parent::_commitProperties();
    }

    protected function _renderContent()
    {
        $attributes = $this->attributes;
        $id = array_try_get($attributes, 'id', null, true);
        log_debug("id00===================00==============",$id);
        $name = array_try_get($attributes, 'name', '', true);
        // 隐藏的input保存完整的value
        $content = sprintf("<input id='%s' type='hidden' name='%s' value='%s' label='%s' tag='select_time'>",
            $id, $name, $this->stringValue, $attributes['label'] );
        $select1 = "<select name = 'hour_start' style='width:55px;' tag='select_time1'";
        $select1 .= sprintf(" id='select_time1%s' ",$id);
        $select1 .= ">
            <option value='00'";
        if($this->arrayValue[0] == '00')$select1 .= "selected='selected'";
        $select1 .= ">00</option>
            <option value='01'";
        if($this->arrayValue[0] == '01') $select1 .= "selected='selected'";
        $select1 .= ">01</option>
            <option value='02'";
        if($this->arrayValue[0] == '02')$select1 .= "selected='selected'";
        $select1 .= ">02</option>
            <option value='03'";
        if($this->arrayValue[0] == '03')$select1 .= "selected='selected'";
        $select1 .= ">03</option>
            <option value='04'";
        if($this->arrayValue[0] == '04')$select1 .= "selected='selected'";
        $select1 .= ">04</option>
            <option value='05'";
        if($this->arrayValue[0] == '05')$select1 .= "selected='selected'";
        $select1 .= ">05</option>
            <option value='06'";
        if($this->arrayValue[0] == '06')$select1 .= "selected='selected'";
        $select1 .= ">06</option>
            <option value='07'";
        if($this->arrayValue[0] == '07')$select1 .= "selected='selected'";
        $select1 .= ">07</option>
            <option value='08'";
        if($this->arrayValue[0] == '08')$select1 .= "selected='selected'";
        $select1 .= ">08</option>
            <option value='09'";
        if($this->arrayValue[0] == '09')$select1 .= "selected='selected'";
        $select1 .= ">09</option>
            <option value='10'";
        if($this->arrayValue[0] == '10')$select1 .= "selected='selected'";
        $select1 .= ">10</option>
            <option value='11'";
        if($this->arrayValue[0] == '11')$select1 .= "selected='selected'";
        $select1 .= ">11</option>
            <option value='12'";
        if($this->arrayValue[0] == '12')$select1 .= "selected='selected'";
        $select1 .= ">12</option>
            <option value='13'";
        if($this->arrayValue[0] == '13')$select1 .= "selected='selected'";
        $select1 .= ">13</option>
            <option value='14'";
        if($this->arrayValue[0] == '14')$select1 .= "selected='selected'";
        $select1 .= ">14</option>
            <option value='15'";
        if($this->arrayValue[0] == '15')$select1 .= "selected='selected'";
        $select1 .= ">15</option>
            <option value='16'";
        if($this->arrayValue[0] == '16')$select1 .= "selected='selected'";
        $select1 .= ">16</option>
            <option value='17'";
        if($this->arrayValue[0] == '17')$select1 .= "selected='selected'";
        $select1 .= ">17</option>
            <option value='18'";
        if($this->arrayValue[0] == '18')$select1 .= "selected='selected'";
        $select1 .= ">18</option>
            <option value='19'";
        if($this->arrayValue[0] == '19')$select1 .= "selected='selected'";
        $select1 .= ">19</option>
            <option value='20'";
        if($this->arrayValue[0] == '20')$select1 .= "selected='selected'";
        $select1 .= ">20</option>
            <option value='21'";
        if($this->arrayValue[0] == '21') $select1 .= "selected='selected'";
        $select1 .= ">21</option>
            <option value='22'";
        if($this->arrayValue[0] == '22')$select1 .= "selected='selected'";
        $select1 .= ">22</option>
            <option value='23'";
        if($this->arrayValue[0] == '23')$select1 .= "selected='selected'";
        $select1 .= ">23</option>
        </select>
        ";
        $select2 = "<select name = 'time_start' style='width:55px' tag='select_time2'";
        $select2 .= sprintf(" id='select_time2%s' ",$id);
        $select2 .= ">
            <option value='00'";
        if($this->arrayValue[1] == '00')$select2 .= "selected='selected'";
        $select2 .= ">00</option>
            <option value='01'";
        if($this->arrayValue[1] == '01')$select2 .= "selected='selected'";
        $select2 .= ">01</option>
            <option value='02'";
        if($this->arrayValue[1] == '02')$select2 .= "selected='selected'";
        $select2 .= ">02</option>
            <option value='03'";
        if($this->arrayValue[1] == '03')$select2 .= "selected='selected'";
        $select2 .= ">03</option>
            <option value='04'";
        if($this->arrayValue[1] == '04')$select2 .= "selected='selected'";
        $select2 .= ">04</option>
            <option value='05'";
        if($this->arrayValue[1] == '05')$select2 .= "selected='selected'";
        $select2 .= ">05</option>
            <option value='06'";
        if($this->arrayValue[1] == '06')$select2 .= "selected='selected'";
        $select2 .= ">06</option>
            <option value='07'";
        if($this->arrayValue[1] == '07')$select2 .= "selected='selected'";
        $select2 .= ">07</option>
            <option value='08'";
        if($this->arrayValue[1] == '08')$select2 .= "selected='selected'";
        $select2 .= ">08</option>
            <option value='09'";
        if($this->arrayValue[1] == '09')$select2 .= "selected='selected'";
        $select2 .= ">09</option>
            <option value='10'";
        if($this->arrayValue[1] == '10')$select2 .= "selected='selected'";
        $select2 .= ">10</option>
            <option value='11'";
        if($this->arrayValue[1] == '11')$select2 .= "selected='selected'";
        $select2 .= ">11</option>
            <option value='12'";
        if($this->arrayValue[1] == '12')$select2 .= "selected='selected'";
        $select2 .= ">12</option>
            <option value='13'";
        if($this->arrayValue[1] == '13')$select2 .= "selected='selected'";
        $select2 .= ">13</option>
            <option value='14'";
        if($this->arrayValue[1] == '14')$select2 .= "selected='selected'";
        $select2 .= ">14</option>
            <option value='15'";
        if($this->arrayValue[1] == '15')$select2 .= "selected='selected'";
        $select2 .= ">15</option>
            <option value='16'";
        if($this->arrayValue[1] == '16')$select2 .= "selected='selected'";
        $select2 .= ">16</option>
            <option value='17'";
        if($this->arrayValue[1] == '17')$select2 .= "selected='selected'";
        $select2 .= ">17</option>
            <option value='18'";
        if($this->arrayValue[1] == '18')$select2 .= "selected='selected'";
        $select2 .= ">18</option>
            <option value='19'";
        if($this->arrayValue[1] == '19')$select2 .= "selected='selected'";
        $select2 .= ">19</option>
            <option value='20'";
        if($this->arrayValue[1] == '20')$select2 .= "selected='selected'";
        $select2 .= ">20</option>
            <option value='21'";
        if($this->arrayValue[1] == '21')$select2 .= "selected='selected'";
        $select2 .= ">21</option>
            <option value='22'";
        if($this->arrayValue[1] == '22')$select2 .= "selected='selected'";
        $select2 .= ">22</option>
            <option value='23'";
        if($this->arrayValue[1] == '23')$select2 .= "selected='selected'";
        $select2 .= ">23</option>
            <option value='24'";
        if($this->arrayValue[1] == '24')$select2 .= "selected='selected'";
        $select2 .= ">24</option>
            <option value='25'";
        if($this->arrayValue[1] == '25')$select2 .= "selected='selected'";
        $select2 .= ">25</option>
            <option value='26'";
        if($this->arrayValue[1] == '26')$select2 .= "selected='selected'";
        $select2 .= ">26</option>
            <option value='27'";
        if($this->arrayValue[1] == '27')$select2 .= "selected='selected'";
        $select2 .= ">27</option>
            <option value='28'";
        if($this->arrayValue[1] == '28')$select2 .= "selected='selected'";
        $select2 .= ">28</option>
            <option value='29'";
        if($this->arrayValue[1] == '29')$select2 .= "selected='selected'";
        $select2 .= ">29</option>
            <option value='30'";
        if($this->arrayValue[1] == '30')$select2 .= "selected='selected'";
        $select2 .= ">30</option>
            <option value='31'";
        if($this->arrayValue[1] == '31')$select2 .= "selected='selected'";
        $select2 .= ">31</option>
            <option value='32'";
        if($this->arrayValue[1] == '32')$select2 .= "selected='selected'";
        $select2 .= ">32</option>
            <option value='33'";
        if($this->arrayValue[1] == '33')$select2 .= "selected='selected'";
        $select2 .= ">33</option>
            <option value='34'";
        if($this->arrayValue[1] == '34')$select2 .= "selected='selected'";
        $select2 .= ">34</option>
            <option value='35'";
        if($this->arrayValue[1] == '35')$select2 .= "selected='selected'";
        $select2 .= ">35</option>
            <option value='36'";
        if($this->arrayValue[1] == '36')$select2 .= "selected='selected'";
        $select2 .= ">36</option>
            <option value='37'";
        if($this->arrayValue[1] == '37')$select2 .= "selected='selected'";
        $select2 .= ">37</option>
            <option value='38'";
        if($this->arrayValue[1] == '38')$select2 .= "selected='selected'";
        $select2 .= ">38</option>
            <option value='39'";
        if($this->arrayValue[1] == '39')$select2 .= "selected='selected'";
        $select2 .= ">39</option>
            <option value='40'";
        if($this->arrayValue[1] == '40')$select2 .= "selected='selected'";
        $select2 .= ">40</option>
            <option value='41'";
        if($this->arrayValue[1] == '41')$select2 .= "selected='selected'";
        $select2 .= ">41</option>
            <option value='42'";
        if($this->arrayValue[1] == '42')$select2 .= "selected='selected'";
        $select2 .= ">42</option>
            <option value='43'";
        if($this->arrayValue[1] == '43')$select2 .= "selected='selected'";
        $select2 .= ">43</option>
            <option value='44'";
        if($this->arrayValue[1] == '44')$select2 .= "selected='selected'";
        $select2 .= ">44</option>
            <option value='45'";
        if($this->arrayValue[1] == '45')$select2 .= "selected='selected'";
        $select2 .= ">45</option>
            <option value='46'";
        if($this->arrayValue[1] == '46')$select2 .= "selected='selected'";
        $select2 .= ">46</option>
            <option value='47'";
        if($this->arrayValue[1] == '47')$select2 .= "selected='selected'";
        $select2 .= ">47</option>
            <option value='48'";
        if($this->arrayValue[1] == '48')$select2 .= "selected='selected'";
        $select2 .= ">48</option>
            <option value='49'";
        if($this->arrayValue[1] == '49')$select2 .= "selected='selected'";
        $select2 .= ">49</option>
            <option value='50'";
        if($this->arrayValue[1] == '50')$select2 .= "selected='selected'";
        $select2 .= ">50</option>
            <option value='51'";
        if($this->arrayValue[1] == '51')$select2 .= "selected='selected'";
        $select2 .= ">51</option>
            <option value='52'";
        if($this->arrayValue[1] == '52')$select2 .= "selected='selected'";
        $select2 .= ">52</option>
            <option value='53'";
        if($this->arrayValue[1] == '53')$select2 .= "selected='selected'";
        $select2 .= ">53</option>
            <option value='54'";
        if($this->arrayValue[1] == '54')$select2 .= "selected='selected'";
        $select2 .= ">54</option>
            <option value='55'";
        if($this->arrayValue[1] == '55')$select2 .= "selected='selected'";
        $select2 .= ">55</option>
            <option value='56'";
        if($this->arrayValue[1] == '56')$select2 .= "selected='selected'";
        $select2 .= ">56</option>
            <option value='57'";
        if($this->arrayValue[1] == '57')$select2 .= "selected='selected'";
        $select2 .= ">57</option>
            <option value='58'";
        if($this->arrayValue[1] == '58')$select2 .= "selected='selected'";
        $select2 .= ">58</option>
            <option value='59'";
        if($this->arrayValue[1] == '59')$select2 .= "selected='selected'";
        $select2 .= ">59</option>

        </select>
        ";

        $select4 = "<select name = 'time_end' style='width:55px;' tag='select_time4'";
        $select4 .= sprintf(" id='select_time4%s' ",$id);
        $select4 .= ">
            <option value='00'";
        if($this->arrayValue[2] == '00')$select4 .= "selected='selected'";
        $select4 .= ">00</option>
            <option value='01'";
        if($this->arrayValue[2] == '01')$select4 .= "selected='selected'";
        $select4 .= ">01</option>
            <option value='02'";
        if($this->arrayValue[2] == '02')$select4 .= "selected='selected'";
        $select4 .= ">02</option>
            <option value='03'";
        if($this->arrayValue[2] == '03')$select4 .= "selected='selected'";
        $select4 .= ">03</option>
            <option value='04'";
        if($this->arrayValue[2] == '04')$select4 .= "selected='selected'";
        $select4 .= ">04</option>
            <option value='05'";
        if($this->arrayValue[2] == '05')$select4 .= "selected='selected'";
        $select4 .= ">05</option>
            <option value='06'";
        if($this->arrayValue[2] == '06')$select4 .= "selected='selected'";
        $select4 .= ">06</option>
            <option value='07'";
        if($this->arrayValue[2] == '07')$select4 .= "selected='selected'";
        $select4 .= ">07</option>
            <option value='08'";
        if($this->arrayValue[2] == '08')$select4 .= "selected='selected'";
        $select4 .= ">08</option>
            <option value='09'";
        if($this->arrayValue[2] == '09')$select4 .= "selected='selected'";
        $select4 .= ">09</option>
            <option value='10'";
        if($this->arrayValue[2] == '10')$select4 .= "selected='selected'";
        $select4 .= ">10</option>
            <option value='11'";
        if($this->arrayValue[2] == '11')$select4 .= "selected='selected'";
        $select4 .= ">11</option>
            <option value='12'";
        if($this->arrayValue[2] == '12')$select4 .= "selected='selected'";
        $select4 .= ">12</option>
            <option value='13'";
        if($this->arrayValue[2] == '13')$select4 .= "selected='selected'";
        $select4 .= ">13</option>
            <option value='14'";
        if($this->arrayValue[2] == '14')$select4 .= "selected='selected'";
        $select4 .= ">14</option>
            <option value='15'";
        if($this->arrayValue[2] == '15')$select4 .= "selected='selected'";
        $select4 .= ">15</option>
            <option value='16'";
        if($this->arrayValue[2] == '16')$select4 .= "selected='selected'";
        $select4 .= ">16</option>
            <option value='17'";
        if($this->arrayValue[2] == '17')$select4 .= "selected='selected'";
        $select4 .= ">17</option>
            <option value='18'";
        if($this->arrayValue[2] == '18')$select4 .= "selected='selected'";
        $select4 .= ">18</option>
            <option value='19'";
        if($this->arrayValue[2] == '19')$select4 .= "selected='selected'";
        $select4 .= ">19</option>
            <option value='20'";
        if($this->arrayValue[2] == '20')$select4 .= "selected='selected'";
        $select4 .= ">20</option>
            <option value='21'";
        if($this->arrayValue[2] == '21')$select4 .= "selected='selected'";
        $select4 .= ">21</option>
            <option value='22'";
        if($this->arrayValue[2] == '22')$select4 .= "selected='selected'";
        $select4 .= ">22</option>
            <option value='23'";
        if($this->arrayValue[2] == '23')$select4 .= "selected='selected'";
        $select4 .= ">23</option>
            <option value='24'";
        if($this->arrayValue[2] == '24')$select4 .= "selected='selected'";
        $select4 .= ">24</option>
            <option value='25'";
        if($this->arrayValue[2] == '25')$select4 .= "selected='selected'";
        $select4 .= ">25</option>
            <option value='26'";
        if($this->arrayValue[2] == '26')$select4 .= "selected='selected'";
        $select4 .= ">26</option>
            <option value='27'";
        if($this->arrayValue[2] == '27')$select4 .= "selected='selected'";
        $select4 .= ">27</option>
            <option value='28'";
        if($this->arrayValue[2] == '28')$select4 .= "selected='selected'";
        $select4 .= ">28</option>
            <option value='29'";
        if($this->arrayValue[2] == '29')$select4 .= "selected='selected'";
        $select4 .= ">29</option>
            <option value='30'";
        if($this->arrayValue[2] == '30')$select4 .= "selected='selected'";
        $select4 .= ">30</option>
            <option value='31'";
        if($this->arrayValue[2] == '31')$select4 .= "selected='selected'";
        $select4 .= ">31</option>
            <option value='32'";
        if($this->arrayValue[2] == '32')$select4 .= "selected='selected'";
        $select4 .= ">32</option>
            <option value='33'";
        if($this->arrayValue[2] == '33')$select4 .= "selected='selected'";
        $select4 .= ">33</option>
            <option value='34'";
        if($this->arrayValue[2] == '34')$select4 .= "selected='selected'";
        $select4 .= ">34</option>
            <option value='35'";
        if($this->arrayValue[2] == '35')$select4 .= "selected='selected'";
        $select4 .= ">35</option>
            <option value='36'";
        if($this->arrayValue[2] == '36')$select4 .= "selected='selected'";
        $select4 .= ">36</option>
            <option value='37'";
        if($this->arrayValue[2] == '37')$select4 .= "selected='selected'";
        $select4 .= ">37</option>
            <option value='38'";
        if($this->arrayValue[2] == '38')$select4 .= "selected='selected'";
        $select4 .= ">38</option>
            <option value='39'";
        if($this->arrayValue[2] == '39')$select4 .= "selected='selected'";
        $select4 .= ">39</option>
            <option value='40'";
        if($this->arrayValue[2] == '40')$select4 .= "selected='selected'";
        $select4 .= ">40</option>
            <option value='41'";
        if($this->arrayValue[2] == '41')$select4 .= "selected='selected'";
        $select4 .= ">41</option>
            <option value='42'";
        if($this->arrayValue[2] == '42')$select4 .= "selected='selected'";
        $select4 .= ">42</option>
            <option value='43'";
        if($this->arrayValue[2] == '43')$select4 .= "selected='selected'";
        $select4 .= ">43</option>
            <option value='44'";
        if($this->arrayValue[2] == '44')$select4 .= "selected='selected'";
        $select4 .= ">44</option>
            <option value='45'";
        if($this->arrayValue[2] == '45')$select4 .= "selected='selected'";
        $select4 .= ">45</option>
            <option value='46'";
        if($this->arrayValue[2] == '46')$select4 .= "selected='selected'";
        $select4 .= ">46</option>
            <option value='47'";
        if($this->arrayValue[2] == '47')$select4 .= "selected='selected'";
        $select4 .= ">47</option>
            <option value='48'";
        if($this->arrayValue[2] == '48')$select4 .= "selected='selected'";
        $select4 .= ">48</option>
            <option value='49'";
        if($this->arrayValue[2] == '49')$select4 .= "selected='selected'";
        $select4 .= ">49</option>
            <option value='50'";
        if($this->arrayValue[2] == '50')$select4 .= "selected='selected'";
        $select4 .= ">50</option>
            <option value='51'";
        if($this->arrayValue[2] == '51')$select4 .= "selected='selected'";
        $select4 .= ">51</option>
            <option value='52'";
        if($this->arrayValue[2] == '52')$select4 .= "selected='selected'";
        $select4 .= ">52</option>
            <option value='53'";
        if($this->arrayValue[2] == '53')$select4 .= "selected='selected'";
        $select4 .= ">53</option>
            <option value='54'";
        if($this->arrayValue[2] == '54')$select4 .= "selected='selected'";
        $select4 .= ">54</option>
            <option value='55'";
        if($this->arrayValue[2] == '55')$select4 .= "selected='selected'";
        $select4 .= ">55</option>
            <option value='56'";
        if($this->arrayValue[2] == '56')$select4 .= "selected='selected'";
        $select4 .= ">56</option>
            <option value='57'";
        if($this->arrayValue[2] == '57')$select4 .= "selected='selected'";
        $select4 .= ">57</option>
            <option value='58'";
        if($this->arrayValue[2] == '58')$select4 .= "selected='selected'";
        $select4 .= ">58</option>
            <option value='59'";
        if($this->arrayValue[2] == '59')$select4 .= "selected='selected'";
        $select4 .= ">59</option>
        </select>
        ";

        //$content .= $select1 . "&nbsp;时&nbsp;&nbsp;" . $select2 . "&nbsp;分&nbsp;" . "——  " .$select3 . "&nbsp;时&nbsp;" . $select4 . "&nbsp;分&nbsp;";
        $content .= $select1 . "&nbsp;时&nbsp;&nbsp;" . $select2 . "&nbsp;分&nbsp;" . $select4 . "&nbsp;秒&nbsp;";

        return $content;
    }
}