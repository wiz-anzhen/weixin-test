-- TABLE bill_detail

CREATE TABLE IF NOT EXISTS `bill_detail` (
    `bill_detail_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `bill_id` INT(10) NOT NULL COMMENT 'bill.bill_id',
    `community_id` INT(10) NOT NULL COMMENT 'community.community_id',
    `bill_day` INT(10) NOT NULL COMMENT '账单日期',
    `bill_detail_name` VARCHAR(128) NOT NULL COMMENT '业主欠费明细收费项目名称',
    `billing_cycle` VARCHAR(128) NOT NULL COMMENT '计费周期',
    `detail_payment` DECIMAL(15,2) NOT NULL COMMENT '应收金额',
    `detail_remarks` VARCHAR(255) COMMENT '备注',
    PRIMARY KEY (`bill_detail_id`),
    KEY `ak_bill_detail_bill_id` (`bill_id`),
    KEY `ak_bill_detail_community_id` (`community_id`),
    KEY `ak_bill_detail_bill_day` (`bill_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收费通知单业主欠费明细' AUTO_INCREMENT=1;