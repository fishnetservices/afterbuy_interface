<?php

/**
* get_languages()
*
* @return
*/
function get_languages() {
    $languages_query = xtc_db_query("select languages_id, name, code, image, directory from ".TABLE_LANGUAGES." where status = '1' order by sort_order");
    while ($languages = xtc_db_fetch_array($languages_query)) {
      $languages_array[] = array ('id' => $languages['languages_id'],
                                  'name' => $languages['name'],
                                  'code' => $languages['code'],
                                  'image' => $languages['image'],
                                  'directory' => $languages['directory']
                                  );
    }
 return $languages_array;
}
  
?>