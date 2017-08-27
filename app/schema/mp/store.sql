-- TABLE store

CREATE TABLE IF NOT EXISTS `store` (
    `store_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '名称',
    `comment` TEXT COMMENT '备注',
    `is_delete` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
    `bound_community_id` INT(10) COMMENT '绑定小区id',
    `bound_store_id` INT(10) COMMENT '绑定商城id',
    PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城列表' AUTO_INCREMENT=1;