-- TABLE community_report

CREATE TABLE IF NOT EXISTS `community_report` (
    `community_report_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `ymd` INT(10) NOT NULL COMMENT '年月日，示例20120525',
    `zhuhu_count` INT(10) NOT NULL DEFAULT 0 COMMENT '住户总数',
    `yezhu_count` INT(10) NOT NULL DEFAULT 0 COMMENT '业主总数',
    `zhuhu_verify` INT(10) NOT NULL DEFAULT 0 COMMENT '认证住户总数',
    `yezhu_verify` INT(10) NOT NULL DEFAULT 0 COMMENT '认证业主总数',
    PRIMARY KEY (`community_report_id`),
    UNIQUE KEY `uk_community_report_ukey` (`community_id`,`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='每日社区报表' AUTO_INCREMENT=1;