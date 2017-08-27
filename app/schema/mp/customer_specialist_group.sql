-- TABLE customer_specialist_group

CREATE TABLE IF NOT EXISTS `customer_specialist_group` (
    `customer_specialist_group_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `group_name` CHAR(64) NOT NULL COMMENT '分组名',
    `comment` VARCHAR(128) COMMENT '备注',
    `work_time` CHAR(64) COMMENT '工作时间段',
    PRIMARY KEY (`customer_specialist_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服专员分组' AUTO_INCREMENT=1;