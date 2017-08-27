<?php

namespace Bluefin\HTML;

class ImageUpload extends SimpleComponent
{
    protected $_supportUrl;
    protected $_uploadID;
    protected $_name;

    public function __construct(array $attributes = null)
    {
        //log_debug("attributes :",$attributes);
        parent::__construct($attributes);

        $this->_supportUrl = array_try_get($this->attributes, 'value', false, false);

        $action = array_try_get($this->attributes, 'action', null, true);
        $fileUpload = $this->_view->get('_fileUpload');
        isset($fileUpload) || [$fileUpload = []];
        $this->_uploadID = array_try_get($this->attributes, Form::FIELD_ID, null, false);
        $this->_name = array_try_get($this->attributes, Form::FIELD_NAME, null, false);

        if (!isset($this->_uploadID))
        {
            throw new \Bluefin\Exception\InvalidOperationException("Missing id attribute!");
        }

        $fileUpload[$this->_uploadID] = $action;
        $this->_view->set('_fileUpload', $fileUpload);
        //log_debug("this->attributes:",$this->attributes);
    }

    // 一些变量后缀方便用于jQuery访问，参见 default.html.twig, default.script.twig
  public function _renderContent()
{
    /*    $img = sprintf("<img  thumbnail='form' orginalid='%s'  id='%s_IMG' src='%s' name='%s' label='%s' style='width:150px;height: 150px' />",
            $this->_uploadID, $this->_uploadID, $this->_supportUrl, $this->_name, $this->attributes['label']);*/


    /*  return sprintf('<div style="float:left">%s</div><div><input  id="%s_UPLOAD" class="file_upload" type="file"/> </div><input id="%s_INPUT" name="%s" label="%s" value="%s" type="hidden"/>',
          $img, $this->_uploadID ,$this->_uploadID , $this->_name, $this->attributes['label'],  $this->attributes['value']);*/

    $i = sprintf("<img  thumbnail='form' orginalid='%s' id='%s_IMG'  src='%s' name='%s' label='%s' style='width:80px;height: 80px' />", $this->_uploadID, $this->_uploadID, $this->_supportUrl, $this->_name, $this->attributes['label']);

    $p = sprintf('<div style="float:left">%s</div><div><input  id="%s_UPLOAD" class="file_upload" type="file"/> </div><input id="%s_INPUT" name="%s" label="%s" value="%s" type="hidden"/>',
        $i, $this->_uploadID ,$this->_uploadID , $this->_name, $this->attributes['label'],  $this->attributes['value']);

    $img = sprintf("<img id='%s_mainImg' src='%s' style='width:150px;height: 150px' />", $this->_uploadID, $this->attributes['value']);
    $div = sprintf("<div id='%s_myModal' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true' style='z-index:9999999;display:none;width:530px;'>
    <div class='modal-header' style='background-color:#0e90d2;color:#ffffff;height:30px;'>
        <button type='button' class='close' aria-hidden='true' style='color:#ffffff;'></button>
        <h4 id='myModalLabel'>添加图片</h4>
    </div>
    <div class='modal-body' style='height:200px;font-size:12px;font-weight:bold;'>
        <ul class='nav_tab' id='%s_myTab' >
            <li class='%s_test1 %s_actives' tag='nav_tab_son' uploadID='{$this->_uploadID}' style='background-color:#7388C1;color:#ffffff;'>从你的电脑上传</li>
            <li class='%s_test2' tag='nav_tab_son' uploadID='{$this->_uploadID}' style='background-color:#ECECEC;'>从网上地址添加</li>
        </ul>
        <div class='clear'></div>
        <div class='tab_content'>
            <div id='%s_test1'>
                %s<br/><br/>
                <div style='margin-top:15px;font-size:12px;color:#717171;'><font style='color:#ff0000;size:40px;'>*</font>支持上传jpg、jpeg、gif、png、bmp格式的图片，大小不超过5M</div>
            </div>
            <div id='%s_test2' style='display:none;' >
                地址：<input type='text'  value='' id='%s_imgUrl'><br/>
                <p style='font-size:12px;color:#DDDDDD;margin-left:45px;'>例如：http://www.sina.com/logo.png</p>

            </div>
        </div>
    </div>
    <div class='modal-footer'>
        <input type='button' class='btn btn-primary' id='success' tag='img_button'  component_id='%s' value='确定' >
        <input type='button' class='btn' tag='img_button_clear' uploadID='{$this->_uploadID}' value='取消'>
    </div>
</div>
    ",$this->_uploadID, $this->_uploadID, $this->_uploadID, $this->_uploadID, $this->_uploadID, $this->_uploadID, $p, $this->_uploadID, $this->_uploadID, $this->_uploadID,  $this->_uploadID);
    return sprintf('<div style="float:left">%s</div>&nbsp;&nbsp;<a href="#" role="button" uploadID="%s" class="btn btn-primary"  tag="img_upload_first">上传图片</a><br/>%s',$img,$this->_uploadID,$div);
}
}
