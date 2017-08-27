-- TABLE group_send_item

CREATE TABLE IF NOT EXISTS `group_send_item` (
    `group_send_item_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` TEXT NOT NULL COMMENT '消息标题',
    `content` TEXT COMMENT '消息内容',
    `pic_url` VARCHAR(1024) COMMENT '消息图片url',
    `description` TEXT COMMENT '内容摘要',
    `content_source_url` VARCHAR(1024) COMMENT '原文链接',
    `author` VARCHAR(128) COMMENT '作者',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `show_cover_pic` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否显示封面',
    `group_send_id` INT(10) COMMENT '群发信息id',
    PRIMARY KEY (`group_send_item_id`),
    KEY `ak_group_send_item_group_send_id` (`group_send_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='保存群发信息内容' AUTO_INCREMENT=1;