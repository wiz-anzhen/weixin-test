-- TABLE community_admin

CREATE TABLE IF NOT EXISTS `community_admin` (
    `community_admin_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username` VARCHAR(128) NOT NULL COMMENT 'user.username',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `power` VARCHAR(1024) COMMENT '权限',
    `admin_username` VARCHAR(128) COMMENT '管理员帐号',
    `order_notify_time` DATETIME COMMENT '新订单提醒时间',
    `answer_notify_id` INT(10) COMMENT '新问卷id',
    `comment` TEXT COMMENT '备注',
    PRIMARY KEY (`community_admin_id`),
    KEY `ak_community_admin_username` (`username`),
    KEY `ak_community_admin_mp_user_id` (`mp_user_id`),
    KEY `ak_community_admin_community_id` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公众帐号管理员，多对多关系' AUTO_INCREMENT=1;