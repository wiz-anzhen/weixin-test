TRUNCATE TABLE `album`;
TRUNCATE TABLE `carousel`;
TRUNCATE TABLE `channel`;
TRUNCATE TABLE `mp_admin`;
TRUNCATE TABLE `mp_user`;
TRUNCATE TABLE `community`;
TRUNCATE TABLE `super_admin`;
TRUNCATE TABLE `top_directory`;
TRUNCATE TABLE `wj_choice`;
TRUNCATE TABLE `wj_question`;
TRUNCATE TABLE `wj_questionnaire`;
TRUNCATE TABLE `wj_user_answer`;
TRUNCATE TABLE `community_admin`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `store`;
TRUNCATE TABLE `product`;
TRUNCATE TABLE `category`;
TRUNCATE TABLE `wx_user`;
TRUNCATE TABLE `customer_specialist_group`;
TRUNCATE TABLE `customer_specialist`;
TRUNCATE TABLE `community_report`;
TRUNCATE TABLE `house_member`;
TRUNCATE TABLE `report`;
TRUNCATE TABLE `mp_article`;
TRUNCATE TABLE `order`;






INSERT INTO `store` (`store_id`, `_created_at`, `mp_user_id`, `community_id`, `title`, `comment`) VALUES
(1, '2013-11-22 06:07:55', 39545, 11, '鸿光楼住邦店', NULL),
(2, '2013-11-22 06:07:55', 39545,11, '家电数码产品', NULL),
(3, '2013-11-23 04:40:34', 39545, 11, '商品服务分类', NULL),
(4, '2013-11-23 04:52:50', 39545, 11, '团购', NULL),
(5, '2013-11-23 04:55:41', 39545, 11, '闪购', NULL),
(6, '2013-11-23 04:57:24', 39545, 11, '海外代购', NULL),
(7, '2013-11-23 05:22:10', 39545, 11, '新品上架', NULL),
(8, '2014-04-11 05:39:32', 39545, 11, '45', '45'),
(9, '2014-04-11 06:05:00', 39545, 11, '美食', NULL);

INSERT INTO `product` (`product_id`, `_created_at`, `mp_user_id`, `community_id`, `store_id`, `category_id`, `title`, `img_url`, `price`, `description`, `sort_no`, `comment`, `detail_url`,is_on_shelf) VALUES
(1, '2014-04-11 06:05:37', 39545, 11, 9, 14, '宫保鸡丁', NULL, 32, NULL, 1, NULL, NULL,1),
(2, '2014-04-11 06:07:37', 39545, 11, 9, 14, '鱼香肉丝', NULL, 21, NULL, 4, NULL, NULL,1);


INSERT INTO `category` (`category_id`, `_created_at`, `mp_user_id`, `community_id`, `store_id`, `title`, `description`, `sort_no`, `comment`,is_on_shelf) VALUES
(1, '2013-11-21 19:36:34', 39545, 11, 1, '中午工作餐', '在微信中回复‘特价’查看所有日期的特价菜', 1, NULL,1),
(2, '2013-11-21 19:36:41', 39545,11, 1, '招牌菜', NULL, 2, NULL,1),
(3, '2013-11-21 19:36:49', 39545, 11, 1, '港式烧味', NULL, 3, NULL,1),
(5, '2013-11-21 19:37:43', 39545, 11, 1, '港粤美点', NULL, 5, NULL,1),
(7, '2013-11-22 06:29:14', 39545, 11, 2, '手机', NULL, 10, NULL,1),
(8, '2013-11-22 06:29:25', 39545, 11, 2, '相机', NULL, 20, NULL,1),
(9, '2013-11-23 04:53:05', 39545, 11, 4, '奶粉', NULL, 1, NULL,1),
(10, '2013-11-23 05:24:47', 39545,11, 6, '奶粉', NULL, 1, NULL,1),
(11, '2013-11-23 05:57:21', 39545, 11, 7, '新品上架', NULL, 10, NULL,1),
(13, '2014-04-11 05:57:50', 39545, 11, 8, '12', '12', 2, NULL,1),
(14, '2014-04-11 06:05:17', 39545, 11, 9, '中餐', NULL, 3, NULL,1);


INSERT INTO `cart` (`cart_id`, `_created_at`, `mp_user_id`, `store_id`, `wx_user_id`) VALUES
(15, '2014-02-09 23:38:22', 77369,  4, 'oPKG3uF9Ns5ROeMnmfFkyKOlEQMs'),
(16, '2014-02-09 23:38:37', 77369,  5, 'oPKG3uF9Ns5ROeMnmfFkyKOlEQMs'),
(17, '2014-02-09 23:38:44', 77369,  6, 'oPKG3uF9Ns5ROeMnmfFkyKOlEQMs'),
(18, '2014-02-09 23:38:49', 77369,  7, 'onbCxjvXUGkFBgbVJqcKk8TNkhIE'),
(20, '2014-03-12 01:54:34', 39545,  2, 'onbCxjhujYoCuQObRFJLYWAzFB1o');


