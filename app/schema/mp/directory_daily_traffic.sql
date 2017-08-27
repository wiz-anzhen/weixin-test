-- TABLE directory_daily_traffic

CREATE TABLE IF NOT EXISTS `directory_daily_traffic` (
    `directory_daily_traffic_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT '‘community.community_id’',
    `directory_id` INT(10) NOT NULL COMMENT '二级目录id',
    `ymd` INT(10) NOT NULL COMMENT '统计日期',
    `uv` INT(10) NOT NULL DEFAULT 0 COMMENT '每日独立访客量',
    `pv` INT(10) NOT NULL DEFAULT 0 COMMENT '每日点击量',
    PRIMARY KEY (`directory_daily_traffic_id`),
    UNIQUE KEY `uk_directory_daily_traffic_ukey` (`directory_id`,`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='二级目录访问量统计' AUTO_INCREMENT=1;