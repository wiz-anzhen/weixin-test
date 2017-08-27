-- TABLE restaurant

CREATE TABLE IF NOT EXISTS `restaurant` (
    `restaurant_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '名称',
    `comment` TEXT COMMENT '备注',
    `bound_community_id` INT(10) COMMENT '绑定小区id',
    PRIMARY KEY (`restaurant_id`),
    UNIQUE KEY `uk_restaurant_ukey` (`title`,`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='餐厅列表' AUTO_INCREMENT=1;