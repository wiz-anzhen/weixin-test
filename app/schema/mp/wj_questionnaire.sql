-- TABLE wj_questionnaire

CREATE TABLE IF NOT EXISTS `wj_questionnaire` (
    `wj_questionnaire_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` VARCHAR(255) NOT NULL COMMENT '问卷名称',
    `head_desc` VARCHAR(255) NOT NULL COMMENT '卷首语',
    `tail_desc` VARCHAR(255) NOT NULL COMMENT '卷尾语',
    `comment` TEXT COMMENT '问卷描述',
    `customer_profile` CHAR(32) NOT NULL DEFAULT 'none' COMMENT '用户个人信息',
    PRIMARY KEY (`wj_questionnaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问卷表' AUTO_INCREMENT=1;