INSERT INTO `album` (`album_id`, `_created_at`, `mp_user_id`,`community_id`, `carousel_id`, `title`, `cover_img`, `sort_no`, `comment`) VALUES
(1, '2013-11-21 04:19:50', 39545, 11,1, '传统美食', 'http://mp.weibotui.com/images/upload/93071/f15394bb61f6f6b4e7a7cb4c55873e23.jpg', 1, '阿道夫'),
(3, '2013-11-21 05:22:57',  39545, 11,1, '测试相册', 'http://mp.weibotui.com/images/upload/93071/8b7c7283b167453f709be357f72fd78a.jpg', 4, NULL),
(4, '2013-11-21 08:23:16', 39545, 11,1, '测试相册1', 'http://mp.weibotui.com/images/upload/93071/f91ea5f738230d052c3e943df49fca94.jpg', 2, NULL),
(5, '2013-11-21 08:23:32',  39545, 11,1, '测试相册2', 'http://mp.weibotui.com/images/upload/93071/0b0093e94f1e3e81ee6d42f767ed841d.jpg', 3, NULL);


INSERT INTO `carousel` (`carousel_id`, `_created_at`, `mp_user_id`, `community_id`,`title`, `comment`) VALUES
(3, '2013-11-21 05:18:04',  39545, 11, '我的生活', '我的生活照片'),
(6, '2013-11-25 10:16:14',  39545, 11,'宝宝世界', NULL),
(7, '2013-11-25 10:46:04',  39545, 11, '儿童成长', NULL);

INSERT INTO `channel` (`channel_id`, `mp_user_id`,`community_id`, `title`) VALUES
(5, 39545, 11,'物业公告'),
(8, 39545, 11,'宠物联盟'),
(9, 39545, 12,'宝宝世界'),
(10, 39545,12, '儿童成长');


INSERT INTO `mp_admin` (`mp_user_id`, `comment`, `username`) VALUES
( 39545, '崔广斌', 'cuiguangbin@kingcores.com'),
( 39545, '涂镥聪', 'tulucong@daojiafuwu.com'),
( 39545, '安振', 'anzhen@kingcores.com');


INSERT INTO `community_admin` ( `username`, `mp_user_id`, `community_id`, `power`, `order_notify_time`, `comment`) VALUES
('community@kingcores.com', 39545, 11, ',directory_r,directory_rw,channel_rw,channel_r,img_carousel_r,img_carousel_rw,store_rw,store_r,order_rw,order_r,phone_book_r,phone_book_rw,customer_specialist_r,customer_specialist_rw,receive_order_notify', NULL, NULL);




INSERT INTO `mp_user` (`_created_at`, `_updated_at`, `mp_user_id`, `mp_name`, `followed_content`, `comment`, `valid`, `api_id`, `token`, `location_x`, `location_y`, `max_vip_no`, `phone_no`, `app_id`, `app_secret`, `card_logo`, `card_background`, `card_list_directory`, `open_date`, `share_pic`) VALUES
('2013-11-26 03:14:38', '2013-11-26 03:14:38', 21817, '生活圈', NULL, '一级', 1, 'o1ui7luldh', 'yohtugprsigwgab', NULL, NULL, 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),

('2013-11-18 09:17:49', '2013-12-19 03:07:56', 39545, '广奥花园', '欢迎您！\r\n广州奥林匹克花园 · 中奥的美丽花园 \r\n如果您已是中奥的业主，可以通过手机进行安全验证，验证后可随时进入所有的业主尊享栏目。\r\n请点击【<a href="http://mp.weibotui.com/wx_user/user_info/index?wx_user_id=onbCxjiFx_ffSaExlwDQLwpp_LcY&mp_user_id=39545">进入安全验证</a>】\r\n如果您在验证的过程中遇见任何问题，请联系我们，我们的专员会立即与您联系为您服务。谢谢！\r\n\r\n', '广州奥林匹克花园', 1, 'tyghtqgo3r', 'stbdwagpevcxwwa', NULL, NULL, 116, NULL, 'wx4b28c6773f09b8b9', '14a02e2b6495f784eeb5192baadf36d8', NULL, NULL, 16, NULL, NULL);

