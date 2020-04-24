<?php

require('includes/application_top.php');
$ab_conf = array();
$afterbuy_configuration_query = xtc_db_query("SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'AFTERBUY_%' AND configuration_group_id = '50' ORDER BY sort_order ASC ");
while($afterbuy_configuration_array = xtc_db_fetch_array($afterbuy_configuration_query)) {
    $ab_conf[$afterbuy_configuration_array["configuration_key"]] = $afterbuy_configuration_array["configuration_value"];
}

require (DIR_WS_INCLUDES.'head.php');

switch ($_GET['action']) {
  case 'startimport':
      ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $.ajax({
                    url: "<?php if (ENABLE_SSL_CATALOG == 'true') { echo HTTPS_SERVER; } else { echo HTTP_SERVER; } ?>/<?php echo FILENAME_AFTERBUY_IMPORT; ?>",
                    method: "GET",
                    dataType: "text",
                    data: { action: "GetShopCatalogs" },
                    success: function() {
						$(".spinner").hide();
                        alert("<?php echo AFTERBUY_IMPORT_SUCCESS_MESSAGE; ?>");
                        location.href = "<?php echo xtc_href_link(FILENAME_AFTERBUY_IMPORT); ?>";
                    }, 
                    error: function(){
                        $(".spinner").hide();
                        $(".overlay").hide();
                        alert("<?php echo AFTERBUY_IMPORT_ERROR_MESSAGE; ?>");
                        location.href = "<?php echo xtc_href_link(FILENAME_AFTERBUY_IMPORT); ?>";
                    }
                });
            });

            $(document).ajaxStart(function() {
                var height = window.innerHeight;
                $(".overlay").show();
                $(".spinner").show();
                $(".spinner").css('z-index', 9999);
            });

            $(document).ajaxStop(function() {
                $(".spinner").hide();
            });
        </script>
      <?php
      break;
    case 'startorderimport':
      ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $.ajax({
                    url: "<?php if (ENABLE_SSL_CATALOG == 'true') { echo HTTPS_SERVER; } else { echo HTTP_SERVER; } ?>/<?php echo FILENAME_AFTERBUY_IMPORT; ?>",
                    method: "GET",
                    dataType: "text",
                    data: { action: "GetSoldItems" },
                    success: function() {
                        $(".spinner").hide();
                        alert("<?php echo AFTERBUY_IMPORT_SUCCESS_MESSAGE; ?>");
                        location.href = "<?php echo xtc_href_link(FILENAME_AFTERBUY_IMPORT); ?>";
                    },
                    error: function(){
                        $(".spinner").hide();
                        $(".overlay").hide();
                        alert("<?php echo AFTERBUY_IMPORT_ERROR_MESSAGE; ?>");
                        location.href = "<?php echo xtc_href_link(FILENAME_AFTERBUY_IMPORT); ?>";
                    }
                });
            });

            $(document).ajaxStart(function() {
                var height = window.innerHeight;
                $(".overlay").show();
                //$(".overlay").css('height', height+'px');
                $(".spinner").show();
                $(".spinner").css('z-index', 9999);
                //$(".container-fluid").css('opacity', 0.1);
            });

            $(document).ajaxStop(function() {
                $(".spinner").hide();
            });
        </script>
      <?php
      break;
    case 'getimages':
      ?>
        <script type="text/javascript">	    
	        $(document).ready(function() {
                $.ajax({
                    url: "<?php echo HTTP_SERVER; ?>/<?php echo FILENAME_AFTERBUY_IMPORT; ?>",
                    method: "GET",
                    dataType: "text",
                    data: { action: "GetImages" },
                    success: function() {
                        $(".spinner").hide();
                        alert("<?php echo AFTERBUY_IMPORT_SUCCESS_MESSAGE; ?>");
                        location.href = "<?php echo xtc_href_link(FILENAME_AFTERBUY_IMPORT); ?>";
                    }
                });
            });

            $(document).ajaxStart(function() {
                var height = window.innerHeight;
                $(".overlay").show();
                //$(".overlay").css('height', height+'px');
                $(".spinner").show();
                $(".spinner").css('z-index', 9999);
                //$(".container-fluid").css('opacity', 0.1);
            });

            $(document).ajaxStop(function() {
                $(".spinner").hide();
            });
        </script>
      <?php
      break;
  default:
    break;
}
?>
<style type="text/css">
.spinner {
    position: fixed;
    left: 0;
    right: 0;
    top: 50%;
    height:200px;
    width:200px;
    margin:0px auto;
    -webkit-animation: rotation .6s infinite linear;
    -moz-animation: rotation .6s infinite linear;
    -o-animation: rotation .6s infinite linear;
    animation: rotation .6s infinite linear;
    border-left:15px solid rgba(0,174,239,.15);
    border-right:15px solid rgba(0,174,239,.15);
    border-bottom:15px solid rgba(0,174,239,.15);
    border-top:15px solid rgba(0,174,239,.8);
    border-radius:100%;
    z-index: 9999;
}

