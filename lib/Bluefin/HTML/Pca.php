<?php

namespace Bluefin\HTML;
use MP\Model\Mp\Province;
class Pca extends SimpleComponent
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
        log_debug("id00===================22222==============",$this->stringValue);
        log_debug("id00===================33333==============",$this->arrayValue);
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
        $content = sprintf("<input id='%s' type='hidden' name='%s' value='%s' label='%s' tag='ymd'>",
            $id, $name, $this->stringValue, $attributes['label'] );
        $province = Province::fetchRows(['*']);
        $select1 = "<select name = 'province' onChange='selectP();' style='width:90px;' tag='province'";
        $select1 .= sprintf(" id='province%s' ",$id);
        $select1 .= "> <option value=''>请选择</option>";
        foreach($province as $key=>$value)
        {
            $select1 .= "<option value='".$value['province_id']."'>".$value['name']."</option>";
        }
        /*$select1 .=    "<option value='2014'";
        if($this->arrayValue[0] == '2014')$select1 .= "selected='selected'";
        $select1 .= ">2014</option>
            <option value='2015'";
        if($this->arrayValue[0] == '2015') $select1 .= "selected='selected'";
        $select1 .= ">2015</option>
            <option value='2016'";
        if($this->arrayValue[0] == '2016')$select1 .= "selected='selected'";
        $select1 .= ">2016</option>
       <option value='2017'";
        if($this->arrayValue[0] == '2017')$select1 .= "selected='selected'";
        $select1 .= ">2017</option>
        <option value='2018'";
        if($this->arrayValue[0] == '2018')$select1 .= "selected='selected'";
        $select1 .= ">2018</option>
       <option value='2019'";
        if($this->arrayValue[0] == '2019')$select1 .= "selected='selected'";
        $select1 .= ">2019</option>*/
        $select1 .="</select>";

        /*$select2 = "<select name = 'time_start' style='width:55px' tag='month'";
        $select2 .= sprintf(" id='month%s' ",$id);
        $select2 .= ">
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

        </select>
        ";*/
        $select2 = "<select name='city'  onChange='selectA();' style='width:100px;' tag='city'";
        $select2 .= sprintf(" id='city%s' ",$id);
        $select2 .= ">
					<option value=''>请选择</option>

				</select>";

        /*$select4 = "<select name = 'time_end' style='width:55px;' tag='date'";
        $select4 .= sprintf(" id='date%s' ",$id);
        $select4 .= ">
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
        </select>
        ";*/
        $select4 = "<select name='area' id='area' style='width:100px;' tag='area'";
        $select4 .= sprintf(" id='area%s' ",$id);
        $select4 .=">
					<option value=''>请选择</option>
				</select>";
        //$content .= $select1 . "&nbsp;时&nbsp;&nbsp;" . $select2 . "&nbsp;分&nbsp;" . "——  " .$select3 . "&nbsp;时&nbsp;" . $select4 . "&nbsp;分&nbsp;";
        $content .= "&nbsp;省份&nbsp;&nbsp;" .$select1 . "&nbsp;城市&nbsp;&nbsp;" . $select2 . "&nbsp;区/县&nbsp;" . $select4 ;

        return $content;
    }
}