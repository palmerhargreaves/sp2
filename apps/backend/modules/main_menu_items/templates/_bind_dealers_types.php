<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 11:52
 */
?>
<script type="text/javascript">
    $(function () {
        window.main_menu_items_dealers_types_rules = new DealersTypesRules({
            save_url: '<?php echo url_for("main_menu_items_dealer_types_save"); ?>',
        }).start();
    });
</script>
