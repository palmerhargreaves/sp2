<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.10.2018
 * Time: 12:28
 */

$export_q_list = $consolidated_information->getExportQuartersList()->getRawValue();
?>

<nav class="nav_quarts">
    <ul class="is-flexbox is-flexbox_justify">
        <?php foreach (Utils::getQuartersList() as $quarter): ?>
            <li class="<?php echo in_array($quarter, $export_q_list) ? 'active' : ''; ?>"><?php echo $quarter; ?> квартал</li>
        <?php endforeach; ?>
    </ul>
</nav>
