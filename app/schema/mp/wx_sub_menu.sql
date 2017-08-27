-- TABLE wx_sub_menu

CREATE TABLE IF NOT EXISTS `wx_sub_menu` (
    `wx_sub_menu_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `wx_menu_id` INT(10) NOT NULL COMMENT '微信子菜单id',
    `wx_menu_name` CHAR(16) NOT NULL COMMENT '菜单名字',
    `wx_menu_key` CHAR(32) COMMENT '按钮KEY值',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序id',
    `content_value` TEXT COMMENT '导航内容',
    `url` VARCHAR(1024) COMMENT 'view类型所对应的网址',
    `access_authority` CHAR(32) DEFAULT 'all' COMMENT '访问权限类型',
    `wx_menu_type` CHAR(32) NOT NULL DEFAULT 'click' COMMENT '微信菜单类型',
    `content_type` CHAR(32) DEFAULT 'custom_text' COMMENT '微信菜单内容类型',
    PRIMARY KEY (`wx_sub_menu_id`),
    KEY `ak_wx_sub_menu_wx_menu_id` (`wx_menu_id`),
    KEY `ak_wx_sub_menu_wx_menu_key` (`wx_menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信自定义子菜单' AUTO_INCREMENT=1;