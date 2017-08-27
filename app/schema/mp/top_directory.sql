-- TABLE top_directory

CREATE TABLE IF NOT EXISTS `top_directory` (
    `top_directory_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '名称',
    `top_dir_no` INT(10) NOT NULL COMMENT '一级目录编号',
    `directory_background_img` VARCHAR(1024) COMMENT '目录背景图片地址',
    `directory_top_img` VARCHAR(1024) COMMENT '目录顶部图片地址一',
    `directory_top_img_second` VARCHAR(1024) COMMENT '目录顶部图片地址二',
    `directory_top_img_third` VARCHAR(1024) COMMENT '目录顶部图片地址三',
    `url_type` CHAR(32) DEFAULT 'none' COMMENT '目录链接类型',
    `power_type` CHAR(32) DEFAULT 'all' COMMENT ' 目录权限判断',
    PRIMARY KEY (`top_directory_id`),
    UNIQUE KEY `uk_top_directory_ukey` (`community_id`,`top_dir_no`),
    KEY `ak_top_directory_community_id` (`community_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='一级目录' AUTO_INCREMENT=1;