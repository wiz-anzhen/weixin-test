-- TABLE carousel

CREATE TABLE IF NOT EXISTS `carousel` (
    `carousel_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `title` CHAR(64) NOT NULL COMMENT '轮播标题',
    `comment` TEXT COMMENT '备注',
    PRIMARY KEY (`carousel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轮播' AUTO_INCREMENT=1;