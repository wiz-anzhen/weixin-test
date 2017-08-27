-- TABLE album

CREATE TABLE IF NOT EXISTS `album` (
    `album_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `carousel_id` INT(10) NOT NULL COMMENT '轮播ID carousel.carousel_id',
    `title` CHAR(64) NOT NULL COMMENT '相册名称',
    `cover_img` VARCHAR(1024) NOT NULL COMMENT '封面图片地址',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `comment` TEXT COMMENT '备注',
    PRIMARY KEY (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='相册' AUTO_INCREMENT=1;