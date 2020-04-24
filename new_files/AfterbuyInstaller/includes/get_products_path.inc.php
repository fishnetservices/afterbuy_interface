<?php

   if (!function_exists('shopstat_functions')) {
    require_once (DIR_FS_INC . 'shopstat_functions.inc.php');
   }
   if (!function_exists('xtc_get_categories_name')) {
    require_once (DIR_FS_INC . 'xtc_get_categories_name.inc.php');
   }
   if (!function_exists('xtc_get_parent_categories')) {
    require_once (DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
   }
   if (!function_exists('xtc_get_categories_children')) {
    require_once (DIR_FS_INC . 'xtc_get_categories_children.inc.php');
   }

// Construct a category path to the product
// TABLES: products_to_categories
  function xtc_get_products_path_afterbuy($products_id) {
    $cPath = '';

    $category_query = "select p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = '" . (int)$products_id . "' and p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id != 0";
    $category_query  = xtc_db_query($category_query);
    if (xtc_db_num_rows($category_query,true)) {
        #Default category( first picked from table)
        $category = xtc_db_fetch_array($category_query);
        $p_path_full = $_SERVER['REQUEST_URI'];
        $p_path = $_SERVER['QUERY_STRING'];

        #If we display product check linked categories
        if(strpos($p_path,'products_id') !== false){
            
            while ($categoryies = xtc_db_fetch_array($category_query) ){
                $cat_name =  xtc_get_categories_name($categoryies['categories_id']);
                $cat_name_slug = shopstat_hrefSmallmask($cat_name);
                $p_path_full_array = explode('/', $p_path_full);
                if($p_path_full_array[count($p_path_full_array)-2] === $cat_name_slug){
                    $category = $categoryies;
                    break;
                }
             }
        }
        
        # Check if current categorie or its children have linked product
        $c_path = $_SERVER['QUERY_STRING'];
        if(strpos($c_path,'cPath') !== false){
            $category_path = substr($c_path, strpos($c_path,'=')+1);
            $categorie_previous = end(explode('_', $category_path));
            $cat_children = array();
            $cat_children = xtc_get_categories_children($categorie_previous);
            foreach($cat_children as $linked_cat){
                $category_query_check = "select p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = '" . (int)$products_id . "' and p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id != 0 and p2c.categories_id = '".$linked_cat."'";
                $category_query_check  = xtDBquery($category_query_check);
                if (xtc_db_num_rows($category_query_check,true)) {
                    $category = xtc_db_fetch_array($category_query_check);
                    break;
                }
            }
        }

      $categories = array();
      xtc_get_parent_categories($categories, $category['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (xtc_not_null($cPath)) $cPath .= '_';
      $cPath .= $category['categories_id'];
  }
//BOF - Dokuman - 2009-10-02 - removed feature, due to wrong links in category on "last viewed"  
/*
  if($_SESSION['lastpath']!=''){
    $cPath = $_SESSION['lastpath'];
  }
*/
//EOF - Dokuman - 2009-10-02 - removed feature, due to wrong links in category on "last viewed"  
  return $cPath;
}
?>