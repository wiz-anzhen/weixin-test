<?php

namespace WBT\Controller\J;

use Bluefin\App;
use Common\Helper\BaseController;
use Bluefin\HTML\NavBar;
use WBT\Business\Weixin\MpUserBusiness;
use WBT\Business\Weixin\ScoreBusiness;


class ScoreCardController extends BaseController
{
    // 用户扫描积分二维码后对应的链接后会触发此action
    // example url: http://canyin.weibotui.com/j/3c9a0423e856ccaa4decf9295be0fd03
    public function idAction()
    {
        $cardID = $this->_request->getRouteParam('card_id');

        $res = ScoreBusiness::getScoreCardInfo($cardID);

        log_debug("res = ", $res);

        $this->_view->set('result',$res);

        $navbar = new NavBar("积分");
        $this->_view->set('navbar', $navbar);

        $this->_view->set('cardID', $cardID);
    }
}
