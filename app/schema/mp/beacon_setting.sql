-- TABLE beacon_setting

CREATE TABLE IF NOT EXISTS `beacon_setting` (
    `beacon_setting_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `uuid` CHAR(64) NOT NULL COMMENT '硬件设备id',
    `description` VARCHAR(255) COMMENT '设备描述',
    PRIMARY KEY (`beacon_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ibeacon硬件设备管理表' AUTO_INCREMENT=1;