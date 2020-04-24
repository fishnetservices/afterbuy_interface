<html>
<head>
<title>Afterbuy installation</title>
<?php
require('../includes/application_top_export.php');
  
if (isset($_POST) && isset($_POST['afterbuy_install_submit'])) {
  foreach($_POST as $key => $value) {
    $value = trim($value);
    if (strpos($key, 'category_afterbuy_') === 0) {
      $category_id = substr($key, strrpos($key, '_') + 1);
      if ($category_id != '' && isset($value) && $value != '') {
        $ab_cat_id_array = array('afterbuy_cid' => $value);
        xtc_db_perform(TABLE_CATEGORIES, $ab_cat_id_array, 'update', "categories_id = '".(int)$category_id."'");
      }
    } else if (strpos($key, 'product_afterbuy_') === 0) {
      $product_id = substr($key, strrpos($key, '_') + 1);
      if ($product_id != '' && isset($value) && $value != '') {
        $ab_prod_id_array = array('ab_productsid' => $value);
        xtc_db_perform(TABLE_PRODUCTS, $ab_prod_id_array, 'update', "products_id = '".(int)$product_id."'");
      }
    }
  }
}
?>

<link rel="stylesheet" href="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/css/bootstrap-add.css" type="text/css" />
<link rel="stylesheet" href="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="includes/afterbuy_install.css" type="text/css" />
<script src="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.js" type="text/javascript"></script>
<script src="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/javascript/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo '../templates/'.CURRENT_TEMPLATE; ?>/javascript/bootstrap-add.js" type="text/javascript"></script>

</head>
<body>
<?php

// required custom functions
require_once('includes/get_languages.inc.php');

// required XTC functions
require_once(DIR_FS_INC.'xtc_parse_input_field_data.inc.php');
require_once(DIR_FS_INC.'xtc_not_null.inc.php');
require_once(DIR_FS_INC.'xtc_draw_pull_down_menu.inc.php');
require_once(DIR_FS_INC.'xtc_draw_input_field.inc.php');
require_once(DIR_FS_INC.'xtc_draw_form.inc.php');
require_once(DIR_FS_INC.'xtc_get_product_path.inc.php');
require_once(DIR_FS_INC.'xtc_get_category_path.inc.php');
require_once(DIR_FS_INC.'xtc_category_link.inc.php');
require_once(DIR_FS_INC. 'xtc_get_categories.inc.php');
//require_once(DIR_FS_INC.'xtc_get_categories_children.inc.php');
require_once(DIR_FS_INC. 'xtc_has_category_subcategories.inc.php');
require_once(DIR_FS_INC.'xtc_href_link.inc.php');

function xtDBquery($query) {
  if (DB_CACHE=='true') {
    $result=xtc_db_queryCached($query);
  } else {
    $result=xtc_db_query($query);
  }
return $result;
}
$default_language_id = '2'; // setting default language to German

$language_id = isset($_POST['language_selector']) ? $_POST['language_selector'] : $default_language_id;
  
function xtc_category_has_products($cID){
  $check_query = xtc_db_query("SELECT p.products_id FROM products p JOIN products_to_categories p2c ON p.products_id = p2c.products_id WHERE p2c.categories_id = {$cID}");
  return (xtc_db_num_rows($check_query) != 0) ? true : false;
}
  
function display_menus($parent_id = 0){
  $main_categories = xtc_db_query("SELECT c.categories_id, cd.categories_name, c.parent_id, c.categories_status, c.afterbuy_cid FROM categories c JOIN categories_description cd ON c.categories_id = cd.categories_id WHERE cd.language_id = 2 AND parent_id = {$parent_id} ORDER BY parent_id, categories_id");
  $main_cat_ul = '';
  $main_cat_li = '';
  $main_cat = '';
  if($parent_id == 0){
    $main_cat_ul = 'list-group';
    $main_cat_li = 'list-group-item ';
    $main_cat = ' main-category';
  }
  if(xtc_db_num_rows($main_categories)){
    echo '<ul class="afterbuy-li'.$main_cat.'">';
    while($res = xtc_db_fetch_array($main_categories)){
      $categories_link_class = ($res['categories_status'] == '1') ? "text-success" : "text-danger";
      $categories_class = "list-group-item-warning";
      $margin = ($main_cat_li != '') ? "margin: 10px 0" : "";
      echo '<li class="'.$main_cat_li.$categories_class.'" style="' . $margin . '">';
      echo '<p style="height:45px; display: inline-block; font-weight: bold"><a class="'.$categories_link_class.'" href="'.xtc_href_link(FILENAME_DEFAULT, 'cPath='.xtc_get_category_path($res['categories_id'])).'">'.$res['categories_name'].'</a></p><p class="pull-right">'.xtc_draw_input_field('category_afterbuy_'.$res['categories_id'], $res['afterbuy_cid']).'</p>';
      if(xtc_category_has_products($res['categories_id'])){
        $products = xtc_db_query("SELECT pd.products_name, p.products_id, p.ab_productsid, p.products_status FROM products_description pd JOIN products_to_categories p2c ON pd.products_id = p2c.products_id JOIN products p ON pd.products_id = p.products_id WHERE p2c.categories_id = {$res['categories_id']} AND pd.language_id = '2'");
        while($products_array = xtc_db_fetch_array($products)){
          $products_link_class = ($products_array['products_status'] == '1') ? "text-success" : "text-danger";
          $products_class = "alert alert-warning";
          echo '<div class="a_products '.$products_class.'">';
          echo '<p style="height:45px; display: inline-block; margin-bottom: 10px;">- <a class="'.$products_link_class.'" href="'.xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product['products_id'].'cPath='.xtc_get_product_path($products_array['products_id'])).'">'.$products_array['products_name'].'</a></p><p class="pull-right">'.xtc_draw_input_field('product_afterbuy_'.$products_array['products_id'], $products_array['ab_productsid']).'</p>';
          echo '</div>';
        }
      }
      display_menus($res['categories_id']);
      echo '</li>';
    }
    echo '</ul>';
  }
}

$dropdown_languages_array = array();
$languages_array = get_languages();
foreach ($languages_array as $language) {
    $dropdown_languages_array[] = array('id' => $language['id'],
                                        'text' => $language['name']);
}
?>
  <div class="container">
    <div class="row">
        <div class="col-xs-3 pull-right text-right">
            <?php echo xtc_draw_form('change_language', 'index.php'); ?>
                <?php echo xtc_draw_pull_down_menu('language_selector', $dropdown_languages_array, $language_id, 'id="language_selector"'); ?>
            </form>
        </div>
    </div>
    <div class="row" style="margin-bottom: 20px;">
        <?php echo xtc_draw_form('afterbuy_install', 'index.php'); ?>
        <?php display_menus(); ?>
        <?php echo xtc_draw_input_field('afterbuy_install_submit', 'Submit', 'class="pull-right btn btn-primary"', 'submit'); ?>
       </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#language_selector').on('change', function() {
            $('#change_language').submit();
        });
    });
</script>
</body>
</html>