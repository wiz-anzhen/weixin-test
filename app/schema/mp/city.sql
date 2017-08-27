-- TABLE city

CREATE TABLE IF NOT EXISTS `city` (
    `city_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` CHAR(64) NOT NULL COMMENT '城市名称',
    `province_id` BIGINT(11) COMMENT '所属省份编号',
    `zip` CHAR(6) COMMENT '邮编(预留)',
    `created` BIGINT(11) COMMENT 'mp.city.created',
    `updated` BIGINT(11) COMMENT 'mp.city.updated',
    `sort` BIGINT(11) DEFAULT 0 COMMENT '排序,从大到小,大于0表示是热门城市',
    PRIMARY KEY (`city_id`),
    KEY `ak_city_province_id` (`province_id`),
    KEY `ak_city_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='城市表' AUTO_INCREMENT=1;