insert  into `community` (`community_id`,mp_user_id,`name`, phone,  admin_email,admin_cc_email,is_virtual) values
(11,39545, '11号小区','13699251111','abc@abc.com,edf@abc.com','g@abc.com,k@abc.com',0),
(111,39545, '虚拟小区','13699251111','abc@abc.com,edf@abc.com','g@abc.com,k@abc.com',1),
(12,39545,'12号小区','13699251212','abc@abc.com,edf@abc.com','g@abc.com,k@abc.com',0);


INSERT INTO `super_admin` (`comment`, `username`) VALUES
( '崔广斌', 'cuiguangbin@kingcores.com'),
( 'anzhen', 'anzhen@kingcores.com'),
( 'tulucong', 'tulucong@daojiafuwu.com'),
('lizhicai', 'lizhicai@kingcores.com');


INSERT INTO `top_directory` (`top_directory_id`, `_created_at`, `mp_user_id`,`community_id`, `title`,`top_dir_no`) VALUES
(1, '0000-00-00 00:00:00',  39545, 11,'设置', 1),
(3, '0000-00-00 00:00:00',  39545,  11,'我的帐号',2),
(5, '2013-11-23 11:33:26',  39545,  11,'左邻右里',3),
(9, '2013-11-24 03:51:23',  39545,11, '生活圈',4),
(10, '2013-11-24 03:59:34',  39545,11, '健康养生',5);


INSERT INTO `wj_choice` (`wj_choice_id`, `_created_at`, `mp_user_id`, community_id,`wj_questionnaire_id`, `wj_question_id`, `content`, `comment`, `sort_no`, `select_times`) VALUES
(12, '2013-12-14 06:26:12', 39545, 11,6, 19, '顺丰速运', NULL, 1, 0),
(13, '2013-12-14 06:26:51', 39545, 11,6, 19, '圆通速递', NULL, 2, 0),
(14, '2013-12-14 06:27:11', 39545, 11,6, 19, '中通速递', NULL, 3, 0),
(15, '2013-12-14 06:27:34',  39545, 11,6, 19, '德邦物流', NULL, 4, 0),
(16, '2013-12-14 06:27:57',  39545, 11,6, 19, '申通E物流', NULL, 5, 0),
(17, '2013-12-14 06:28:28',  39545, 11,6, 19, 'EMS', NULL, 6, 0),
(18, '2013-12-14 06:28:44',  39545, 11,6, 19, '其他', NULL, 7, 0);

INSERT INTO `wj_question` (`wj_question_id`, `_created_at`, `mp_user_id`,community_id, `wj_questionnaire_id`, `content`, `comment`, `sort_no`, `question_type`) VALUES
(11, '2013-11-21 06:43:20',  39545, 11, 3, '建议或投诉：', NULL, 10, 'input_multiple'),
(13, '2013-12-13 02:43:53',  39545, 11,4, '需要的服务说明：', NULL, 10, 'input_multiple'),
(14, '2013-12-13 02:44:46',  39545, 11,4, '预约时间：（日期、时间）', NULL, 20, 'input_single'),
(15, '2013-12-13 07:15:10',  39545, 11,5, '美食标题', NULL, 1, 'input_single'),
(16, '2013-12-13 07:15:39',  39545, 11,5, '美食介绍', NULL, 2, 'input_multiple'),
(17, '2013-12-13 07:17:57',  39545, 11,5, '食材', NULL, 3, 'input_multiple'),
(18, '2013-12-13 07:19:29',  39545, 11,5, '做法', NULL, 4, 'input_multiple'),
(19, '2013-12-14 06:14:44',  39545, 11,6, '快递公司', NULL, 1, 'choice_single'),
(20, '2013-12-14 06:17:28',  39545, 11,6, '快递单号', NULL, 2, 'input_single');


INSERT INTO `wj_questionnaire` (`wj_questionnaire_id`, `_created_at`, `mp_user_id`,`community_id`, `title`, `head_desc`, `tail_desc`, `comment`, `customer_profile`) VALUES
(3, '2013-11-21 06:39:36', 39545,11, '给物业建议', '您好！欢迎给予我们宝贵的建议，您的建议将使我们提高物业管理的服务质量。', '再次感谢您的支持！我们将不断努力，为您提供满意的服务。谢谢！', NULL, 'optional'),
(4, '2013-12-13 02:42:27',  39545,11, '预约服务', '您好！欢迎使用在线预约服务，请在下方填写需要的服务说明然后提交发送给我们。', '感谢您的支持！我们的服务专员会立即与您联系，为您提供满意的服务。谢谢！', NULL, 'required'),
(5, '2013-12-13 07:14:37',  39545,11, '美食煮意投稿', '欢迎您分享美食菜谱 ！', '谢谢您的分享 ！ 我们会尽快把您的菜谱分享, 请密切关注。', NULL, 'optional'),
(6, '2013-12-14 06:09:31',  39545,11, '代收快递', '请把快递公司、快递单号及收件人信息填写清楚，然后提交。', '回来收取快递时，请提供上述信息。', NULL, 'required');


