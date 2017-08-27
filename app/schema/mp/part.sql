-- TABLE part

CREATE TABLE IF NOT EXISTS `part` (
    `part_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '名称',
    `comment` TEXT COMMENT '备注',
    `bound_store_id` VARCHAR(128) COMMENT '绑定供应商id',
    PRIMARY KEY (`part_id`),
    UNIQUE KEY `uk_part_ukey` (`title`,`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='档口列表' AUTO_INCREMENT=1;