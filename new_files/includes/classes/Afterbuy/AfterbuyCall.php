<?php

require_once('Helper/SimpleXMLExtended.php');
require_once('Helper/Logger.php');

class Afterbuy {
    
    /*
     * Afterbuy Username
     */
    protected $afterbuyUsername;
    
    /*
     * Afterbuy Password
     */
    protected $afterbuyPassword;
    
    /*
     * Afterbuy Partner ID
     */
    protected $afterbuyPartnerID;
    
    /*
     * Afterbuy Partner Password
     */
    protected $afterbuyPartnerPassword;
    
    /*
     * Afterbuy XML body
     */
    protected $xml;
    
    /*
     * Afterbuy Logger
     */
    public $logger;
    
    public function __construct($ab_username, $ab_password, $ab_partnerid, $ab_partner_password)
    {
        $this->afterbuyUsername = $ab_username;
        $this->afterbuyPassword = $ab_password;
        $this->afterbuyPartnerID = $ab_partnerid;
        $this->afterbuyPartnerPassword = $ab_partner_password;
        $this->xml = '';
        $this->logger = new Logger('afterbuy_log.txt');
    }
    
    /*
     * @param $action
     */
    public function makeCall($action, $additionalData = NULL, $lastID = NULL) {
        $this->logger->writeLog("Started Afterbuy Import - Action: ".$action);
        if (isset($_SESSION['customer_id'])) {
            $this->logger->writeLog('Import started by customer ID : ' . $_SESSION['customer_id']);
        } else {
            $this->logger->writeLog('Import started by automated cronjob');
        }
        $this->changeAfterbuyImportRunningStatus("true");
        $this->xml = $this->makeHeader($action, $additionalData, $lastID);

        $afterbuyUrl = "http://api.afterbuy.de/afterbuy/ABInterface.aspx";
        
        //Setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $afterbuyUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=" . $this->xml->asXML());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);
        curl_close($ch);
        
        //Convert the XML result into array
        $array_data = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        
        if ($array_data["CallStatus"] == "Success" && !empty($array_data["Result"])) {
            return $array_data;
        } else {
            $this->changeAfterbuyImportRunningStatus("false");
            $this->logger->writeLog("Connection unsuccessful - Error Code: ". $array_data["Result"]["ErrorList"]["Error"]["ErrorCode"] ." - Error Description: ". $array_data["Result"]["ErrorList"]["Error"]["ErrorLongDescription"]);
            return false;
        }

    }
    
    /*
     * @param $action
     * @param $data
     */
    protected function makeHeader($action, $data = NULL, $lastID = NULL) {
        $xml = new SimpleXMLExtended("<Request></Request>");
        $afterbuyGlobal = $xml->addChild("AfterbuyGlobal");
        $afterbuyGlobal->addChild("PartnerID", $this->afterbuyPartnerID);
        $afterbuyGlobal->addChild("PartnerPassword", $this->afterbuyPartnerPassword);
        $afterbuyGlobal->addChild("UserID", $this->afterbuyUsername);
        $afterbuyGlobal->addChild("UserPassword", $this->afterbuyPassword);
        $afterbuyGlobal->addChild("CallName", $action);
        $afterbuyGlobal->addChild("DetailLevel", 0);
        $afterbuyGlobal->addChild("ErrorLanguage", "EN");
        
        switch ($action){
            case "GetShopCatalogs":
                $this->buildGetShopCatalogsRequest($xml, $lastID);
                break;
            case "GetShopProducts":
                $this->buildGetShopProductsRequest($xml, $lastID);
                break;
            case "GetStockInfo":
                $this->buildGetStockInfoRequest($xml, $data);
                break;
            case "GetSoldItems":
                $this->buildGetSoldItemsRequest($xml, $data, $lastID);
                break;
            default:
                break;
        }
        return $xml;
    }
    
    /*
     * @param $xml
     */
    protected function buildGetShopCatalogsRequest($xml, $lastID = NULL) {
        $xml->addChild("MaxCatalogs", 200);
        if (AFTERBUY_CATEGORY_FILTER_LEVEL_TO >= 0) {
            $data_filter = $xml->addChild("DataFilter");
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "Level");
            $filter_values = $filter->addChild("FilterValues");
            $level_to = $filter_values->addChild("FilterValue", AFTERBUY_CATEGORY_FILTER_LEVEL_TO);
        }
       
