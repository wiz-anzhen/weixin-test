-- TABLE mp_fans_count

CREATE TABLE IF NOT EXISTS `mp_fans_count` (
    `_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'UpdatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `fans_count` INT(10) NOT NULL DEFAULT 0 COMMENT '粉丝数',
    PRIMARY KEY (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众帐号粉丝数';