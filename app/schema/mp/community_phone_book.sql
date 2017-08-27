-- TABLE community_phone_book

CREATE TABLE IF NOT EXISTS `community_phone_book` (
    `community_phone_book_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `mp_user_id` INT(10) NOT NULL COMMENT 'mp_user.mp_user_id',
    `name` VARCHAR(128) NOT NULL COMMENT '名称',
    `phone` CHAR(32) NOT NULL COMMENT '电话',
    PRIMARY KEY (`community_phone_book_id`),
    KEY `ak_community_phone_book_community_id` (`community_id`,`mp_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='电话薄' AUTO_INCREMENT=1;