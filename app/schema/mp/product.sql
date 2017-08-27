-- TABLE product

CREATE TABLE IF NOT EXISTS `product` (
    `product_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `_created_at` TIMESTAMP NOT NULL DEFAULT 0 COMMENT 'CreatedAt',
    `is_on_shelf` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否上架',
    `cost_price` DECIMAL(15,2) COMMENT '成本价',
    `reference_price` DECIMAL(15,2) COMMENT '参考价',
    `profit` DECIMAL(15,2) COMMENT '利润',
    `commissions` DECIMAL(15,2) COMMENT '提成',
    `mp_user_id` INT(10) NOT NULL COMMENT '公众账号ID',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `store_id` INT(10) NOT NULL COMMENT '商城ID store.store_id',
    `category_id` INT(10) NOT NULL COMMENT '分类ID category.category_id',
    `title` VARCHAR(128) NOT NULL COMMENT '商品名称',
    `img_url` VARCHAR(1024) COMMENT '图片地址',
    `big_img_url` VARCHAR(1024) COMMENT '大图片地址',
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '价格',
    `description` VARCHAR(128) COMMENT '商品描述',
    `sort_no` FLOAT NOT NULL DEFAULT 0 COMMENT '排序',
    `comment` TEXT COMMENT '备注',
    `detail_url` VARCHAR(1024) COMMENT '详情地址',
    `detail` TEXT COMMENT '商品详情',
    `parameters_url` VARCHAR(1024) COMMENT '商品参数地址',
    `is_delete` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
    `product_unit` CHAR(32) DEFAULT 'kilo' COMMENT '单位',
    PRIMARY KEY (`product_id`),
    KEY `ak_product_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品' AUTO_INCREMENT=1;