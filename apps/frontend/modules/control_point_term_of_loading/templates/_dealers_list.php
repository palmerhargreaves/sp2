<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.01.2019
 * Time: 15:28
 */
?>

<?php foreach ($control_point_terms_loading as $control_point): ?>
    <tr>
        <td><?php echo $control_point->getDealer()->getNameAndNumber(); ?></td>

        <?php foreach (range(1,4 ) as $q): ?>
            <?php $custom_func = 'getQ'.$q; ?>
            <td style='text-align: center;'>
                <input type="checkbox"
                       class="control-point-quarter"
                    <?php echo $control_point->$custom_func() ? "checked" : ""; ?>
                       value="<?php echo $control_point->$custom_func(); ?>"
                       data-def-value="<?php echo $control_point->$custom_func(); ?>"
                       data-year="<?php echo $control_point->getYear(); ?>"
                       data-quarter="<?php echo $q; ?>"
                       data-dealer-id="<?php echo $control_point->getDealer()->getId(); ?>"
                />
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
