-- TABLE wx_user_ext

CREATE TABLE IF NOT EXISTS `wx_user_ext` (
    `wx_user_ext_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '微信用户OpenID',
    `phone` BIGINT(20) COMMENT '用户电话',
    `phone_verify_code` CHAR(8) COMMENT '手机验证码',
    `phone_verify_code_generate_time` DATETIME COMMENT '手机验证码创建时间',
    PRIMARY KEY (`wx_user_ext_id`),
    UNIQUE KEY `uk_wx_user_ext_wx_user_id` (`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='普通微信用户扩展表' AUTO_INCREMENT=1;