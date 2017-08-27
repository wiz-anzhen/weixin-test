<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-8-6
 * Time: 下午12:48
 */
namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;
use MP\Model\Mp\Category;
use MP\Model\Mp\MpUserConfigType;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\Store;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\StoreBusiness;
use MP\Model\Mp\Product;
use WBT\Business\Weixin\ProductBusiness;
use WBT\Business\Weixin\CateGoryBusiness;
use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\ProductComment;
use Bluefin\Data\Database;
use WBT\Business\Weixin\ProductCommentBusiness;


class TestController extends BaseController
{

    public function indexAction()
    {

    }
    public function aAction()
    {

    }
    public function bAction()
    {
        $communityID = "138";
        $this->_view->set("community_id",$communityID);


    }
    public function cAction()
    {
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);

        $this->_view->set( 'store_data', $storeData );
    }
    public function dAction()
    {

    }
    public function eAction()
    {

    }
    public function fAction()
    {

    }
    public function gAction()
    {

    }
    public function hAction()
    {

    }
}