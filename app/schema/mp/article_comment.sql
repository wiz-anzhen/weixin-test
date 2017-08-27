-- TABLE article_comment

CREATE TABLE IF NOT EXISTS `article_comment` (
    `article_comment_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `wx_user_id` CHAR(64) NOT NULL COMMENT 'wx_user.wx_user_id',
    `mp_article_id` CHAR(32) NOT NULL COMMENT 'mp_article.mp_article_id',
    `comment` TEXT NOT NULL COMMENT 'mp.article_comment.comment',
    `mail_recipients` TEXT COMMENT '接收人邮件列表',
    `mail_content` TEXT NOT NULL DEFAULT '' COMMENT '邮件内容',
    PRIMARY KEY (`article_comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章意见反馈' AUTO_INCREMENT=1;