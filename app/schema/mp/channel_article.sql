-- TABLE channel_article

CREATE TABLE IF NOT EXISTS `channel_article` (
    `channel_article_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `channel_id` INT(10) NOT NULL COMMENT 'channel.channel_id',
    `article_title` VARCHAR(255) COMMENT '标题',
    `share_url` VARCHAR(1024) COMMENT '分享图片',
    `article_desc` TEXT COMMENT '摘要',
    `article_url` VARCHAR(1024) COMMENT '外部链接',
    `release_date` DATE NOT NULL COMMENT '发布日期',
    `keep_top` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '置顶',
    `article_detail` TEXT COMMENT '正文',
    `article_type` CHAR(32) NOT NULL DEFAULT 'article_ours' COMMENT '来源',
    PRIMARY KEY (`channel_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='频道文章' AUTO_INCREMENT=1;