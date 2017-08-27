-- TABLE pc_user

CREATE TABLE IF NOT EXISTS `pc_user` (
    `pc_user_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `_updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'UpdatedAt',
    `user_id` INT(10) NOT NULL COMMENT 'system.user.user_id',
    `username` VARCHAR(255) NOT NULL COMMENT '用户账号',
    `is_paying_user` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否是付费用户',
    `logo_path` VARCHAR(2083) COMMENT 'mp.pc_user.logo_path',
    `expired_time` DATETIME NOT NULL COMMENT '付费过期时间',
    `last_access` DATETIME COMMENT '上次访问时间',
    PRIMARY KEY (`pc_user_id`),
    KEY `ak_pc_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='网页用户' AUTO_INCREMENT=1;