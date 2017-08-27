-- TABLE channel

CREATE TABLE IF NOT EXISTS `channel` (
    `channel_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` VARCHAR(128) NOT NULL COMMENT '频道名称',
    PRIMARY KEY (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='频道' AUTO_INCREMENT=1;