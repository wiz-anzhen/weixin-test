-- TABLE mp_article_daily_traffic

CREATE TABLE IF NOT EXISTS `mp_article_daily_traffic` (
    `mp_article_daily_traffic_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT '‘community.community_id’',
    `mp_article_id` CHAR(32) NOT NULL COMMENT '素材管理id',
    `ymd` INT(10) NOT NULL COMMENT '统计日期',
    `pv` INT(10) NOT NULL DEFAULT 0 COMMENT '每日点击量',
    PRIMARY KEY (`mp_article_daily_traffic_id`),
    UNIQUE KEY `uk_mp_article_daily_traffic_ukey` (`mp_article_id`,`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='素材管理访问量统计' AUTO_INCREMENT=1;