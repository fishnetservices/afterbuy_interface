<p class="h3" style="margin-top: 0;"><?php echo TABLE_CAPTION_AFTERBUY_LAST_IMPORT_RUNTIME; ?></p>

<table class="table table-striped">
    <tbody>
        <?php
        $afterbuy_last_runtime_query = xtc_db_query("SELECT configuration_value as afterbuy_last_runtime FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'AFTERBUY_LAST_IMPORT_RUNTIME' ");
        $afterbuy_last_runtime_array = xtc_db_fetch_array($afterbuy_last_runtime_query);

        ?>
        <td>
            <?php 
            echo $afterbuy_last_runtime_array['afterbuy_last_runtime'];
            ?>
        </td>
    </tbody>
</table>