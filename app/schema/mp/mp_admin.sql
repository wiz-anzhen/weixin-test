-- TABLE mp_admin

CREATE TABLE IF NOT EXISTS `mp_admin` (
    `mp_admin_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username` VARCHAR(128) NOT NULL COMMENT 'user.username',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `comment` TEXT COMMENT '备注',
    PRIMARY KEY (`mp_admin_id`),
    KEY `ak_mp_admin_username` (`username`),
    KEY `ak_mp_admin_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众帐号管理员，多对多关系' AUTO_INCREMENT=1;