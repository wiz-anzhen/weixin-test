-- TABLE wx_menu

CREATE TABLE IF NOT EXISTS `wx_menu` (
    `wx_menu_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `access_authority` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '访问权限',
    `name` CHAR(16) NOT NULL COMMENT '菜单名字',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序id',
    PRIMARY KEY (`wx_menu_id`),
    KEY `ak_wx_menu_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信自定义菜单' AUTO_INCREMENT=1;