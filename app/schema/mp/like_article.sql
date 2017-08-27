-- TABLE like_article

CREATE TABLE IF NOT EXISTS `like_article` (
    `like_article_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT 'wx_user.wx_user_id',
    `mp_article_id` CHAR(32) NOT NULL COMMENT 'mp_article.mp_article_id',
    PRIMARY KEY (`like_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='喜欢文章记录表' AUTO_INCREMENT=1;