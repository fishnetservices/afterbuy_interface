ALTER TABLE `admin_access` ADD `afterbuy_import` int(1) NOT NULL DEFAULT '0';
UPDATE `admin_access` SET `afterbuy_import` = '1' WHERE `customers_id` = '1';

INSERT INTO `configuration_group` (`configuration_group_id`, `configuration_group_title`, `configuration_group_description`, `sort_order`, `visible`) VALUES ('50', 'Afterbuy', 'Afterbuy Data And Configuration', '50', '1');

INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_STATUS', 'false', '50', '0', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_USERNAME', '', '50', '1', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PASSWORD', '', '50', '2', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PARTNER_ID', '', '50', '3', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PARTNER_PASSWORD', '', '50', '4', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_INACTIVE_STATUS', 'true', '50', '5', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_PRODUCTS_STATUS', 'active', '50', '6', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'active\', \'inactive\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_TEMPLATE', 'default', '50', '7', NULL, now(), NULL, 'ab_cfg_pull_down_product_info_templates(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_LISTING_TEMPLATE', 'default', '50', '8', NULL, now(), NULL, 'ab_cfg_pull_down_product_listing_templates(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_OPTIONS_TEMPLATE', 'default', '50', '9', NULL, now(), NULL, 'ab_cfg_pull_down_product_options_templates(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_DEFAULT_IMPORT_CATEGORIES_TEMPLATE', 'default', '50', '10', NULL, now(), NULL, 'ab_cfg_pull_down_categorie_listing_templates(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_1', 'none', '50', '11', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_2', 'none', '50', '12', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_3', 'none', '50', '13', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_4', 'none', '50', '14', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_5', 'none', '50', '15', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_6', 'none', '50', '16', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_7', 'none', '50', '17', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_8', 'none', '50', '18', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_9', 'none', '50', '19', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_FREE_FIELD_10', 'none', '50', '20', NULL, now(), NULL, 'ab_cfg_pull_down_free_fields(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_OVERWRITE_EXISTING_IMAGE', 'false', '50', '21', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PAID_ORDER_STATUS', '2', '50', '22', NULL, now(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_SHIPPED_ORDER_STATUS', '3', '50', '23', NULL, now(), 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_CATEGORY_FILTER_LEVEL_TO', '0', '50', '25', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PRODUCT_FILTER_LEVEL_FROM', '0', '50', '26', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PRODUCT_FILTER_LEVEL_TO', '0', '50', '27', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PRODUCT_ID_START', '0', '50', '28', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_PRODUCT_ID_END', '0', '50', '29', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_ADDITIONAL_IMAGES_LOCATION', 'images', '50', '30', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'memo\', \'images\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_DESCRIPTION_HEADER', 'false', '50', '31', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_DESCRIPTION_FOOTER', 'false', '50', '32', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_RESELLER_GROUPS', '', '50', '33', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_LAST_IMPORT_RUNTIME', now(), '50', '34', NULL, now(), NULL, NULL);
INSERT INTO `configuration` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES ('AFTERBUY_IMPORT_RUNNING', 'false', '50', '999', NULL, now(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

ALTER TABLE `categories`
ADD `afterbuy_cid` int(11) NULL AFTER `last_modified`,
ADD `afterbuy_pid` int(11) NULL AFTER `afterbuy_cid`;

ALTER TABLE `products`
ADD `ab_productsid` int(11) NULL AFTER `wholesaler_reorder`;

ALTER TABLE `products_attributes`
ADD `ab_productsid` int(11) NULL AFTER `attributes_ean`;

CREATE TABLE IF NOT EXISTS `afterbuy_images` (
  `products_id` int(11) NOT NULL,
  `images_url` varchar(512) NOT NULL,
  `images_name` varchar(254) NOT NULL,
  `afterbuy_images_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`afterbuy_images_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
