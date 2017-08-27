-- TABLE chat_room_record

CREATE TABLE IF NOT EXISTS `chat_room_record` (
    `chat_room_record_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `wx_user_id` CHAR(64) NOT NULL COMMENT '用户ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp.chat_room_record.mp_user_id',
    `wx_user_name` VARCHAR(128) COMMENT '用户名称',
    `community_id` INT(10) NOT NULL COMMENT '用户所在社区ID',
    `vip_no` BIGINT(20) NOT NULL COMMENT '客服专员会员号ID',
    `group_name` VARCHAR(128) COMMENT '客服组名称',
    `cs_name` VARCHAR(128) COMMENT 'mp.chat_room_record.cs_name',
    `content_value` TEXT NOT NULL COMMENT '聊天记录',
    `cs_group_id` INT(10) COMMENT '客服专员分组id',
    `cs_id` INT(10) COMMENT '客服专员id',
    `record_time` DATETIME NOT NULL COMMENT 'mp.chat_room_record.record_time',
    `content_type` CHAR(32) NOT NULL DEFAULT 'text' COMMENT '聊天内容类型',
    PRIMARY KEY (`chat_room_record_id`),
    KEY `ak_chat_room_record_wx_user_id` (`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='聊天室通话记录表' AUTO_INCREMENT=1;