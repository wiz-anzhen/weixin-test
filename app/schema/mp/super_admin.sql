-- TABLE super_admin

CREATE TABLE IF NOT EXISTS `super_admin` (
    `username` VARCHAR(128) NOT NULL COMMENT 'user.username',
    `comment` TEXT COMMENT '备注',
    `has_delete_power` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '删除公众账号权限',
    PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='超级管理员';