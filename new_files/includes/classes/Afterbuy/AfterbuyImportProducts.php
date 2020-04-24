<?php

class AfterbuyImportProducts {
    
    protected $xmlData;
    
    protected $accepted_products_image_files_extensions;
    
    protected $accepted_products_image_files_mime_types;
    
    protected $insertedProductsCounter;
    
    protected $updatedProductsCounter;


    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
        $this->accepted_products_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
        $this->accepted_products_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");
        $this->insertedProductsCounter = 0;
        $this->updatedProductsCounter = 0;
    }
    
    public function importProducts() {
        $hasMoreProducts = false;
        if (isset($this->xmlData["HasMoreProducts"]) && $this->xmlData["HasMoreProducts"] != 0) {
            $hasMoreProducts = true;
            $lastProductID = $this->xmlData["LastProductID"];
        }
        $products = $this->xmlData["Products"];
        foreach ($products as $product) {
            foreach ($product as $prod) {
                $product_exists = $this->checkExistingAfterbuyProduct($prod["ProductID"]);
                if (!$product_exists) {
                    $result = $this->importProduct($prod);
                    if ($result) {
                        $this->insertedProductsCounter++;
                    }
                } else {
                    $this->updateProduct($prod, $product_exists);
                    $this->updatedProductsCounter++;
                }
            }
        }
        
        $results_array = array('insertedItems' => $this->insertedProductsCounter,
                               'updatedItems' => $this->updatedProductsCounter,
                               'rerun' => $hasMoreProducts,
                               'lastProductID' => $lastProductID);
        
        return $results_array;
    }
    
    protected function checkExistingAfterbuyProduct($ab_productsid) {
        $check_existing_query = xtc_db_query("SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE ab_productsid = '" . $ab_productsid . "' ");
        if (xtc_db_num_rows($check_existing_query) > 0) {
            $check_existing_array = xtc_db_fetch_array($check_existing_query);
            return $check_existing_array["products_id"];
        } else {
            return false;
        }
    }
    
    protected function importProduct($product_data) {
        if ((AFTERBUY_IMPORT_INACTIVE_STATUS == 'false' && $product_data["Quantity"] <= 0)) {
            return false;
        }
        
        if ($product_data["BaseProductFlag"] == '3') {
            $attribute_id = $this->checkAttributeByBaseProduct($product_data["ProductID"], $product_data["BaseProducts"]["BaseProduct"]["BaseProductID"]);
            if ($attribute_id) {
                $this->updateAttributeData($attribute_id, $product_data);    
            } 

            return false;
        }
        
        $price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["SellingPrice"])));
        
        $status = 0;
        if((AFTERBUY_IMPORT_PRODUCTS_STATUS == 'active' && $product_data["Quantity"] > 0)){
            $status = 1;
        }
        
        $import_data_array = array('products_ean' => !empty($product_data["EAN"]) ? $product_data["EAN"] : NULL,
                              'products_quantity' => $product_data["Quantity"],
                              'products_shippingtime' => !empty($product_data['DeliveryTime']) ? $this->getProductsShippingTime($product_data['DeliveryTime']) : DEFAULT_SHIPPING_STATUS_ID,
                              'products_model' => $product_data["Anr"],
                              'products_sort' => $product_data["Position"],
                              'products_image_title' => '',
                              'products_image_alt' => '',
                              'products_price' => $price / ((100 + $product_data["TaxRate"]) / 100),
                              'products_date_added' => date('Y-m-d H:i:s'),
                              'products_last_modified' => date('Y-m-d H:i:s'),
                              'products_date_available' => NULL,
                              'products_weight' => number_format(floatval(str_replace(',', '.', str_replace('.', '', $product_data["Weight"]))), 3),
                              'products_status' => $status, //(AFTERBUY_IMPORT_PRODUCTS_STATUS == 'active') ? 1 : 0,
                              'products_tax_class_id' => $this->getTaxClassId($product_data["TaxRate"]),
                              'product_template' => AFTERBUY_DEFAULT_IMPORT_PRODUCTS_TEMPLATE,
                              'options_template' => AFTERBUY_DEFAULT_IMPORT_PRODUCTS_OPTIONS_TEMPLATE,
                              'manufacturers_id' => (isset($product_data["ProductBrand"]) && !empty($product_data["ProductBrand"])) ? $this->getManufacturersId($product_data["ProductBrand"]) : NULL,
                              'products_manufacturers_model' => !empty($product_data["ManufacturerPartNumber"]) ? $product_data["ManufacturerPartNumber"] : NULL,
                              'products_ordered' => 0,
                              'products_fsk18' => 0,
                              'products_vpe' => !empty($product_data["UnitOfQuantity"]) ? $this->getProductsVPEID($product_data["UnitOfQuantity"]) : 0,
                              'products_vpe_status' => !empty($product_data["UnitOfQuantity"]) ? 1 : 0,
                              'products_vpe_value' => number_format(floatval(str_replace(',', '.', str_replace('.', '', $product_data["BasepriceFactor"]))), 4),
                              'products_startpage' => 0,
                              'products_startpage_sort' => 0,
                              'wholesaler_id' => 0,
                              'wholesaler_reorder' => 0,
                              'ab_productsid' => $product_data["ProductID"]);
                          
        $customers_statuses_array = xtc_get_customers_statuses();
        $permission_array = array();
        for ($i = 0, $n = count($customers_statuses_array); $i <= $n; $i ++) {
            if (isset($customers_statuses_array[$i]['id'])) {
                $permission_array = array_merge($permission_array, array('group_permission_'.$customers_statuses_array[$i]['id'] => 1));
            }
        }
        
        $import_array = array_merge($import_data_array, $permission_array);
        
        xtc_db_perform(TABLE_PRODUCTS, $import_array);
        $products_id = xtc_db_insert_id();

        if (AFTERBUY_IMPORT_RESELLER_GROUPS != '' && isset($product_data["DealerPrice"]) && !empty($product_data["DealerPrice"])) {
          $dealer_price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["DealerPrice"])));
          if ($dealer_price > 0) {
            $groups_array = explode(',', AFTERBUY_IMPORT_RESELLER_GROUPS);
            foreach ($groups_array as $group) {
              $group = trim($group);
              if (is_numeric($group)) {
                $group_price_array = array('products_id' => $products_id,
                                           'quantity' => 1,
                                           'personal_offer' => $dealer_price);

                xtc_db_perform(TABLE_PERSONAL_OFFERS_BY.$group, $group_price_array);
              }
            }
          }
        }

        if (isset($product_data["ImageLargeURL"]) && !empty($product_data["ImageLargeURL"])) { 
            $products_image = $this->processProductImage($product_data["ImageLargeURL"], $products_id);
        }
        
        xtc_db_query("UPDATE " . TABLE_PRODUCTS . " SET products_image = '" . $products_image . "' WHERE products_id = '" . $products_id . "' ");
        
        if (AFTERBUY_ADDITIONAL_IMAGES_LOCATION == 'images') {
            if (isset($product_data["ProductPictures"]) && !empty($product_data["ProductPictures"])) {
                $is_array = true;
                foreach ($product_data["ProductPictures"] as $product_picture) {

                  if (is_array($product_picture)) {

                    $products_additional_image_array = array('products_id' => $products_id,
                                                             'image_nr' => $product_picture["Nr"],
                                                             'image_name' => $this->processProductImage($product_picture["Url"], $products_id, $product_picture["Nr"]),
                                                             'image_alt' => !empty($product_picture["Alt"]) ? $product_picture["Alt"] : '');
                  } else {
                    $is_array = false;
                    $products_additional_image_array = array('products_id' => $product_exists,
                                                             'image_nr' => $product_data["ProductPictures"]["ProductPicture"]["Nr"],
                                                             'image_name' => $this->processProductImage($product_data["ProductPictures"]["ProductPicture"]["Url"], $product_exists, $product_data["ProductPictures"]["ProductPicture"]["Nr"]),
                                                             'image_alt' => !empty($product_data["ProductPictures"]["ProductPicture"]["Alt"]) ? $product_data["ProductPictures"]["ProductPicture"]["Alt"] : '');
                  }

                    xtc_db_perform(TABLE_PRODUCTS_IMAGES, $products_additional_image_array);

                    if (!$is_array) {
                      break;
                    }
                }
            }
        } elseif (AFTERBUY_ADDITIONAL_IMAGES_LOCATION == 'memo') {
            if (isset($product_data["Memo"]) && !empty($product_data["Memo"]) && !is_array($product_data["Memo"])) {
                $images_array = explode('|', $product_data["Memo"]);
                $image_nr_counter = 1;
                foreach ($images_array as $image) {
                    $products_additional_image_array = array('products_id' => $products_id,
                                                             'image_nr' => $image_nr_counter,
                                                             'image_name' => $this->processProductImage(trim($image), $products_id, $image_nr_counter),
                                                             'image_alt' => '');

                    xtc_db_perform(TABLE_PRODUCTS_IMAGES, $products_additional_image_array);
                    $image_nr_counter++;
                }
            }
        }
        
        $languages = get_active_language_ids();
        $free_fields = $this->getFreeFields();
        foreach ($languages AS $lang) {
            $description = (isset($product_data["Description"]) && !is_array($product_data["Description"])) ? $product_data["Description"] : '';

            if (AFTERBUY_IMPORT_DESCRIPTION_HEADER == 'true' && isset($product_data["HeaderDescriptionValue"]) && !is_array($product_data["HeaderDescriptionValue"])) {
              $description = $product_data["HeaderDescriptionValue"] . $description;
            }

            if (AFTERBUY_IMPORT_DESCRIPTION_FOOTER == 'true' && isset($product_data["FooterDescriptionValue"]) && !is_array($product_data["FooterDescriptionValue"])) {
              $description = $description . $product_data["FooterDescriptionValue"];
            }

            $import_description_array = array('products_id' => $products_id,
                                              'language_id' => $lang["id"],
                                              'products_name' => $product_data["Name"],
                                              //'products_description' => (isset($product_data["Description"]) && !is_array($product_data["Description"])) ? $product_data["Description"] : '',
                                              'products_description' => $description,
                                              'products_short_description' => (isset($product_data["ShortDescription"]) && !is_array($product_data["ShortDescription"])) ? $product_data["ShortDescription"] : '',
                                              'products_meta_title' => (isset($free_fields["meta_title"]) && !is_array($product_data["FreeValue".$free_fields["meta_title"]])) ? $product_data["FreeValue".$free_fields["meta_title"]] : '',
                                              'products_meta_description' => (isset($free_fields["meta_description"]) && !is_array($product_data["FreeValue".$free_fields["meta_description"]])) ? $product_data["FreeValue".$free_fields["meta_description"]] : '',
                                              'products_meta_keywords' => '',
                                              'products_url' => NULL,
                                              'products_viewed' => 0,
                                              'products_order_description' => (isset($free_fields["order_description"]) && !is_array($product_data["FreeValue".$free_fields["order_description"]])) ? $product_data["FreeValue".$free_fields["order_description"]] : '');

            xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $import_description_array);
            
            if (isset($product_data["BaseProducts"]) && $product_data["BaseProductFlag"] == '1') {
                foreach ($product_data["BaseProducts"]["BaseProduct"] as $baseProduct) {
                    $this->addProductAttribute($baseProduct, $products_id, $lang["id"]);
                }
            }
        }
        
        // delete previous entries in products_to_categories for current product
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = '" . $products_id . "' ");
 
        if (isset($product_data["Catalogs"])) {
            foreach ($product_data["Catalogs"] as $catalog) {
                if (!is_array($catalog)) {
                    $category_id = ($catalog == 0) ? $catalog : $this->getShopCategoryId($catalog);
                    xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('" . $category_id . "', '" . $products_id . "') ");
                } else {
                    foreach ($catalog as $catalogID) {
                        $category_id = ($catalogID == 0) ? $catalogID : $this->getShopCategoryId($catalogID);
                        xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('" . $category_id . "', '" . $products_id . "') ");
                    }
                }
            }
        } else {
            // Set to category 0 if no catalogs were received
            xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('0', '" . $products_id . "') ");
        }
        
        return $products_id;
    }
    
    protected function updateProduct($product_data, $product_exists) {
      
        $price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["SellingPrice"])));        
        $status = 0;
        
        if((AFTERBUY_IMPORT_PRODUCTS_STATUS == 'active' && $product_data["Quantity"] > 0)){
            $status = 1;
        }
        
        $import_array = array('products_ean' => !empty($product_data["EAN"]) ? $product_data["EAN"] : NULL,
                      'products_quantity' => $product_data["Quantity"],
                      'products_shippingtime' => !empty($product_data['DeliveryTime']) ? $this->getProductsShippingTime($product_data['DeliveryTime']) : DEFAULT_SHIPPING_STATUS_ID,
                      'products_model' => $product_data["Anr"],
                      'products_sort' => $product_data["Position"],
                      'products_price' => $price / ((100 + $product_data["TaxRate"]) / 100),
                      'products_last_modified' => date('Y-m-d H:i:s'),
                      'products_weight' => number_format(floatval(str_replace(',', '.', str_replace('.', '', $product_data["Weight"]))), 3),
                      'products_status' => $status,
                      'products_tax_class_id' => $this->getTaxClassId($product_data["TaxRate"]),
                      'manufacturers_id' => (isset($product_data["ProductBrand"]) && !empty($product_data["ProductBrand"])) ? $this->getManufacturersId($product_data["ProductBrand"]) : NULL,
                      'products_manufacturers_model' => !empty($product_data["ManufacturerPartNumber"]) ? $product_data["ManufacturerPartNumber"] : NULL,
                      'products_vpe' => !empty($product_data["UnitOfQuantity"]) ? $this->getProductsVPEID($product_data["UnitOfQuantity"]) : 0,
                      'products_vpe_status' => !empty($product_data["UnitOfQuantity"]) ? 1 : 0,
                      'products_vpe_value' => number_format(floatval(str_replace(',', '.', str_replace('.', '', $product_data["BasepriceFactor"]))), 4),
                      'ab_productsid' => $product_data["ProductID"]);

        if (AFTERBUY_IMPORT_RESELLER_GROUPS != '' && isset($product_data["DealerPrice"]) && !empty($product_data["DealerPrice"])) {
          $dealer_price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["DealerPrice"])));
          if ($dealer_price > 0) {
            $groups_array = explode(',', AFTERBUY_IMPORT_RESELLER_GROUPS);
            foreach ($groups_array as $group) {
              $group = trim($group);
              if (is_numeric($group)) {
                // delete previous entry
                xtc_db_query("DELETE FROM ".TABLE_PERSONAL_OFFERS_BY.$group. " WHERE products_id = '" . $product_exists . "'");

                $group_price_array = array('products_id' => $product_exists,
                                           'quantity' => 1,
                                           'personal_offer' => $dealer_price);

                xtc_db_perform(TABLE_PERSONAL_OFFERS_BY.$group, $group_price_array);
              }
            }
          }
        }

        if (AFTERBUY_OVERWRITE_EXISTING_IMAGE == 'true') {
            $import_array['products_image'] = (isset($product_data["ImageLargeURL"]) && !empty($product_data["ImageLargeURL"])) ? $this->processProductImage($product_data["ImageLargeURL"], $product_exists) : NULL;
        }

        xtc_db_perform(TABLE_PRODUCTS, $import_array, 'update', 'products_id = \''.$product_exists.'\'');
        
        if (AFTERBUY_OVERWRITE_EXISTING_IMAGE == 'true') {
            // delete previous entries for additional product images
            xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_IMAGES . " WHERE products_id = '" . $product_exists . "' ");
            
            if (AFTERBUY_ADDITIONAL_IMAGES_LOCATION == 'images') {
                if (isset($product_data["ProductPictures"]) && !empty($product_data["ProductPictures"])) {
                    $is_array = true;
                    foreach ($product_data["ProductPictures"]["ProductPicture"] as $product_picture) {

                      if (is_array($product_picture)) {

                        $products_additional_image_array = array('products_id' => $product_exists,
                                                                 'image_nr' => $product_picture["Nr"],
                                                                 'image_name' => $this->processProductImage($product_picture["Url"], $product_exists, $product_picture["Nr"]),
                                                                 'image_alt' => !empty($product_picture["Alt"]) ? $product_picture["Alt"] : '');
                      } else {
                        $is_array = false;
                        $products_additional_image_array = array('products_id' => $product_exists,
                                                                 'image_nr' => $product_data["ProductPictures"]["ProductPicture"]["Nr"],
                                                                 'image_name' => $this->processProductImage($product_data["ProductPictures"]["ProductPicture"]["Url"], $product_exists, $product_data["ProductPictures"]["ProductPicture"]["Nr"]),
                                                                 'image_alt' => !empty($product_data["ProductPictures"]["ProductPicture"]["Alt"]) ? $product_data["ProductPictures"]["ProductPicture"]["Alt"] : '');
                      }

                        xtc_db_perform(TABLE_PRODUCTS_IMAGES, $products_additional_image_array);

                        if (!$is_array) {
                          break;
                        }

                    }
                }
            } elseif (AFTERBUY_ADDITIONAL_IMAGES_LOCATION == 'memo') {
                if (isset($product_data["Memo"]) && !empty($product_data["Memo"]) && !is_array($product_data["Memo"])) {
                    $images_array = explode('|', $product_data["Memo"]);
                    $image_nr_counter = 1;
                    foreach ($images_array as $image) {
                        $products_additional_image_array = array('products_id' => $product_exists,
                                                                 'image_nr' => $image_nr_counter,
                                                                 'image_name' => $this->processProductImage(trim($image), $product_exists, $image_nr_counter),
                                                                 'image_alt' => '');

                        xtc_db_perform(TABLE_PRODUCTS_IMAGES, $products_additional_image_array);
                        $image_nr_counter++;
                    }
                }
            }
        }
            
        $languages = get_active_language_ids();
        $free_fields = $this->getFreeFields();
        foreach ($languages AS $lang) {
            $description = (isset($product_data["Description"]) && !is_array($product_data["Description"])) ? $product_data["Description"] : '';

            if (AFTERBUY_IMPORT_DESCRIPTION_HEADER == 'true' && isset($product_data["HeaderDescriptionValue"]) && !is_array($product_data["HeaderDescriptionValue"])) {
              $description = $product_data["HeaderDescriptionValue"] . $description;
            }

            if (AFTERBUY_IMPORT_DESCRIPTION_FOOTER == 'true' && isset($product_data["FooterDescriptionValue"]) && !is_array($product_data["FooterDescriptionValue"])) {
              $description = $description . $product_data["FooterDescriptionValue"];
            }

            $import_description_array = array('products_name' => $product_data["Name"],
                                              //'products_description' => (isset($product_data["Description"]) && !is_array($product_data["Description"])) ? $product_data["Description"] : '',
                                              'products_description' => $description,
                                              'products_short_description' => (isset($product_data["ShortDescription"]) && !is_array($product_data["ShortDescription"])) ? $product_data["ShortDescription"] : '',
                                              'products_meta_title' => (isset($free_fields["meta_title"]) && !is_array($product_data["FreeValue".$free_fields["meta_title"]])) ? $product_data["FreeValue".$free_fields["meta_title"]] : '',
                                              'products_meta_description' => (isset($free_fields["meta_description"]) && !is_array($product_data["FreeValue".$free_fields["meta_description"]])) ? $product_data["FreeValue".$free_fields["meta_description"]] : '',
                                              'products_order_description' => (isset($free_fields["order_description"]) && !is_array($product_data["FreeValue".$free_fields["order_description"]])) ? $product_data["FreeValue".$free_fields["order_description"]] : '');

            xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION, $import_description_array, 'update', 'products_id = \''.$product_exists.'\' AND language_id = \''.$lang["id"].'\' ');
            
            if (isset($product_data["BaseProducts"]) && $product_data["BaseProductFlag"] == '1') {
                foreach ($product_data["BaseProducts"]["BaseProduct"] as $baseProduct) {
                    $this->updateAttributeDataFromBase($baseProduct, $product_exists, $lang["id"]);
                }
            }
        }
        
        
        
        // delete previous entries in products_to_categories for current product
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = '" . $product_exists . "' ");
        
        if (isset($product_data["Catalogs"])) {
        // insert all connections for categories and products
            foreach ($product_data["Catalogs"] as $catalog) {
                if (!is_array($catalog)) {
                    $category_id = ($catalog == 0) ? $catalog : $this->getShopCategoryId($catalog);
                    xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('" . $category_id . "', '" . $product_exists . "') ");
                } else {
                    foreach ($catalog as $catalogID) {
                        $category_id = ($catalogID == 0) ? $catalogID : $this->getShopCategoryId($catalogID);
                        xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('" . $category_id . "', '" . $product_exists . "') ");
                    }
                }
            }
        } else {
            // Set to category 0 if no catalogs were received
            xtc_db_query("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (categories_id, products_id) VALUES ('0', '" . $product_exists . "') ");
        }
    }
    
    protected function getTaxClassId($tax_rate) {
        if ($tax_rate > 0) {
        $formatted_tax_rate = number_format($tax_rate, 4);
        $tax_rate_query = xtc_db_query("SELECT tax_class_id FROM " . TABLE_TAX_RATES . " WHERE tax_rate = '" . $formatted_tax_rate . "' ");
        $tax_rate_query_array = xtc_db_fetch_array($tax_rate_query);
        
        return $tax_rate_query_array["tax_class_id"]; 
            
        } else {
            return 0;
        }
    }
    
    protected function getShopCategoryId($afterbuy_cid) {
        $check_existing_query = xtc_db_query("SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE afterbuy_cid = '" . $afterbuy_cid . "' ");
        if (xtc_db_num_rows($check_existing_query) > 0) {
            $check_existing_array = xtc_db_fetch_array($check_existing_query);
            return $check_existing_array["categories_id"];
        } else {
            return 0;
        }
    }
    
    protected function addProductAttribute($attributeData, $products_id, $language_id) {
        $attributeExists = $this->checkAttributeByName($attributeData["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationName"], $language_id);
        $attributeValueExists = $this->checkAttributeValueByName($attributeData["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationValue"], $language_id);
        if ($attributeExists) {
            $attribute_id = $attributeExists;
        } else {
            $attribute_id = $this->createAttributeOption($attributeData["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationName"]);
        }
        if ($attributeValueExists) {
            $attribute_value_id = $attributeValueExists;
        } else {
            $attribute_value_id = $this->createAttributeOptionValue($attributeData["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationValue"], $attribute_id);
        }
        if ($attribute_id != 0 && $attribute_value_id != 0) {
            if (!$this->checkExistingAttributeByBaseProduct($products_id, $attributeData["BaseProductID"])) {
                $this->assignAttributeToProduct($attribute_id, $attribute_value_id, $products_id, $attributeData["BaseProductsRelationData"], $attributeData["BaseProductID"]);
            }
        }
    }
    
    protected function checkAttributeByName($attributeName, $language_id) {
        $check_existing_query = xtc_db_query("SELECT products_options_id FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_name = '" . $attributeName . "' AND language_id = '" . $language_id . "' ");
        if (xtc_db_num_rows($check_existing_query) > 0) {
            $check_existing_array = xtc_db_fetch_array($check_existing_query);
            return $check_existing_array["products_options_id"];
        } else {
            return false;
        }
    }
    
    protected function checkAttributeValueByName($attributeValueName, $language_id) {
        $check_existing_query = xtc_db_query("SELECT products_options_values_id FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_name = '" . $attributeValueName . "' AND language_id = '" . $language_id . "' ");
        if (xtc_db_num_rows($check_existing_query) > 0) {
            $check_existing_array = xtc_db_fetch_array($check_existing_query);
            return $check_existing_array["products_options_values_id"];
        } else {
            return false;
        }
    }
    
    protected function createAttributeOption($attributeName) {
        $attributeNameTrimmed = trim($attributeName);
        $max_id_query = xtc_db_query("SELECT max(products_options_id) + 1 as next_id FROM " . TABLE_PRODUCTS_OPTIONS);
        $max_id_values = xtc_db_fetch_array($max_id_query);
        $next_id = $max_id_values['next_id'];
        if ($next_id < 1) { 
            $next_id = 1;
        }
        $languages = get_active_language_ids();
        foreach ($languages AS $lang) {
            $products_options_array = array('products_options_id' => $next_id,
                                            'language_id' => $lang["id"],
                                            'products_options_name' => $attributeNameTrimmed,
                                            'products_options_sortorder' => 0);
            
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $products_options_array);
        }

        return $next_id;
    }
    
    protected function createAttributeOptionValue($attributeValueName, $attribute_id) {
        $attributeValueNameTrimmed = trim($attributeValueName);
        $max_values_id_query = xtc_db_query("SELECT max(products_options_values_id) + 1 as next_id FROM " . TABLE_PRODUCTS_OPTIONS_VALUES);
        $max_values_id_values = xtc_db_fetch_array($max_values_id_query);
        $next_id = $max_values_id_values['next_id'];
        if ($next_id < 1) { 
            $next_id = 1;
        }
        $languages = get_active_language_ids();
        foreach ($languages AS $lang) {
            $products_options_array = array('products_options_values_id' => $next_id,
                                            'language_id' => $lang["id"],
                                            'products_options_values_name' => $attributeValueNameTrimmed);
            
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $products_options_array);
        }
        
        $values_to_options = array('products_options_id' => $attribute_id,
                                   'products_options_values_id' => $next_id);
        
        xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS, $values_to_options);
        
        return $next_id;
    }
    
    protected function checkAttributeByBaseProduct($product_id, $base_product) {
        $base_product_internal = $this->checkExistingAfterbuyProduct($base_product);
        $attribute_query = xtc_db_query("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = '" . $base_product_internal . "' AND ab_productsid = '" . $product_id . "'");
        if (xtc_db_num_rows($attribute_query) > 0) {
            while ($attribute_array = xtc_db_fetch_array($attribute_query)) {
                return $attribute_array["products_attributes_id"];
            }
        } else {
            return false;
        }
    }
    
    protected function checkExistingAttributeByBaseProduct($product_id, $base_product) {
        $attribute_query = xtc_db_query("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = '" . $product_id . "' AND ab_productsid = '" . $base_product . "'");
        if (xtc_db_num_rows($attribute_query) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    protected function assignAttributeToProduct($attribute_id, $attribute_value_id, $products_id, $attribute_data, $base_product_id) {
        $attribute_array = array('products_id' => $products_id,
                                 'options_id' => $attribute_id,
                                 'options_values_id' => $attribute_value_id,
                                 'options_values_price' => number_format(0, 4),
                                 'price_prefix' => '+',
                                 'attributes_model' => '',
                                 'attributes_stock' => $attribute_data["BaseProductsRelationData"]['Quantity'],
                                 'options_values_weight' => number_format(0, 4),
                                 'weight_prefix' => '+',
                                 //'sortorder' => $attribute_data["eBayVariationData"]["Position"],
                                 'sortorder' => $attribute_data["BaseProductsRelationData"]['Position'],
                                 'attributes_ean' => NULL,
                                 'ab_productsid' => $base_product_id);
        
        xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attribute_array);
    }
    
    protected function updateAttributeData($products_attribute_id, $product_data) {
        $base_product_price = $this->checkProductsData('products_price', $product_data["BaseProducts"]["BaseProduct"]["BaseProductID"]);
        $base_product_weight = $this->checkProductsData('products_weight', $product_data["BaseProducts"]["BaseProduct"]["BaseProductID"]);
        
        $price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["SellingPrice"])));
        
        $difference = $base_product_price - ($price / ((100 + $product_data["TaxRate"]) / 100));
        
        $weight_difference = number_format($base_product_weight, 4) - number_format($product_data["Weight"], 4);
        
        $attribute_array = array('options_values_price' => number_format(abs($difference), 4),
                                 'price_prefix' => ($difference >= 0) ? '-' : '+',
                                 'attributes_model' => $product_data["Anr"],
                                 'attributes_stock' => $product_data["Quantity"],
                                 'options_values_weight' => number_format(abs($weight_difference), 4),
                                 'weight_prefix' => ($weight_difference >= 0) ? '-' : '+',
                                 'sortorder' => $product_data["Position"],
                                 'attributes_ean' => (isset($product_data["EAN"]) && !empty($product_data["EAN"])) ? $product_data["EAN"] : NULL,
                                 'ab_productsid' => $product_data["ProductID"]);
        
        xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attribute_array, 'update', 'products_attributes_id = \''.$products_attribute_id.'\'');
    }
    
    protected function updateAttributeDataFromBase($product_data, $existing_product, $language_id) {
        $base_product_price = $this->checkProductsData('products_price', $existing_product);
        $base_product_weight = $this->checkProductsData('products_weight', $existing_product);
        $base_product_attribute_id = $this->getProductsAttributesID($product_data["BaseProductID"], $existing_product);
        
        $attributeExists = $this->checkAttributeByName($product_data["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationName"], $language_id);
        $attributeValueExists = $this->checkAttributeValueByName($product_data["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationValue"], $language_id);
        if ($attributeExists) {
            $attribute_id = $attributeExists;
        } else {
            $attribute_id = $this->createAttributeOption($product_data["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationName"]);
        }
        if ($attributeValueExists) {
            $attribute_value_id = $attributeValueExists;
        } else {
            $attribute_value_id = $this->createAttributeOptionValue($product_data["BaseProductsRelationData"]["eBayVariationData"]["eBayVariationValue"], $attribute_id);
        }
        if ($attribute_id != 0 && $attribute_value_id != 0) {
        
            $price = floatval(str_replace(',', '.', str_replace('.', '', $product_data["SellingPrice"])));
            
            $difference = $base_product_price - ($price / ((100 + $product_data["TaxRate"]) / 100));

            $weight_difference = number_format($base_product_weight, 4) - number_format($product_data["Weight"], 4);

            $attribute_array = array('options_id' => $attribute_id,
                                     'options_values_id' => $attribute_value_id,'options_values_price' => number_format(abs($difference), 4),
                                     'price_prefix' => ($difference >= 0) ? '-' : '+',
                                     'attributes_model' => $product_data["Anr"],
                                     'attributes_stock' => $product_data["Quantity"],
                                     'options_values_weight' => number_format(abs($weight_difference), 4),
                                     'weight_prefix' => ($weight_difference >= 0) ? '-' : '+',
                                     'sortorder' => $product_data["Position"],
                                     'attributes_ean' => (isset($product_data["EAN"]) && !empty($product_data["EAN"])) ? $product_data["EAN"] : NULL,
                                     'ab_productsid' => $product_data["BaseProductID"]);

            if ($base_product_attribute_id) {
                xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attribute_array, 'update', 'products_attributes_id = \''.$base_product_attribute_id.'\'');
            } else {
                $attribute_array['products_id'] = $existing_product;
                xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attribute_array);
            }
        }
    }
    
    protected function getManufacturersId($manufacturers_name) {
        $manufacturers_query = xtc_db_query("SELECT manufacturers_id FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_name = '" . $manufacturers_name . "' ");
        if (xtc_db_num_rows($manufacturers_query)) {
            $manufacturers_array = xtc_db_fetch_array($manufacturers_query);
            return $manufacturers_array["manufacturers_id"];
        } else {
            $new_manufacturer_array = array('manufacturers_name' => $manufacturers_name,
                                            'manufacturers_image' => NULL,
                                            'date_added' => date('Y-m-d H:i:s'),
                                            'last_modified' => date('Y-m-d H:i:s'));
            
            xtc_db_perform(TABLE_MANUFACTURERS, $new_manufacturer_array);
            $manufacturers_id = xtc_db_insert_id();
            
            $languages = get_active_language_ids();
            foreach ($languages AS $lang) {
                $new_manufacturer_info_array = array('manufacturers_id' => $manufacturers_id,
                                                     'languages_id' => $lang['id'],
                                                     'manufacturers_url' => '',
                                                     'manufacturers_meta_title' => '',
                                                     'manufacturers_meta_description' => '',
                                                     'manufacturers_meta_keywords' => '',
                                                     'manufacturers_description' => '',
                                                     'manufacturers_description_more' => '',
                                                     'manufacturers_short_description' => '');
                
                xtc_db_perform(TABLE_MANUFACTURERS_INFO, $new_manufacturer_info_array);
            }
            
            return $manufacturers_id;
        }
    }

    protected function processProductImage($imageUrl, $products_id, $image_nr = '0') {
      $pname_arr = explode('.', $imageUrl);
      $nsuffix = array_pop($pname_arr);
      $products_image_name = $products_id.'_'.$image_nr.'.'.$nsuffix;
      $imageUrl = str_replace (' ', '%20', $imageUrl);

      if ($imageUrl != '' && strpos($imageUrl, 'http') !== false) {

        $products_afterbuy_image_array = array('products_id' => $products_id,
                                               'images_name' => $products_image_name,
                                               'images_url' => $imageUrl);
      }

      $afterbuy_images_query = xtc_db_query("SELECT images_name FROM ".TABLE_AFTERBUY_IMAGES. " WHERE products_id = ".$products_id. " AND images_name LIKE '%".$products_image_name."%' AND images_url LIKE '%".$imageUrl."%'");

      if (xtc_db_num_rows($afterbuy_images_query) == 0) {
        xtc_db_perform(TABLE_AFTERBUY_IMAGES, $products_afterbuy_image_array);      
      }
      return $products_image_name;
    }
    
    protected function getFreeFields() {
        $free_fields = array();
        $free_fields_query = xtc_db_query("SELECT configuration_value, configuration_key FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'AFTERBUY_FREE_FIELD_%' AND configuration_group_id = 50 ");
        while($free_fields_array = xtc_db_fetch_array($free_fields_query)) {
            $free_fields[$free_fields_array['configuration_value']] = str_replace('AFTERBUY_FREE_FIELD_', '', $free_fields_array['configuration_key']);
        }
        
        return $free_fields;
    }
    
    protected function checkProductsData($data_field, $product_id) {
        $data_query = xtc_db_query("SELECT " . $data_field . " FROM " . TABLE_PRODUCTS . " WHERE ab_productsid = '" . $product_id . "'");
        $data_array = xtc_db_fetch_array($data_query);
        
        return $data_array[$data_field];
    }
    
    protected function getProductsAttributesID($afterbuy_base_id, $product_id) {
        $data_query = xtc_db_query("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE ab_productsid = '" . $afterbuy_base_id . "' AND products_id = '" . $product_id . "' ");
        if (xtc_db_num_rows($data_query) > 0) {
            $data_array = xtc_db_fetch_array($data_query);
            
            return $data_array['products_attributes_id'];
        } else {
            return false;
        }
    }
    
    /*protected function getProductsImage($products_id, $image_nr) {
        $products_image_query = xtc_db_query("SELECT image_id FROM " . TABLE_PRODUCTS_IMAGES . " WHERE products_id = '" . $products_id . "' AND image_nr = '" . $image_nr . "' ");
        if (xtc_db_num_rows($products_image_query)) {
            $products_image_array = xtc_db_fetch_array($products_image_query);
            return $data_array['image_id'];
        } else {
            return false;
        }
    }*/
    
    protected function getProductsVPEID($products_vpe_name) {
        $vpe_query = xtc_db_query("SELECT products_vpe_id FROM " . TABLE_PRODUCTS_VPE . " WHERE products_vpe_name = '" . $products_vpe_name . "' ");
        if (xtc_db_num_rows($vpe_query)) {
            $vpe_array = xtc_db_fetch_array($vpe_query);
            return $vpe_array['products_vpe_id'];
        } else {
            $products_vpe_query = xtc_db_query("SELECT MAX(products_vpe_id) as max_id FROM " . TABLE_PRODUCTS_VPE . " ");
            if (xtc_db_num_rows($products_vpe_query)) {
                $products_vpe_id_array = xtc_db_fetch_array($products_vpe_query);
                $products_vpe_id = $products_vpe_id_array['max_id'] + 1;
            } else {
                $products_vpe_id = 1;
            }
            $languages = get_active_language_ids();
            foreach ($languages AS $lang) {
                $products_vpe_array = array('products_vpe_id' => $products_vpe_id,
                                            'language_id' => $lang['id'],
                                            'products_vpe_name' => $products_vpe_name);
                
                xtc_db_perform(TABLE_PRODUCTS_VPE, $products_vpe_array);
            }
            
            return $products_vpe_id;
        }
    }

    protected function getProductsShippingTime($shipping_status_name) {
        $shipping_time_query = xtc_db_query("SELECT shipping_status_id FROM " . TABLE_SHIPPING_STATUS . " WHERE shipping_status_name = '" . $shipping_status_name . "' ");
        if(xtc_db_num_rows($shipping_time_query)) { //check if shipping id exists in the shop
            $shipping_time_array = xtc_db_fetch_array($shipping_time_query);
            return $shipping_time_array['shipping_status_id'];
        } else { //if doesn't exist
            $shipping_status_id_query = xtc_db_query("SELECT MAX(shipping_status_id) AS max_id FROM " . TABLE_SHIPPING_STATUS . " "); //get the last shipping_id from table
            if(xtc_db_num_rows($shipping_status_id_query)) {
                $shipping_status_id_array = xtc_db_fetch_array($shipping_status_id_query);
                $shipping_status_id = $shipping_status_id_array['max_id'] + 1;
            } else {
                $shipping_status_id = 1;
            }
            $languages = get_active_language_ids();
            foreach ($languages as $lang) {
                $shipping_time_array = array('shipping_status_id' => $shipping_status_id, 
                                             'language_id' => $lang['id'], 
                                             'shipping_status_name' => $shipping_status_name, 
                                             'shipping_status_image' => '');
                
                xtc_db_perform(TABLE_SHIPPING_STATUS, $shipping_time_array);
            }
            
            return $shipping_status_id;
        }
    }
}