INSERT INTO `wj_user_answer` (`wj_user_answer_id`, `_created_at`, `mp_user_id`, `wx_user_id`,`community_id`, `name`, `gender`, `tel`, `birth`, `email`, `wj_questionnaire_id`, `answer`) VALUES
(2, '2013-12-20 08:35:17', 39545, 11,'onbCxjhujYoCuQObRFJLYWAzFB1o', '张冬生', 'female', '13552552253', '1986-12-02', 'zhangdshad@gmail.com', 3, '{"11":"\\u5efa\\u8bae\\u53d6\\u6d88"}');

INSERT INTO `mp`.`wx_sub_menu` (`wx_sub_menu_id`, `mp_user_id`, `access_authority`, `wx_menu_id`, `wx_menu_name`, `wx_menu_key`, `sort_no`, `content_value`, `url`, `wx_menu_type`, `content_type`) VALUES (NULL, '24352', '0', '453', 'fghdfg', '245', '0', 'lkdasl', 'wretwt', 'click', 'custom_text');



INSERT INTO `customer_specialist_group` (`customer_specialist_group_id`, `mp_user_id`, `community_id`, `group_name`, `comment`) VALUES
(1, 39545, 12, '客服经理', NULL);

INSERT INTO `mp`.`customer_specialist` (`customer_specialist_id`, `staff_id`, `mp_user_id`, `community_id`, `customer_specialist_group_id`, `name`, `phone`, `comment`, `valid`) VALUES (NULL, '12', '39545', '12', '1', '安振', '12365478963', NULL, '1');

INSERT INTO `mp`.`community_report` (`community_report_id`, `mp_user_id`, `community_id`, `ymd`, `zhuhu_count`, `yezhu_count`, `zhuhu_verify`, `yezhu_verify`) VALUES (NULL, '39545', '111', '20140512', '40', '40', '40', '40'), (NULL, '39545', '111', '20140513', '1', '1', '1', '1');

INSERT INTO `mp`.`house_member` (`house_member_id`, `mp_user_id`, `house_no`, `community_id`, `house_address`, `house_area`, `name`, `birthday`, `phone1`, `phone2`, `phone3`, `add_by`, `wx_user_id`, `comment`, `member_type`, `add_type`) VALUES (NULL, '39545', 'A-10', '111', '万达广场', '0.00', '大帅', '20140101', NULL, NULL, '12312312312', 'wuye', 'onbCxjhujYoCuQObRFJLYWAzFB1o', NULL, 'owner', 'wuye'), (NULL, '39545', 'A-9', '111', '上海', '0.00', '大叔', '20111010', NULL, '55555555555', NULL, 'wuye', 'onbCxjhujYoCuQObRFJLYWAzFB1o', NULL, 'owner', 'wuye');

INSERT INTO `mp`.`customer_specialist` (`customer_specialist_id`, `staff_id`, `mp_user_id`, `community_id`, `customer_specialist_group_id`, `name`, `phone`, `comment`, `valid`) VALUES (NULL, '13', '39545', '12', '1', '安振1', '', NULL, '1');

INSERT INTO `mp`.`report` (`report_id`, `mp_user_id`, `ymd`, `fans_total_count`, `net_increase_fans_count`, `followed_count`, `unfollowed_count`, `uv`, `pv`, `zhuhu_count`, `yezhu_count`, `zhuhu_verify`, `yezhu_verify`) VALUES (NULL, '21817', '20140515', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1');

INSERT INTO `mp`.`mp_article` (`mp_article_id`, `mp_user_id`, `community_id`, `title`, `share_desc`, `content`, `show_like`, `like_count`, `user_level`, `redirect`, `redirect_url`, `tag`) VALUES
 ('2', '39545', '12', '5', '5', '5', '5', '5', 'level_0', '0', '', ''),
 ('1', '39545', '12', '4', '4', '4', '4', '4', 'level_0', '0', '', '');

INSERT INTO `mp`.`order` (`order_id`, `mp_user_id`, `community_id`, `wx_user_id`, `comment`, `customer_name`, `tel`, `address`, `total_price`, `finish_time`, `cs_id`, `cs_group_id`, `status`) VALUES
( '123456', '39545', '12', 'onbCxjhujYoCuQObRFJLYWAzFB1o', '55', '55', '454545454545', '5555555', '55', '2014-05-19 00:00:00', '1', '1', 'default_status');


INSERT INTO `mp`.`customer_specialist_group` (`customer_specialist_group_id`, `mp_user_id`, `community_id`, `group_name`, `comment`) VALUES (NULL, '39545', '12', '不知道', NULL);



