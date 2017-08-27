-- TABLE mp_user_nav

CREATE TABLE IF NOT EXISTS `mp_user_nav` (
    `mp_user_nav_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL DEFAULT 0 COMMENT 'mp_user.mp_user_id',
    `title` TEXT COMMENT '标题',
    `description` TEXT COMMENT '描述',
    `pic_url` VARCHAR(1024) COMMENT '图片url',
    `url` VARCHAR(1024) COMMENT '图文消息url',
    `sort_no` FLOAT NOT NULL COMMENT '排序字段',
    `navigation_type` CHAR(32) NOT NULL DEFAULT 'general' COMMENT '微信导航类型',
    PRIMARY KEY (`mp_user_nav_id`),
    KEY `ak_mp_user_nav_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信主导航' AUTO_INCREMENT=1;