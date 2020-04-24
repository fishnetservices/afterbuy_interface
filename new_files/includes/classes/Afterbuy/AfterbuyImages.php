<?php

class AfterbuyImages {

    protected $updatedPicturesCounter;

    public function __construct() {
        $this->updatedPicturesCounter = 0;
    }
    
    public function AfterbuyImages () {
        $afterbuy_images_query = xtc_db_query("SELECT afterbuy_images_id, images_name, images_url FROM ".TABLE_AFTERBUY_IMAGES." LIMIT 1000");

        if (xtc_db_num_rows($afterbuy_images_query) > 0) {
            ob_start();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            while($afterbuy_images_array = xtc_db_fetch_array($afterbuy_images_query)) {
                $this->get_afterbuy_image($afterbuy_images_array['images_url'],$afterbuy_images_array['images_name'],$ch);
                $delete_query = xtc_db_query("DELETE FROM ".TABLE_AFTERBUY_IMAGES." WHERE afterbuy_images_id= ".$afterbuy_images_array['afterbuy_images_id']);
                $this->updatedPicturesCounter++;
            }

            curl_close($ch);
            ob_end_clean();
        }

        $results_array = array('updatedItems' => $this->updatedPicturesCounter);

        return $results_array;
    }

    protected function get_afterbuy_image($products_image_url,$products_image_name,$ch) {        
        curl_setopt($ch, CURLOPT_URL, $products_image_url);
        curl_setopt($ch, CURLOPT_REFERER, $products_image_url);
        $result = curl_exec($ch);
        //file_put_contents(DIR_FS_CATALOG_ORIGINAL_IMAGES.$products_image_name, $result, FILE_APPEND);
        file_put_contents(DIR_WS_IMAGES.'product_images/original_images/'.$products_image_name, $result);

        require DIR_WS_CLASSES . 'Afterbuy/ImageProcessing/product_thumbnail_images.php';
        require DIR_WS_CLASSES . 'Afterbuy/ImageProcessing/product_info_images.php';
        require DIR_WS_CLASSES . 'Afterbuy/ImageProcessing/product_popup_images.php';
    }
}

?>