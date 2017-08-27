-- TABLE category

CREATE TABLE IF NOT EXISTS `category` (
    `category_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `store_id` INT(10) NOT NULL COMMENT '商城ID store.store_id',
    `title` CHAR(64) NOT NULL COMMENT '名称',
    `cover_img` VARCHAR(1024) COMMENT '分类封面图',
    `description` VARCHAR(128) COMMENT '分类描述',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `comment` TEXT COMMENT '备注',
    `is_delete` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
    `is_on_shelf` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否上架',
    `send_author` VARCHAR(128) COMMENT '发布者',
    `send_time` DATETIME COMMENT '发布时间',
    `shelf_time` VARCHAR(128) COMMENT '上架时间',
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品分类' AUTO_INCREMENT=1;