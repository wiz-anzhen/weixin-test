<?php

namespace WBT\Controller;

use Bluefin\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        $data = [ 'users' => [
            [ 'title' => '高富财富项目',
              'pic'   => 'gaofucaifuxiangmu.jpeg',
              'text'  => 'Rogers:[图片]', ],
            [ 'title' => '文件传输助手',
              'pic'   => 'wenjianchuanshuzhushou.jpeg',
              'text'  => '[图片]', ],
            [ 'title' => 'kingcorestest1',
              'pic'   => 'kingcorestest1.jpeg',
              'text'  => '中奥卡 * 享业主购物消费特权', ],
            [ 'title' => 'Rogers',
              'pic'   => 'rogers.jpeg',
              'text'  => '还没呢', ],
            [ 'title' => '腾讯新闻',
              'pic'   => 'tengxunxinwen.jpeg',
              'text'  => '四川监狱搬迁百余武警压解', ],
            [ 'title' => '微餐厅产品和市场',
              'pic'   => 'chanpinheshichang.jpeg',
              'text'  => '吕良博：我周四周五请假回家...(大家有事找我 可以给我发微信或者打电话1861090360)', ],
            [ 'title' => '马宁',
              'pic'   => 'maning.jpeg',
              'text'  => '我通过你的好友验证请求，现在我们可以开始对话啦', ],
            [ 'title' => '订餐管家',
              'pic'   => 'dingcanguanjia.jpeg',
              'text'  => '亲，点击左下方‘键盘按钮’，按“+”键，选择位置，将位置发送给管家就可以查询啦！若定位有偏差，亲可移动地图重新定位', ],
        ] ];
        $this->_view->appendData( $data );
        log_debug('this is for test');

        $this->changeView( 'WBT/Test.html' );
    }
}
