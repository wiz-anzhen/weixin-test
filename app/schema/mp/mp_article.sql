-- TABLE mp_article

CREATE TABLE IF NOT EXISTS `mp_article` (
    `mp_article_id` CHAR(32) NOT NULL COMMENT 'ID',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` VARCHAR(255) NOT NULL COMMENT '文章标题',
    `share_desc` VARCHAR(255) NOT NULL COMMENT '分享时显示的文章摘要',
    `content` TEXT NOT NULL COMMENT '文章内容',
    `show_like` TINYINT(1) NOT NULL COMMENT '是否显示喜欢和意见反馈',
    `like_count` INT(10) NOT NULL COMMENT '已经点喜欢的人数',
    `user_level` VARCHAR(128) NOT NULL DEFAULT 'level_0' COMMENT '逗号分隔的可阅读用户等级',
    `redirect` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否跳转',
    `redirect_url` VARCHAR(1024) COMMENT '跳转链接',
    `tag` VARCHAR(255) COMMENT '标签',
    `last_modify_time` DATETIME NOT NULL COMMENT '最后修改时间',
    `last_modify_author` VARCHAR(128) COMMENT '最后修改人',
    PRIMARY KEY (`mp_article_id`),
    KEY `ak_mp_article_mp_user_id` (`mp_user_id`),
    KEY `ak_mp_article_community_id` (`community_id`),
    KEY `ak_mp_article_last_modify_time` (`last_modify_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='富文本内容';