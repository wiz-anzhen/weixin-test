-- TABLE test_procurement

CREATE TABLE IF NOT EXISTS `test_procurement` (
    `test_procurement_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `power` CHAR(32) DEFAULT 'none' COMMENT '员工权限',
    PRIMARY KEY (`test_procurement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='仅用来生成一些枚举类型的代码' AUTO_INCREMENT=1;