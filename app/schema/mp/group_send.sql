-- TABLE group_send

CREATE TABLE IF NOT EXISTS `group_send` (
    `group_send_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` VARCHAR(128) NOT NULL COMMENT '消息标题',
    `content_value` TEXT COMMENT '消息内容',
    `msg_id` CHAR(64) COMMENT '返回消息ID',
    `status` TEXT COMMENT '返回发送状态',
    `create_time` DATETIME NOT NULL COMMENT '创建时间',
    `group_send_no` TEXT COMMENT '指定房间编号',
    `group_send_time` DATETIME COMMENT '发布时间',
    `group_send_author` VARCHAR(128) COMMENT '发布者',
    `send_type` CHAR(32) COMMENT '发送类型',
    `content_type` CHAR(32) NOT NULL DEFAULT 'custom_text' COMMENT '群发信息内容类型',
    `group_send_range` CHAR(32) NOT NULL DEFAULT 'send_to_whole_community' COMMENT '群发范围类型',
    PRIMARY KEY (`group_send_id`),
    KEY `ak_group_send_create_time` (`create_time`),
    KEY `ak_group_send_msg_id` (`msg_id`),
    KEY `ak_group_send_community_id` (`community_id`,`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群发信息' AUTO_INCREMENT=1;