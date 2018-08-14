<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 19.06.2016
 * Time: 12:06
 */
?>
<div class="span11">
    <div class="span4">
        <h4>Заявки</h4>
        <hr/>
        <table>
            <tr>
                <td>Всего заявок:</td>
                <td><?php echo count($compared_items_result['left']['models']); ?></td>
            </tr>
        </table>
    </div>

    <?php $img = count($compared_items_result['left']['models']) == count($compared_items_result['right']['models']) ? "equal" : "not_equal"; ?>
    <div class="span2" style="text-align: center;">
        <img style="width: 64px;" src="/img/<?php echo $img; ?>.png" title=""/>

        <?php if ($img == "not_equal"): ?>
            <input type="button" style="margin-top: 20px;" class="btn btn-info show-extended-info"
                   data-cls="container-models-compared-info" value="Подробнее"/>
        <?php endif; ?>
    </div>

    <div class="span4">
        <h4>Заявки</h4>
        <hr/>

        <table>
            <tr>
                <td>Всего заявок:</td>
                <td><?php echo count($compared_items_result['right']['models']); ?></td>
            </tr>
        </table>
    </div>

    <div class="container-models-compared-info" style="display: none;">
        <hr class="span11"/>
        <div class="span10">
            <div class="span10">
                <h6>Заявки</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['compared_models']['models'] as $model): ?>
                        <div class="span1"><?php echo $model; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['compared_models']['models'] as $model): ?>
                        <div class="span1"><?php echo $model; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="span10">
                <h6>Без совпадения по заявкам</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['compared_models']['not_compared'] as $model): ?>
                        <div class="span1"><?php echo $model; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['compared_models']['not_compared'] as $model): ?>
                        <div class="span1"><?php echo $model; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="span11"/>
<div class="span11">
    <div class="span4">
        <h4>Активности с выполненной статистикой</h4>
        <hr/>
        <table>
            <tr>
                <td>Всего активностей:</td>
                <td><?php echo $compared_items_result['left']['activities_with_comp_stat']; ?></td>
            </tr>
        </table>
    </div>
    <?php $img = $compared_items_result['left']['activities_with_comp_stat'] == $compared_items_result['right']['activities_with_comp_stat'] ? "equal" : "not_equal"; ?>
    <div class="span2" style="text-align: center;">
        <img style="width: 64px;" src="/img/<?php echo $img; ?>.png" title=""/>

        <?php if ($img == "not_equal"): ?>
            <input type="button" style="margin-top: 20px;" class="btn btn-info show-extended-info"
                   data-cls="container-activities-with-comp-stat-compared-info" value="Подробнее"/>
        <?php endif; ?>
    </div>

    <div class="span4">
        <h4>Активности с выполненной статистикой</h4>
        <hr/>

        <table>
            <tr>
                <td>Всего активностей:</td>
                <td><?php echo $compared_items_result['right']['activities_with_comp_stat']; ?></td>
            </tr>
        </table>
    </div>

    <div class="container-activities-with-comp-stat-compared-info" style="display: none;">
        <hr class="span11"/>
        <div class="span10">
            <div class="span10">
                <h6>Активности</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['activities_comp'] as $key => $item): ?>
                        <div class="span1"><?php echo $key; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['activities_comp'] as $key => $item): ?>
                        <div class="span1"><?php echo $key; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="span10">
                <h6>Без совпадения по активностям</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['compared_activities_with_completed_stat'] as $item): ?>
                        <div class="span1"><?php echo $item; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['compared_activities_with_completed_stat'] as $item): ?>
                        <div class="span1"><?php echo $item; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="span11"/>
<div class="span11">
    <div class="span4">
        <h4>Активности без выполнения статистики</h4>
        <hr/>
        <table>
            <tr>
                <td>Всего активностей:</td>
                <td><?php echo $compared_items_result['left']['activities_without_comp_stat']; ?></td>
            </tr>
        </table>
    </div>
    <?php $img = $compared_items_result['left']['activities_without_comp_stat'] == $compared_items_result['right']['activities_without_comp_stat'] ? "equal" : "not_equal"; ?>
    <div class="span2" style="text-align: center;">
        <img style="width: 64px;" src="/img/<?php echo $img; ?>.png" title=""/>

        <?php if ($img == "not_equal"): ?>
            <input type="button" style="margin-top: 20px;" class="btn btn-info show-extended-info"
                   data-cls="container-activities-with-not-comp-stat-compared-info" value="Подробнее"/>
        <?php endif; ?>
    </div>
    <div class="span4">
        <h4>Активности без выполнения статистики</h4>
        <hr/>

        <table>
            <tr>
                <td>Всего активностей:</td>
                <td><?php echo $compared_items_result['right']['activities_without_comp_stat']; ?></td>
            </tr>
        </table>
    </div>

    <div class="container-activities-with-not-comp-stat-compared-info" style="display: none;">
        <hr class="span11"/>
        <div class="span10">
            <div class="span10">
                <h6>Активности</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['activities_not_comp'] as $key => $item): ?>
                        <div class="span1"><?php echo $key; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['activities_not_comp'] as $key => $item): ?>
                        <div class="span1"><?php echo $key; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="span10">
                <h6>Без совпадения по активностям</h6>
                <hr/>

                <div class="span5" style="text-align: right;">
                    <?php foreach ($compared_items_result['left']['compared_activities_without_completed_stat'] as $item): ?>
                        <div class="span1"><?php echo $item; ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="span4">
                    <?php foreach ($compared_items_result['right']['compared_activities_without_completed_stat'] as $item): ?>
                        <div class="span1"><?php echo $item; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

