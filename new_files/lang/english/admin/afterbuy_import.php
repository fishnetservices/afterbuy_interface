<?php

define('HEADING_TITLE', 'Afterbuy Import');
define('AFTERBUY_LOG_FILE_NOT_FOUND', 'Log file not yet created or import has not been run yet');

define('TEXT_INFO_AFTERBUY_IMPORT', 'Afterbuy Import configuration values overview');
define('AFTERBUY_IMPORT_STATUS_TITLE', 'Afterbuy Status');
define('AFTERBUY_USERNAME_TITLE', 'Afterbuy Username');
define('AFTERBUY_PASSWORD_TITLE', 'Afterbuy Password');
define('AFTERBUY_PARTNER_ID_TITLE', 'Afterbuy Partner ID');
define('AFTERBUY_PARTNER_PASSWORD_TITLE', 'Afterbuy Partner Password');
define('AFTERBUY_IMPORT_RUNNING_TITLE', 'Afterbuy Import Running?');
define('AFTERBUY_IMPORT_INACTIVE_STATUS_TITLE', 'Import inactive items?');
define('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_TEMPLATE_TITLE', 'Product info template for imported products');
define('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_LISTING_TEMPLATE_TITLE', 'Product listing template for imported products');
define('AFTERBUY_DEFAULT_IMPORT_PRODUCTS_OPTIONS_TEMPLATE_TITLE', 'Product options template for imported products');
define('AFTERBUY_DEFAULT_IMPORT_CATEGORIES_TEMPLATE_TITLE', 'Categories template for imported categories');
for ($i=0;$i<=10;$i++) {
    define('AFTERBUY_FREE_FIELD_'.$i.'_TITLE', 'Value for Afterbuy free field '.$i);
}
define('AFTERBUY_ORDER_STATUS_UPDATE_INTERVAL_TITLE', 'At which interval is update for order status called (days)');
define('AFTERBUY_OVERWRITE_EXISTING_IMAGE_TITLE', 'Are existing images overwritten while products are updated?');
define('AFTERBUY_PAID_ORDER_STATUS_TITLE', 'Order status for paid orders');
define('AFTERBUY_SHIPPED_ORDER_STATUS_TITLE', 'Order status for shipped orders');
define('AFTERBUY_FILTER_LEVEL_TITLE', 'Afterbuy filter level');

define('BUTTON_START_IMPORT', 'Start Import');
define('BUTTON_START_ORDER_IMPORT', 'Start Order Status Update');
define('AFTERBUY_INSTALLER_FOLDER_EXISTS', '<font color="#FF0000">Directory "AfterbuyInstaller" exists in the shop root, please delete it for security reasons!</font>');

define('META_TITLE_TITLE', 'Meta Title');
define('ORDER_DESCRIPTION_TITLE', 'Order Description');
define('AFTERBUY_CATEGORY_FILTER_LEVEL_TO_TITLE', 'Start filter level for categories');
define('AFTERBUY_CATEGORY_FILTER_LEVEL_FROM_TITLE', 'End filter level for categories');
define('AFTERBUY_PRODUCT_FILTER_LEVEL_TO_TITLE', 'Start filter level for products');
define('AFTERBUY_PRODUCT_FILTER_LEVEL_FROM_TITLE', 'End filter level for products');
define('AFTERBUY_PRODUCT_ID_START_TITLE', 'Start import from this product ID (0 - no limit)');
define('AFTERBUY_PRODUCT_ID_END_TITLE', 'End import from this product ID (0 - no limit)');
define('AFTERBUY_IMPORT_PRODUCTS_STATUS_TITLE', 'Status of imported products');
define('AFTERBUY_ADDITIONAL_IMAGES_LOCATION_TITLE', 'Location of data where additional images are stored');
define('AFTERBUY_IMPORT_SUCCESS_MESSAGE', 'Successfully import!');
define('AFTERBUY_IMPORT_RESELLER_GROUPS_TITLE', 'Reseller group IDs');
define('AFTERBUY_IMPORT_RESELLER_GROUPS_DESC', 'Divide IDs with comma');

define('BUTTON_GET_IMAGES', 'Import images');
define('AFTERBUY_LAST_IMPORT_RUNTIME_TITLE', 'Date and time of last import run');
define('AFTERBUY_LAST_IMPORT_RUNTIME_DESC', '');

?>
