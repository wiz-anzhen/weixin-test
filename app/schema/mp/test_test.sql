-- TABLE test_test

CREATE TABLE IF NOT EXISTS `test_test` (
    `test_test_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `power` CHAR(32) DEFAULT 'channel' COMMENT 'power',
    PRIMARY KEY (`test_test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='仅用来生成一些枚举类型的代码' AUTO_INCREMENT=1;