.overlay {
    background: #f1f1f1;
    display: none;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    opacity: 0.8;
    height: 100vh;
    z-index: 9999;
}

@-webkit-keyframes rotation {
    from {-webkit-transform: rotate(0deg);}
    to {-webkit-transform: rotate(359deg);}
}
@-moz-keyframes rotation {
    from {-moz-transform: rotate(0deg);}
    to {-moz-transform: rotate(359deg);}
}
@-o-keyframes rotation {
    from {-o-transform: rotate(0deg);}
    to {-o-transform: rotate(359deg);}
}
@keyframes rotation {
    from {transform: rotate(0deg);}
    to {transform: rotate(359deg);}
}
</style>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<div class="overlay">
    <div class="spinner"></div>
</div>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div class="row">
<!-- body //-->
    <div class='col-xs-12'>
        <p class="h2">
            <?php echo HEADING_TITLE; ?>
        </p>
        Configuration
    </div>
<?php include DIR_WS_INCLUDES.FILENAME_ERROR_DISPLAY; ?>
<div class='col-xs-12'><br></div>
<div class='col-xs-12'>
<div id='responsive_table' class='table-responsive pull-left col-sm-12'>
    <div class="col-xs-12 text-left">
        <?php
        if ($file = file("../log/afterbuy_log.txt")) {
            $filelines = array();
            $file = array_reverse($file);
            $line_counter = 0;
            foreach ($file as $line) {
                if ($line_counter < 20) {
                    $filelines[] = $line;
                    $line_counter++;
                } else { 
                    break;
                }
            }
            $filelines = array_reverse($filelines);
        ?>
        <table class="table table-responsive table-striped table-bordered">
            <th><?php echo TIME; ?></th>
            <th><?php echo MESSAGE; ?></th>
            <?php
            foreach ($filelines as $line) {
                    $time = substr($line, 0, strpos($line, ']') + 1);
                    $action = substr($line, strpos($line, ']') + 1);
                    ?>
                    <tr>
                        <td><b><?php echo $time; ?></b></td>
                        <td><?php echo $action; ?></td>
                    </tr>
                    <?php
            } 
            ?>
        </table>
        <?php
        } else {
            echo AFTERBUY_LOG_FILE_NOT_FOUND;
        }
        ?>
    </div>
</div>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    default:
        $heading[] = array('text' => '<b>' . TEXT_INFO_AFTERBUY_IMPORT . '</b>');

        $contents = array('form' => xtc_draw_form('afterbuy_import', FILENAME_AFTERBUY_IMPORT, 'action=startimport', 'post', 'id="afterbuy_import"'));
        foreach ($ab_conf as $key => $value) {
           $contents[] = array('text' => '<br />' . constant($key . '_TITLE') . '<br /><input type="text" disabled="disabled" value="' . $value . '"/>');
        }
        if ($ab_conf['AFTERBUY_IMPORT_STATUS'] == 'true' && $ab_conf['AFTERBUY_IMPORT_RUNNING'] == 'false') {
            $disabled = false;
        } else {
            $disabled = true;
        }
        if (file_exists('../AfterbuyInstaller/index.php')) {
          $contents[] = array('align' => 'center', 'text' => AFTERBUY_INSTALLER_FOLDER_EXISTS);
        }  
        $contents[] = array('align' => 'center', 'text' => '<br /><a ' . (($disabled) ? 'disabled="disabled" ' : '') .  'class="btn btn-default" href="' . xtc_href_link(FILENAME_AFTERBUY_IMPORT, 'action=startimport', 'SSL') . '">' . BUTTON_START_IMPORT . '</a> ');
        $contents[] = array('align' => 'center', 'text' => '<br /><a ' . (($disabled) ? 'disabled="disabled" ' : '') .  'class="btn btn-default" href="' . xtc_href_link(FILENAME_AFTERBUY_IMPORT, 'action=startorderimport', 'SSL') . '">' . BUTTON_START_ORDER_IMPORT . '</a> ');
        $contents[] = array('align' => 'center', 'text' => '<br /><a ' . (($disabled) ? 'disabled="disabled" ' : '') .  'class="btn btn-default" href="' . xtc_href_link(FILENAME_AFTERBUY_IMPORT, 'action=getimages', 'SSL') . '">' . BUTTON_GET_IMAGES . '</a> ');
      break;
  }

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    echo '            <div class="col-md-3 col-sm-12 col-xs-12 pull-right edit-box-class">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </div>' . "\n";
    ?>
    <script>
        //responsive_table
        $('#responsive_table').addClass('col-md-9');
    </script>               
    <?php
  }
?>
</div>
</div>

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>