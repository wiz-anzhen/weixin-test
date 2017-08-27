<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\DirectoryCommonType;
use MP\Model\Mp\DirectorySmallFlowType;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Directory;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\TopUrlType;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\DirectoryBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\DirectoryDailyTraffic;
use MP\Model\Mp\DirectoryWxUserVisit;
use Bluefin\Data\Database;
use MP\Model\Mp\DirectoryPowerType;

class DirectoryController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "directory";
        parent::_init();
    }
    //显示
    public function listTopAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $communityId = $this->_request->get( Directory::COMMUNITY_ID );
        $communityName = $this->_request->get( 'community_name' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $this->_view->set( "community_name", $communityName);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);

        $outputColumns = TopDirectory::s_metadata()->getFilterOptions();
        $condition= [TopDirectory::MP_USER_ID => $mpUserID,TopDirectory::COMMUNITY_ID => $communityId] ;

        $data          = DirectoryBusiness::getListTop( $condition, $paging, null,
            $outputColumns );
        $power = $this->checkChangePower("directory_rw","directory_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('directory_rw', $checkReadPower);
        $shownColumns = [
            TopDirectory::TOP_DIR_NO => [Table::COLUMN_CELL_STYLE => 'width:15%'],
            TopDirectory::TITLE,
            TopDirectory::TOP_DIRECTORY_ID => [Table::COLUMN_TITLE => "一级目录id"],
            TopDirectory::DIRECTORY_BACKGROUND_IMG => [
                Table::COLUMN_FUNCTION => function (array $row)
                {
                    return sprintf('<img src="%s" width="130px" height="" alt="无图"/>', $row[TopDirectory::DIRECTORY_BACKGROUND_IMG]);
                }],
            TopDirectory::DIRECTORY_TOP_IMG => [
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        return sprintf('<img src="%s" width="130px" height="" alt="无图"/>', $row[TopDirectory::DIRECTORY_TOP_IMG]);
                    }],
            TopDirectory::DIRECTORY_TOP_IMG_SECOND => [
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        return sprintf('<img src="%s" width="130px" height="" alt="无图"/>', $row[TopDirectory::DIRECTORY_TOP_IMG_SECOND]);
                    }],
            TopDirectory::DIRECTORY_TOP_IMG_THIRD => [
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        return sprintf('<img src="%s" width="130px" height="" alt="无图"/>', $row[TopDirectory::DIRECTORY_TOP_IMG_THIRD]);
                    }],

            TopDirectory::URL_TYPE => [
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        return TopUrlType::getDisplayName($row[TopDirectory::URL_TYPE]);
                    }],

            TopDirectory::POWER_TYPE => [
                    Table::COLUMN_TITLE => "目录打开权限",
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            return DirectoryPowerType::getDisplayName($row[TopDirectory::POWER_TYPE]);
                        }],

            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION   => function(array $row) use ($communityName)
                    {
                        $topDirectoryID = $row[TopDirectory::TOP_DIRECTORY_ID];
                        $communityID = $row[TopDirectory::COMMUNITY_ID];
                        $mpUserID = $row[TopDirectory::MP_USER_ID];

                        $listLink =  new Link('列表', "/mp_admin/directory/list?mp_user_id={$mpUserID}&community_id={$communityID}&top_directory_id={$topDirectoryID}&community_name={$communityName}");

                        $addressLink =  new Link('地址', "/wx_user/directory/list?community_id={$communityID}&top_directory_id={$topDirectoryID}&mp_user_id={$mpUserID}", ['target' => '_blank']);
                        $link = $listLink . '&nbsp&nbsp'. $addressLink;
                        return $link;
                    }
            ],
        ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =
                [
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION   => function(array $row) use ($communityName,$power)
                        {
                            $topDirectoryID = $row[TopDirectory::TOP_DIRECTORY_ID];
                            $communityID = $row[TopDirectory::COMMUNITY_ID];
                            $mpUserID = $row[TopDirectory::MP_USER_ID];
                            $updateLink = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/directory_dialog/edit_top?community_id={$communityID}&top_directory_id={$topDirectoryID}');");
                            $url = "../fcrm/directory/remove_top?community_id={$communityID}&top_directory_id={$topDirectoryID}";
                            $deleteLink =  new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('$url', null, function(){bluefinBH.showInfo('删除成功',function(){location.reload();});});});");

                            $listLink =  new Link('列表', "/mp_admin/directory/list?mp_user_id={$mpUserID}&community_id={$communityID}&top_directory_id={$topDirectoryID}&community_name={$communityName}");

                            $addressLink =  new Link('地址', "/wx_user/directory/list?community_id={$communityID}&top_directory_id={$topDirectoryID}&mp_user_id={$mpUserID}", ['target' => '_blank']);

                            $ret = $updateLink . "<br>". $listLink . "<br>". $addressLink;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$deleteLink;
                            }
                            return $ret;
                        }
            ];
        }



        $table               = Table::fromDbData( $data, $outputColumns,
            TopDirectory::TOP_DIRECTORY_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function listAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $communityId = $this->_request->get( Directory::COMMUNITY_ID );
        $communityName = $this->_request->get( 'community_name' );
        $this->_view->set( "community_name", $communityName);
        $this->_view->set( "community_id", $communityId);

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);

        $topDirectoryId = $this->_request->get(Directory::TOP_DIRECTORY_ID);
        $topDirectory = new TopDirectory([TopDirectory::TOP_DIRECTORY_ID => $topDirectoryId]);
        $this->_view->set('top_directory', $topDirectory->data());
        $outputColumns = Directory::s_metadata()->getFilterOptions();
        $ranking       = [ Directory::SORT_NO ];
        $condition     = [ Directory::TOP_DIRECTORY_ID => $topDirectoryId, MpArticle::MP_USER_ID => $mpUserID ,Directory::COMMUNITY_ID =>  $communityId];
        $data          = DirectoryBusiness::getList( $condition, $paging, $ranking,
            $outputColumns );
        $power = $this->checkChangePower("directory_rw","directory_d" ,"directory_small_flow");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('directory_rw', $checkReadPower);
        $shownColumns = [
            Directory::ICON =>
                [ Table::COLUMN_FUNCTION => function(array $row)
                    {
                        return sprintf('<img src="%s" width="30px" height="30px" alt="无图"/>', $row[Directory::ICON]);
                    }
                ],
            Directory::TITLE,
            Directory::DIRECTORY_ID => [Table::COLUMN_TITLE => "目录id"],
            Directory::COMMON_TYPE =>
                [
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return DirectoryCommonType::getDisplayName($row[Directory::COMMON_TYPE]);
                        }
                ],
            Directory::SORT_NO,
            Directory::COMMON_CONTENT =>
                [
                    Table::COLUMN_CELL_STYLE => "width:8%",
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            $content = DirectoryBusiness::getContent($row[Directory::COMMON_TYPE],$row[Directory::COMMON_CONTENT]);

                            if($row[Directory::COMMON_TYPE]== "text")
                            {
                                return $content;
                            }
                            else
                            {
                                if(preg_match("/\?/i", $content))
                                {
                                    $content = $content."&mp_user_id=".$row[Directory::MP_USER_ID]."&power_type=".$row[Directory::POWER_TYPE];
                                }
                                else
                                {
                                    $content = $content."?mp_user_id=".$row[Directory::MP_USER_ID]."&power_type=".$row[Directory::POWER_TYPE];
                                }

                                return "<a href=\"$content\">链接到这里</a>";
                            }
                        }
                ],
            Directory::POWER_TYPE => [
                Table::COLUMN_TITLE => "目录打开权限",
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        return DirectoryPowerType::getDisplayName($row[Directory::POWER_TYPE]);
                    }],
            Directory::HEAD_DESC     =>
                [
                  Table::COLUMN_CELL_STYLE => 'width: 10%',
                  Table::COLUMN_TITLE => "组头组尾说明",
                  Table::COLUMN_FUNCTION => function (array $row)
                      {
                          $retHead = "组头说明"."："."<br>".$row[Directory::HEAD_DESC]."<br><br>";
                          $retTail = "组尾说明"."："."<br>".str_replace("\n", '<br/>', $row[Directory::TAIL_DESC])."<br><br>";
                          $ret = "";
                          if(!empty($row[Directory::HEAD_DESC]))
                          {
                              $ret .= $retHead;
                          }
                          if(!empty($row[Directory::TAIL_DESC]))
                          {
                              $ret .= $retTail;
                          }
                          return $ret;
                      }
                ],

            Directory::GROUP_END  => [ Table::COLUMN_TITLE    => '分组标志',
                                       Table::COLUMN_FUNCTION => function ( array $row )
                                           {
                                               return $row[Directory::GROUP_END] == 1 ? '至此为一组' : '';
                                           }, ],
            Directory::SHOW_SMALL_FLOW =>
                [
                    Table::COLUMN_TITLE => "是否开通小流量",
                    Table::COLUMN_CELL_STYLE => 'width: 7%',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return $row[Directory::SHOW_SMALL_FLOW] == 1 ? '已开通':'未开通';

                        }
                ],
            Directory::SMALL_FLOW_TYPE =>
                [
                    Table::COLUMN_TITLE => "小流量类型",
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return DirectorySmallFlowType::getDisplayName($row[Directory::SMALL_FLOW_TYPE]);

                        }
                ],
            Directory::SMALL_FLOW_CONTENT =>
                [
                    Table::COLUMN_TITLE => "小流量内容",
                    Table::COLUMN_CELL_STYLE => "width:8%",
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            $content = DirectoryBusiness::getContent($row[Directory::SMALL_FLOW_TYPE],$row[Directory::SMALL_FLOW_CONTENT]);
                            if(preg_match("/\?/i", $content))
                            {
                                $content = $content."&mp_user_id=".$row[Directory::MP_USER_ID]."&power_type=".$row[Directory::POWER_TYPE];
                            }
                            else
                            {
                                $content = $content."?mp_user_id=".$row[Directory::MP_USER_ID]."&power_type=".$row[Directory::POWER_TYPE];
                            }

                            return "<a href=\"$content\">链接到这里</a>";
                        }
                ],
            Directory::SMALL_FLOW_NO =>
                [
                    Table::COLUMN_CELL_STYLE => 'width: 9%',
                    Table::COLUMN_FUNCTION => function (array $row){
                            $smallFlowNo = explode("\n",$row[Directory::SMALL_FLOW_NO]);
                            $ret = "";
                            foreach($smallFlowNo as $value)
                            {
                                $ret.=$value."<br>";
                            }
                            return $ret;
                        }
                ],
            "流量统计" => [
                Table::COLUMN_TITLE => '流量统计',
                Table::COLUMN_CELL_STYLE => 'width: 8%',
                Table::COLUMN_FUNCTION => function(array $row) {
                        $directoryDailyTraffic = DirectoryBusiness::getDirectoryTraffic($row[Directory::DIRECTORY_ID]);
                        $totalUv = $directoryDailyTraffic["total_uv"];
                        $totalPv = $directoryDailyTraffic["total_pv"];
                        $currentUv = $directoryDailyTraffic["current_uv"];
                        $currentPv = $directoryDailyTraffic["current_pv"];
                        $ret = "
                        Uv总计：$totalUv<br>
                        Pv总计：$totalPv<br>
                        当天Uv：$currentUv<br>
                        当天Pv：$currentPv<br>";
                        return $ret;

                    }
            ],
        ];
        // 小区管理员看不到操作栏目
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =
            [
                    Table::COLUMN_TITLE => '操作',
                    Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION => function (array $row)use($power)
                        {
                         $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/directory_dialog/edit?community_id={$row['community_id']}&directory_id={$row['directory_id']}');");
                         $delete =  new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function(){javascript:wbtAPI.call('../fcrm/directory/remove?community_id={$row['community_id']}&directory_id={$row['directory_id']}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                         $directorySmallFlow = new Link('小流量设置', "javascript:bluefinBH.ajaxDialog('/mp_admin/directory_dialog/small_flow_set?community_id={$row['community_id']}&directory_id={$row['directory_id']}');");
                            $ret = $update;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            if($power["directory_small_flow"])
                            {
                                $ret .= "<br>".$directorySmallFlow;
                            }
                            return $ret;
                    },

            ];
        }

        $table               = Table::fromDbData( $data, $outputColumns,
            Directory::DIRECTORY_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function trafficAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $communityId = $this->_request->get( Directory::COMMUNITY_ID );
        $communityName = $this->_request->get( 'community_name' );
        $this->_view->set( "community_name", $communityName);
        $this->_view->set( "community_id", $communityId);

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);

        $outputColumns = DirectoryDailyTraffic::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking       = [ DirectoryDailyTraffic::YMD => true,DirectoryDailyTraffic::DIRECTORY_ID ];
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 100;

        $condition     = [ DirectoryDailyTraffic::COMMUNITY_ID => $communityId ,];
        $data          = DirectoryBusiness::getListTraffic( $condition, $paging, $ranking,$outputColumns );

        $shownColumns = [
            DirectoryDailyTraffic::DIRECTORY_ID =>
                [
                    Table::COLUMN_TITLE => '目录名称',
                    Table::COLUMN_FUNCTION => function(array $row) {
                        $directory = new Directory([Directory::DIRECTORY_ID => $row[Directory::DIRECTORY_ID]]);
                        $directoryName = $directory->getTitle();
                        return $directoryName;
                } ],
            DirectoryDailyTraffic::YMD,
            DirectoryDailyTraffic::UV =>
                [
                    Table::COLUMN_FUNCTION => function(array $row) {
                            if(!isset($row[DirectoryDailyTraffic::UV]))
                            {
                                return '0';
                            }
                            else
                            {
                                return $row[DirectoryDailyTraffic::UV];
                            }

                        } ],
            DirectoryDailyTraffic::PV =>
                [
                    Table::COLUMN_FUNCTION => function(array $row) {
                            if(!isset($row[DirectoryDailyTraffic::PV]))
                            {
                                return '0';
                            }
                            else
                            {
                                return $row[DirectoryDailyTraffic::PV];
                            }

                        } ],

            ];

        $table               = Table::fromDbData( $data, $outputColumns,
            DirectoryDailyTraffic::DIRECTORY_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}