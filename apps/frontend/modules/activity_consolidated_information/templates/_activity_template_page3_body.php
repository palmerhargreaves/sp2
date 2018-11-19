<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$dealers = $consolidated_information->getDealersInformation();

$page = $page->getRawValue();
?>

<main id="d-content">

    <div class="d-grid">

        <div class="report-dealers fs_xl">
            <table>
                <tbody>

                <?php if (array_key_exists('level_1', $page)) {
                    include_partial('dealer_level_icons', array('completed_by_levels' => $page, 'level_index' => 'level_1'));
                } ?>
                <?php if (array_key_exists('level_2', $page)) {
                    include_partial('dealer_level_icons', array('completed_by_levels' => $page, 'level_index' => 'level_2'));
                } ?>
                <?php if (array_key_exists('level_3', $page)) {
                    include_partial('dealer_level_icons', array('completed_by_levels' => $page, 'level_index' => 'level_3'));
                } ?>

                <?php if (array_key_exists('level_4', $page)): ?>
                    <?php foreach ($page['level_4'] as $level): ?>
                        <tr class="is-empty">
                            <td class="report-dealers__num"><?php echo $level['dealer']['number']; ?></td>
                            <td class="report-dealers__title"><?php echo $level['dealer']['name']; ?></td>
                            <td class="report-dealers__icons"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>

</main>
