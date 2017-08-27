-- TABLE directory_wx_user_visit

CREATE TABLE IF NOT EXISTS `directory_wx_user_visit` (
    `directory_wx_user_visit_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `directory_id` INT(10) NOT NULL COMMENT '二级目录id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT 'wx_user.wx_user_id',
    `last_access_ymd` INT(10) NOT NULL COMMENT '上次访问日期',
    PRIMARY KEY (`directory_wx_user_visit_id`),
    UNIQUE KEY `uk_directory_wx_user_visit_ukey` (`directory_id`,`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='二级目录用户访问表' AUTO_INCREMENT=1;