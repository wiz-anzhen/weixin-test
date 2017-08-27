<?php

namespace WBT\Controller\WxUser;


use Common\Helper\BaseController;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\MpUser;
class HelpController extends WxUserControllerBase
{
    public function helpAction()
    {

    }
    public function helpTypeAction()
    {
        $communityType = $this->_request->get( "community_type" );
        $this->_view->set('community_type', $communityType);
    }
    public function weixinAction()
    {
        $communityType = $this->_request->get( "community_type" );
        $this->_view->set('community_type', $communityType);
        $imgType = $this->_request->get( "img_type" );
        $imgName = "";
        if($communityType == "procurement_restaurant")
        {
            if($imgType == "chef")
            {
                $imgName = "chef_help";
            }
            if($imgType == "order")
            {
                $imgName = "order_help";
            }
            if($imgType == "examine")
            {
                $imgName = "examine_help";
            }
            if($imgType == "return")
            {
                $imgName = "return_help";
            }
            if($imgType == "chef_self")
            {
                $imgName = "chef_help";
            }
            if($imgType == "order_self")
            {
                $imgName = "order_self";
            }
            if($imgType == "examine_self")
            {
                $imgName = "examine_self";
            }
            if($imgType == "return_self")
            {
                $imgName = "return_self";
            }
        }

        if($communityType == "procurement_supply")
        {
            if($imgType == "order")
            {
                $imgName = "";
            }
            if($imgType == "examine")
            {
                $imgName = "";
            }
        }

        if($communityType == "procurement_total")
        {
            if($imgType == "order")
            {
                $imgName = "";
            }
            if($imgType == "examine")
            {
                $imgName = "";
            }
        }

        $this->_view->set('img_name', $imgName);
    }

}