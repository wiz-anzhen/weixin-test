-- TABLE mp_user_config

CREATE TABLE IF NOT EXISTS `mp_user_config` (
    `mp_user_config_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `config_value` TEXT COMMENT '公众账号配置值',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `config_type` CHAR(32) NOT NULL DEFAULT 'birthday_title' COMMENT '公众账号配置类型',
    `config_type_type` CHAR(32) NOT NULL DEFAULT 'text' COMMENT '公众账号配置值选择类型',
    PRIMARY KEY (`mp_user_config_id`),
    UNIQUE KEY `uk_mp_user_config_ukey` (`mp_user_id`,`config_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众账号配置表' AUTO_INCREMENT=1;