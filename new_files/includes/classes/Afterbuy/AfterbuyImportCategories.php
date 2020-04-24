<?php

class AfterbuyImportCategories {
    
    protected $xmlData;
    
    protected $insertedCategoriesCounter;
    
    protected $updatedCategoriesCounter;
    
    public function __construct($xmlData) {
        $this->xmlData = $xmlData;
        $this->insertedCategoriesCounter = 0;
        $this->updatedCategoriesCounter = 0;
    }
    
    public function importCategories() {
        $hasMoreCatalogs = false;
        if (isset($this->xmlData["HasMoreCatalogs"]) && $this->xmlData["HasMoreCatalogs"] != 0) {
            $hasMoreCatalogs = true;
            $lastCatalogID = $this->xmlData["LastCatalogID"];
        }
        $catalogs = $this->xmlData["Catalogs"];
        foreach ($catalogs as $catalog) {
            foreach ($catalog as $category) {
                $category_exists = $this->checkExistingAfterbuyCategory($category["CatalogID"]);
                if (!$category_exists) {
                    $result = $this->importCategory($category);
                    if ($result) {
                        $this->insertedCategoriesCounter++;
                    }
                } else {
                    $this->updateCategory($category, $category_exists);
                    $this->updatedCategoriesCounter++;
                }
            }
        }
        
        $results_array = array('insertedItems' => $this->insertedCategoriesCounter,
                               'updatedItems' => $this->updatedCategoriesCounter,
                               'rerun' => $hasMoreCatalogs,
                               'lastCatalogID' => $lastCatalogID);
        
        return $results_array;
    }
    
    protected function checkExistingAfterbuyCategory($afterbuy_cid) {
        $check_existing_query = xtc_db_query("SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE afterbuy_cid = '" . $afterbuy_cid . "' ");
        if (xtc_db_num_rows($check_existing_query) > 0) {
            $check_existing_array = xtc_db_fetch_array($check_existing_query);
            return $check_existing_array["categories_id"];
        } else {
            // Attach to root category if parent category has not been entered
            return false;
        }
    }
    
    protected function importCategory($category_data) {
        if (AFTERBUY_IMPORT_INACTIVE_STATUS == 'false' && $category_data["Show"] == 0) {
            return false;
        }
        $category_parent_id = ($category_data["ParentID"] == 0) ? 0 : $this->getAfterbuyCategoryParentID($category_data["ParentID"]);
        $import_data_array = array('parent_id' => $category_parent_id,
                              'categories_status' => $category_data["Show"],
                              'categories_template' => AFTERBUY_DEFAULT_IMPORT_CATEGORIES_TEMPLATE,
                              'listing_template' => AFTERBUY_DEFAULT_IMPORT_PRODUCTS_LISTING_TEMPLATE,
                              'sort_order' => $category_data["Position"],
                              'products_sorting' => 'p.products_sort',
                              'products_sorting2' => 'ASC',
                              'date_added' => date('Y-m-d H:i:s'),
                              'last_modified' => date('Y-m-d H:i:s'),
                              'afterbuy_cid' => $category_data["CatalogID"],
                              'afterbuy_pid' => $category_data["ParentID"]);
        
        $customers_statuses_array = xtc_get_customers_statuses();
        $permission_array = array();
        for ($i = 0, $n = count($customers_statuses_array); $i <= $n; $i ++) {
            if (isset($customers_statuses_array[$i]['id'])) {
                $permission_array = array_merge($permission_array, array('group_permission_'.$customers_statuses_array[$i]['id'] => 1));
            }
        }
        
        $import_array = array_merge($import_data_array, $permission_array);
        
        xtc_db_perform(TABLE_CATEGORIES, $import_array);
        $categories_id = xtc_db_insert_id();
        
        $languages = get_active_language_ids();
        foreach ($languages AS $lang) {
            $import_description_array = array('categories_id' => $categories_id,
                                              'language_id' => $lang["id"],
                                              'categories_name' => $category_data["Name"],
                                              'categories_heading_title' => '',
                                              'categories_description' => '',
                                              'categories_meta_title' => '',
                                              'categories_meta_description' => '');

            xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $import_description_array);
        }
        
        return $categories_id;
    }
    
    protected function updateCategory($category_data, $category_exists) {
        if (AFTERBUY_IMPORT_INACTIVE_STATUS == 'false' && $category_data["Show"] == 0) {
            return false;
        }

        $category_parent_id = ($category_data["ParentID"] == 0) ? 0 : $this->getAfterbuyCategoryParentID($category_data["ParentID"]);
        $import_array = array('parent_id' => $category_parent_id,
                              'categories_status' => $category_data["Show"],
                              'sort_order' => $category_data["Position"],
                              'last_modified' => date('Y-m-d H:i:s'),
                              'afterbuy_cid' => $category_data["CatalogID"],
                              'afterbuy_pid' => $category_data["ParentID"]);
        
        xtc_db_perform(TABLE_CATEGORIES, $import_array, 'update', 'categories_id = \''.$category_exists.'\'');
        
        $languages = get_active_language_ids();
        foreach ($languages AS $lang) {
            $import_description_array = array('categories_name' => $category_data["Name"]);

            xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION, $import_description_array, 'update', 'categories_id = \''.$category_exists.'\' AND language_id = \''.$lang["id"].'\' ');
        }
    }
    
    protected function getAfterbuyCategoryParentID($afterbuy_parent_id) {
        $check_parent_query = xtc_db_query("SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE afterbuy_cid = '" . $afterbuy_parent_id . "' ");
        if (xtc_db_num_rows($check_parent_query) > 0) {
            $check_parent_array = xtc_db_fetch_array($check_parent_query);
            return $check_parent_array["categories_id"];
        } else {
            // Attach to root category if parent category has not been entered
            return 0;
        }
    }
}
