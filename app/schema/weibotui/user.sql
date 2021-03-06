-- TABLE user

CREATE TABLE IF NOT EXISTS `user` (
    `user_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'UpdatedAt',
    `_is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'IsDeleted',
    `username` VARCHAR(255) NOT NULL COMMENT '用户账号',
    `password` VARCHAR(128) NOT NULL COMMENT '密码',
    `password_salt` INT(8) ZEROFILL NOT NULL COMMENT '干扰码',
    `profile` BLOB COMMENT 'personal_profile',
    `preferences` TEXT COMMENT '用户偏好',
    `status` VARCHAR(32) NOT NULL DEFAULT 'nonactivated' COMMENT '用户状态',
    `nonactivated_time` DATETIME COMMENT '未激活时间',
    `activated_time` DATETIME COMMENT '正常时间',
    `disabled_time` DATETIME COMMENT '已禁用时间',
    `status_log` TEXT COMMENT '用户状态历史',
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uk_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户' AUTO_INCREMENT=10000;