        if (isset($lastID)) {
            if (!isset($data_filter)) {
                $data_filter = $xml->addChild("DataFilter");
            }
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "RangeID");
            $filter_values = $filter->addChild("FilterValues");
            $filter_value_from = $filter_values->addChild("ValueFrom", $lastID);
            $filter_value_to = $filter_values->addChild("ValueTo");
        }
    }
    
    /*
     * @param $xml
     */
    protected function buildGetShopProductsRequest($xml, $lastID = NULL) {
        $xml->addChild("MaxShopItems", 250);
        $xml->addChild("SuppressBaseProductRelatedData", 0);
        if (AFTERBUY_PRODUCT_FILTER_LEVEL_FROM >= 0 && AFTERBUY_PRODUCT_FILTER_LEVEL_TO >= AFTERBUY_PRODUCT_FILTER_LEVEL_FROM) {
            $data_filter = $xml->addChild("DataFilter");
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "Level");
            $filter_values = $filter->addChild("FilterValues");
            //$filter_value = $filter_values->addChild("FilterValue");
            $level_from = $filter_values->addChild("LevelFrom", AFTERBUY_PRODUCT_FILTER_LEVEL_FROM);
            $level_to = $filter_values->addChild("LevelTo", AFTERBUY_PRODUCT_FILTER_LEVEL_TO);
        }
        
        if (AFTERBUY_PRODUCT_ID_START > 0 && AFTERBUY_PRODUCT_ID_END > 0 && AFTERBUY_PRODUCT_ID_END > AFTERBUY_PRODUCT_ID_START && !isset($lastID)) {
            if (!isset($data_filter)) {
                $data_filter = $xml->addChild("DataFilter");
            }
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "RangeID");
            $filter_values = $filter->addChild("FilterValues");
            $filter_value_from = $filter_values->addChild("ValueFrom", AFTERBUY_PRODUCT_ID_START);
            $filter_value_to = $filter_values->addChild("ValueTo", AFTERBUY_PRODUCT_ID_END);
        }
        
        if (isset($lastID)) {
            if (!isset($data_filter)) {
                $data_filter = $xml->addChild("DataFilter");
            }
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "RangeID");
            $filter_values = $filter->addChild("FilterValues");
            $filter_value_from = $filter_values->addChild("ValueFrom", $lastID);
            if (AFTERBUY_PRODUCT_ID_END > 0 && AFTERBUY_PRODUCT_ID_END > $lastID) {
                $filter_value_to = $filter_values->addChild("ValueTo", AFTERBUY_PRODUCT_ID_END);
            } else {
                $filter_value_to = $filter_values->addChild("ValueTo", 0);
            }
        }
        
    }
    
    /*
     * @param $xml
     * @param $data
     */
    protected function buildGetStockInfoRequest($xml, $data = NULL) {
        $products = $xml->addChild("Products");
        $product = $products->addChild("Product");
        $product->addChild("ProductID", $data['afterbuy_pid']);
        $product->addChild("EAN", $data['products_ean']);
    }
    
    /*
     * @param $xml
     * @param $data
     */
    protected function buildGetSoldItemsRequest($xml, $data, $lastID = NULL) {
        $xml->addChild("MaxSoldItems", 250);
        $data_filter = $xml->addChild("DataFilter");
        $filter = $data_filter->addChild("Filter");
        $filter_name = $filter->addChild("FilterName", "DateFilter");
        $filter_values = $filter->addChild("FilterValues");
        $date_from = $filter_values->addChild("DateFrom", $data['interval_date']);
        $date_to = $filter_values->addChild("DateTo", $data['current_date']);
        //$filter_value = $filter_values->addChild("FilterValue", "AuctionEndDate");
        $filter_value = $filter_values->addChild("FilterValue", "ShippingDate");
        
        if(isset($lastID)){
            if (!isset($data_filter)) {
                $data_filter = $xml->addChild("DataFilter");
            }
            $filter = $data_filter->addChild("Filter");
            $filter_name = $filter->addChild("FilterName", "RangeID");
            $filter_values = $filter->addChild("FilterValues");
            $filter_value_from = $filter_values->addChild("ValueFrom", $lastID);
            $filter_value_to = $filter_values->addChild("ValueTo", 0);
        } 
    }
    
    protected function changeAfterbuyImportRunningStatus($value) {

        if ($value == 'true') {
            xtc_db_query("UPDATE configuration SET configuration_value = NOW() WHERE configuration_key = 'AFTERBUY_LAST_IMPORT_RUNTIME' "); 
        }
        
        xtc_db_query("UPDATE configuration SET configuration_value = '" . $value . "' WHERE configuration_key = 'AFTERBUY_IMPORT_RUNNING' ");
    }
    
    public function getStockInfoData() {
        $stock_info_data = array();
        $afterbuy_product_query = xtc_db_query("SELECT afterbuy_pid, products_ean FROM " . TABLE_PRODUCTS . " WHERE afterbuy_pid IS NOT NULL ");
        while($afterbuy_product_array = xtc_db_fetch_array($afterbuy_product_query)) {
            $stock_info_data[] = array("afterbuy_pid" => $afterbuy_product_array["afterbuy_pid"],
                                       "products_ean" => $afterbuy_product_array["products_ean"]);
        }
        return $stock_info_data;
    }
    
    public function getUpdateSoldItemsData() {
        $interval_date = date("d.m.Y H:i:s", strtotime("-".AFTERBUY_ORDER_STATUS_UPDATE_INTERVAL." days"));
        $current_date = date("d.m.Y H:i:s");
        $update_sold_items_data = array('interval_date' => $interval_date,
                                        'current_date' => $current_date);
        return $update_sold_items_data;
    }
    
    public function logResults($action, $result_array) {
        $this->logger->writeLog("Afterbuy Import action " . $action . " finished");
        if (isset($result_array) && is_array($result_array) && !empty($result_array)) {
            switch ($action){
                case "GetShopCatalogs":
                    if (isset($result_array["insertedItems"])) $this->logger->writeLog("Inserted Categories : " . $result_array["insertedItems"]);
                    if (isset($result_array["updatedItems"])) $this->logger->writeLog("Updated Categories : " . $result_array["updatedItems"]);
                    break;
                case "GetShopProducts":
                    if (isset($result_array["insertedItems"])) $this->logger->writeLog("Inserted Products : " . $result_array["insertedItems"]);
                    if (isset($result_array["updatedItems"])) $this->logger->writeLog("Updated Products : " . $result_array["updatedItems"]);
                    break;
                case "GetSoldItems":
                    if (isset($result_array["updatedItems"])) $this->logger->writeLog("Updated Order Statuses : " . $result_array["updatedItems"]);
                    break;
                case "GetImages":
                    if (isset($result_array["updatedItems"])) $this->logger->writeLog("Updated Product Images : " . $result_array["updatedItems"]);
                    break;
                default:
                    break;
            }
        }
        
        $this->changeAfterbuyImportRunningStatus("false");
    }
}

?>