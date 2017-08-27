-- TABLE wx_user_feedback

CREATE TABLE IF NOT EXISTS `wx_user_feedback` (
    `wx_user_feedback_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `wx_user_id` CHAR(64) NOT NULL COMMENT 'wx_user.wx_user_id',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `content` TEXT NOT NULL COMMENT 'mp.wx_user_feedback.content',
    PRIMARY KEY (`wx_user_feedback_id`),
    KEY `ak_wx_user_feedback_mp_user_id` (`mp_user_id`),
    KEY `ak_wx_user_feedback_wx_user_id` (`wx_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户意见反馈' AUTO_INCREMENT=1;