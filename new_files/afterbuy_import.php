<?php

ini_set('max_execution_time', 1800);

include_once 'includes/application_top.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyCall.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyImportCategories.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyImportProducts.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyUpdateStock.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyUpdateSoldItems.php';
require_once DIR_WS_CLASSES . 'Afterbuy/AfterbuyImages.php';

if (isset($_GET["action"])) {
    $action = trim($_GET["action"]);
}

if (isset($_GET["lastID"])) {
    $lastID = (int)$_GET["lastID"];
}

$expire_time = strtotime(date("Y-m-d H:i:s", strtotime("- 1 days")));
$last_runtime = strtotime(AFTERBUY_LAST_RUNTIME);

if ($expire_time >= $last_runtime && AFTERBUY_IMPORT_RUNNING == 'true') {
    xtc_db_query("UPDATE configuration SET configuration_value = 'false' WHERE configuration_key = 'AFTERBUY_IMPORT_RUNNING' ");
}

$running_status_query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key = 'AFTERBUY_IMPORT_RUNNING' ");
$running_status = xtc_db_fetch_array($running_status_query);

if ($running_status['configuration_value'] == 'false' && AFTERBUY_IMPORT_STATUS == 'true') {
    $AB = new Afterbuy(AFTERBUY_USERNAME, AFTERBUY_PASSWORD, AFTERBUY_PARTNER_ID, AFTERBUY_PARTNER_PASSWORD);
    if ($action == 'GetImages') {
        $import = new AfterbuyImages();
        $result = $import->AfterbuyImages();
        $AB->logResults($action, $result);
    } else if ($action == "GetStockInfo") {
        $stockInfoData = $AB->getStockInfoData();
        $afterbuy_stock_info_result = array();
        foreach ($stockInfoData as $stockInfoSingleData) {
            $afterbuy_data = $AB->makeCall($action, $stockInfoSingleData);
            $afterbuy_stock_info_result[] = $afterbuy_data;
        }
    } else if($action == "GetSoldItems") {
        $updateSoldItemsData = $AB->getUpdateSoldItemsData();
        if (!empty($updateSoldItemsData)) {
            $afterbuy_data = $AB->makeCall($action, $updateSoldItemsData, $lastID);
        }
    } else {
        $afterbuy_data = $AB->makeCall($action, NULL, $lastID);
    }
    if (!isset($afterbuy_stock_info_result) && is_array($afterbuy_data)) {
        $afterbuy_request_result = $afterbuy_data['Result'];
        ob_start();
        switch ($action){
            case "GetShopCatalogs":
                $import = new AfterbuyImportCategories($afterbuy_request_result);
                $result = $import->importCategories();
                $AB->logResults($action, $result);
                ob_clean();
                if (($result['rerun'])) {
                    xtc_redirect(xtc_href_link('afterbuy_import.php', 'action=GetShopCatalogs&lastID='.$result['lastCatalogID']));
                } else {
                    xtc_redirect(xtc_href_link('afterbuy_import.php', 'action=GetShopProducts'));
                }
                break;
            case "GetShopProducts":
                $import = new AfterbuyImportProducts($afterbuy_request_result);
                $result = $import->importProducts();
                $AB->logResults($action, $result);
                ob_clean();
                if (($result['rerun'])) {
                    xtc_redirect(xtc_href_link('afterbuy_import.php', 'action=GetShopProducts&lastID='.$result['lastProductID']));
                }
                break;
            case "GetSoldItems":
                $import = new AfterbuyUpdateOrderStatus($afterbuy_request_result);
                $result = $import->updateOrderStatuses();
                $AB->logResults($action, $result);
                ob_clean();
                if (($result['rerun'])) {
                    xtc_redirect(xtc_href_link('afterbuy_import.php', 'action=GetSoldItems&lastID='.$result['lastOrderID']));
                }
                break;
            default:
                $AB->logger->writeLog('Action not provided, import aborted');
                xtc_redirect(xtc_href_link('index.php'));
                break;
        }
        ob_clean();
        $AB->logger->writeLog("Call to Afterbuy finished successfully");
    } else {
        if (isset($afterbuy_stock_info_result) && is_array($afterbuy_stock_info_result)) {
            if ($action == "GetStockInfo") {
                $import = new AfterbuyUpdateStock($afterbuy_request_result);
                $import->updateStock();
            }
        }
    }
}
