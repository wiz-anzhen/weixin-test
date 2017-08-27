-- TABLE address_level_info

CREATE TABLE IF NOT EXISTS `address_level_info` (
    `address_level_info_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `add_info` VARCHAR(128) NOT NULL COMMENT '地址信息',
    `level` INT(10) NOT NULL COMMENT '级别',
    `parent_id` INT(10) NOT NULL DEFAULT 0 COMMENT '上一级地址信息id',
    PRIMARY KEY (`address_level_info_id`),
    KEY `ak_address_level_info_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地址分级数据表' AUTO_INCREMENT=1;