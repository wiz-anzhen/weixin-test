-- TABLE community_config

CREATE TABLE IF NOT EXISTS `community_config` (
    `community_config_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `config_value` TEXT COMMENT '社区配置值',
    `config_type` CHAR(32) NOT NULL DEFAULT 'cs_answer' COMMENT '社区配置类型',
    PRIMARY KEY (`community_config_id`),
    UNIQUE KEY `uk_community_config_ukey` (`community_id`,`config_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='社区配置表' AUTO_INCREMENT=1;