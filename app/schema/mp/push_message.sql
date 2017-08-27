-- TABLE push_message

CREATE TABLE IF NOT EXISTS `push_message` (
    `push_message_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '通知标题',
    `content` CHAR(64) NOT NULL COMMENT '通知内容',
    `infoid` CHAR(64) COMMENT '信息编号/来源',
    `create_time` DATETIME NOT NULL COMMENT '创建时间',
    `send_no` TEXT COMMENT '指定房间编号',
    `send_time` DATETIME COMMENT '发布时间',
    `send_author` VARCHAR(128) COMMENT '发布者',
    `send_type` CHAR(32) COMMENT '发布类型',
    `send_status` CHAR(32) DEFAULT 'send_no' COMMENT '向用户发送模板消息通知发布状态',
    `send_range` CHAR(32) NOT NULL DEFAULT 'send_to_whole_community' COMMENT '向用户发送模板消息通知范围类型',
    PRIMARY KEY (`push_message_id`),
    KEY `ak_push_message_create_time` (`create_time`),
    KEY `ak_push_message_community_id` (`community_id`,`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='向App用户推送通知表' AUTO_INCREMENT=1;