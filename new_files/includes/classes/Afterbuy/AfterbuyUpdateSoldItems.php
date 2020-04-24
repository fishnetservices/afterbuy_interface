<?php

class AfterbuyUpdateOrderStatus {
    
    protected $xmlData;
    
    protected $updatedOrdersCounter;


    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
        $this->updatedOrdersCounter = 0;
    }
    
    public function updateOrderStatuses() {
        
        $hasMoreItems = false;
        if (isset($this->xmlData["HasMoreItems"]) && $this->xmlData["HasMoreItems"] != 0) {
            $hasMoreItems = true;
            $lastOrderID = $this->xmlData["LastOrderID"];
        }
        $orders = $this->xmlData["Orders"];
        foreach ($orders as $ordersInternal) {
            foreach ($ordersInternal as $order) {
                $status = 0;
                
                if (isset($order["PaymentInfo"]["PaymentDate"]) && !empty($order["PaymentInfo"]["PaymentDate"])) {
                    if ((strtotime($order["PaymentInfo"]["PaymentDate"]) < time())) {
                        if ($order["PaymentInfo"]["FullAmount"] == $order["PaymentInfo"]["AlreadyPaid"]) {
                            $status = AFTERBUY_PAID_ORDER_STATUS;
                        }
                    }
                }
                
                if (isset($order["ShippingInfo"]["DeliveryDate"]) && (strtotime($order["ShippingInfo"]["DeliveryDate"]) < time())) {
                    $status = AFTERBUY_SHIPPED_ORDER_STATUS;
                }
                
                $order_id = $this->getInternalOrderID($order["OrderID"]);
                if ($order_id) {
                    if ($status != 0 && $status != $this->checkExistingOrderStatus($order_id)) {
                        $this->updateOrderStatus($order_id, $status);
                        $this->updatedOrdersCounter++;
                    }
                }
               
            }
        }

        $results_array = array('updatedItems' => $this->updatedOrdersCounter,
                               'rerun' => $hasMoreItems,
                               'lastOrderID' => $lastOrderID);

        return $results_array;
    }
    
    protected function updateOrderStatus($orders_id, $status) {
        
        $orders_array = array('orders_status' => $status,
                              'last_modified' => date('Y-m-d H:i:s'));
        
        xtc_db_perform(TABLE_ORDERS, $orders_array, 'update', 'orders_id = \''.$orders_id.'\'');
        
        $orders_status_history_array = array('orders_id' => $orders_id,
                                             'orders_status_id' => $status,
                                             'date_added' => date('Y-m-d H:i:s'));
        
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $orders_status_history_array);
    }
    
    protected function checkExistingOrderStatus($orders_id) {
        
        $order_status_query = xtc_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $orders_id . "' ");
        if (xtc_db_num_rows($order_status_query) == 1) {
            $order_status_array = xtc_db_fetch_array($order_status_query);
            
            return $order_status_array['orders_status'];
        } else {
            return 0;
        }
    }
    
    protected function getInternalOrderID($ab_oid) {
        $order_id_query = xtc_db_query("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE afterbuy_id = '" . $ab_oid . "' ");
        if (xtc_db_num_rows($order_id_query) == 1) {
            $order_id_array = xtc_db_fetch_array($order_id_query);
            
            return $order_id_array['orders_id'];
        } else {
            return false;
        }
    }
    
}
