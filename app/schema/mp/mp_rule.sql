-- TABLE mp_rule

CREATE TABLE IF NOT EXISTS `mp_rule` (
    `mp_rule_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'UpdatedAt',
    `mp_user_id` INT(10) NOT NULL DEFAULT 0 COMMENT 'mp_user.mp_user_id',
    `name` CHAR(64) NOT NULL COMMENT '规则名称',
    `keyword` TEXT NOT NULL COMMENT '规则关键词',
    `content` TEXT NOT NULL COMMENT '规则内容',
    `content_type` CHAR(32) NOT NULL DEFAULT 'text' COMMENT '微信消息类型',
    PRIMARY KEY (`mp_rule_id`),
    KEY `ak_mp_rule_mp_user_id` (`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众帐号关键词匹配规则' AUTO_INCREMENT=1;