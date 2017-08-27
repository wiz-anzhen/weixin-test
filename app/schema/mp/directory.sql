-- TABLE directory

CREATE TABLE IF NOT EXISTS `directory` (
    `directory_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `top_directory_id` INT(10) NOT NULL COMMENT '一级目录',
    `title` VARCHAR(128) COMMENT '标题',
    `icon` VARCHAR(1024) COMMENT '图标',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序号',
    `group_end` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '分组标志',
    `show_small_flow` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否显示小流量',
    `common_url` VARCHAR(1024) COMMENT '目录连接',
    `common_content` TEXT COMMENT '目录内容',
    `small_flow_url` VARCHAR(1024) COMMENT '小流量目录连接',
    `small_flow_content` TEXT COMMENT '小流量目录内容',
    `small_flow_no` TEXT COMMENT '小流量用户房间编号',
    `head_desc` VARCHAR(128) COMMENT '组头说明',
    `tail_desc` TEXT COMMENT '组尾说明',
    `common_type` CHAR(32) NOT NULL DEFAULT 'text' COMMENT '目录类型',
    `small_flow_type` CHAR(32) DEFAULT 'link' COMMENT '小流量目录类型',
    `power_type` CHAR(32) DEFAULT 'all' COMMENT ' 目录权限判断',
    PRIMARY KEY (`directory_id`),
    KEY `ak_directory_community_id` (`mp_user_id`,`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='二级目录' AUTO_INCREMENT=1;