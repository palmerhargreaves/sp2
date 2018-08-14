<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 19.06.2016
 * Time: 12:06
 */
?>

<tr class="budget-dealer-item-info-<?php echo $item_info->getId(); ?>" style="display: none;">
    <td colspan="7">
        <div >
            <table style="font-size: 12px;">
                <tr>
                    <td>Всего заявок: </td>
                    <td>
                        <?php
                            $models_count = $item_info->loadData()->getTotalModels();
                            if ($models_count > 0):
                                ?>
                                <a href="javascript:;" class="show-dealer-budget-data" data-act="models_list" data-item-id="<?php echo $item_info->getId(); ?>"><?php echo $models_count; ?></a>
                        <?php else: ?>
                                0
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>Сумма заявок: </td>
                    <td><?php echo Utils::format_amount($item_info->getTotalModelsSum()); ?></td>
                </tr>

                <tr>
                    <td>Активностей: </td>
                    <td>
                        <?php
                            $act_count = $item_info->getTotalActivities();
                            if ($act_count > 0):
                                ?>
                                <a href="javascript:;" class="show-dealer-budget-data" data-act="activities_list" data-item-id="<?php echo $item_info->getId(); ?>"><?php echo $act_count; ?></a>
                        <?php else: ?>
                                0
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>Активностей с выполненной статистикой: </td>
                    <td>
                        <?php
                        $act_count = $item_info->getTotalActivitiesWithCompleteStatistic();
                        if ($act_count > 0):
                            ?>
                            <a href="javascript:;" class="show-dealer-budget-data" data-act="activities_with_complete_stat_list" data-item-id="<?php echo $item_info->getId(); ?>"><?php echo $act_count; ?></a>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>Активностей без выполненной статистики: </td>
                    <td>
                        <?php
                        $act_count = $item_info->getTotalActivitiesWithoutCompleteStatistic();
                        if ($act_count > 0):
                            ?>
                            <a href="javascript:;" class="show-dealer-budget-data" data-act="activities_without_complete_stat_list" data-item-id="<?php echo $item_info->getId(); ?>"><?php echo $act_count; ?></a>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </td>
</tr>
