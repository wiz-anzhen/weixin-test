-- TABLE report

CREATE TABLE IF NOT EXISTS `report` (
    `report_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `ymd` INT(10) NOT NULL COMMENT '年月日，示例20120525',
    `fans_total_count` INT(10) NOT NULL DEFAULT 0 COMMENT '粉丝数',
    `net_increase_fans_count` INT(10) NOT NULL DEFAULT 0 COMMENT '净增粉丝数',
    `followed_count` INT(10) NOT NULL DEFAULT 0 COMMENT '被关注的次数',
    `unfollowed_count` INT(10) NOT NULL DEFAULT 0 COMMENT '被取消关注的次数',
    `uv` INT(10) NOT NULL DEFAULT 0 COMMENT '活跃用户数',
    `pv` INT(10) NOT NULL DEFAULT 0 COMMENT '访问量',
    `zhuhu_count` INT(10) NOT NULL DEFAULT 0 COMMENT '住户总数',
    `yezhu_count` INT(10) NOT NULL DEFAULT 0 COMMENT '业主总数',
    `zhuhu_verify` INT(10) NOT NULL DEFAULT 0 COMMENT '认证住户总数',
    `yezhu_verify` INT(10) NOT NULL DEFAULT 0 COMMENT '认证业主总数',
    PRIMARY KEY (`report_id`),
    UNIQUE KEY `uk_report_ukey` (`mp_user_id`,`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='每日报表' AUTO_INCREMENT=1;