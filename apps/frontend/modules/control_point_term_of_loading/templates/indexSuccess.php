<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.01.2019
 * Time: 11:30
 */

?>

<div class="approvement" style="min-height: 55px;">
    <div style="display: block; width: 100%; float: left; margin-bottom: 20px;">
        <div style='float:left; width: 25%;'>
            <h1>Сроки подгрузки за: </h1>
        </div>

        <div style='float:left; width: 75%;'>
            <div id="filters" style="left: 180px;">
                <form action="<?php echo url_for('@control_point_terms_loading_change_year') ?>" method="get">
                    <div class="modal-select-wrapper krik-select select dealer filter">
                        <span class="select-value"><?php echo $current_year; ?></span>
                        <input type="hidden" name="year">

                        <div class="ico"></div>
                        <span class="select-filter"><input type="text"></span>
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <?php foreach (D::getYearsRangeList(2015) as $year): ?>
                                <div class="modal-select-dropdown-item select-item" data-value="<?php echo $year ?>"><?php echo $year ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <br/>
    <div id="agreement-models">
        <br/>
        <h2>Дилеры</h2>

        <table class="models" id="models-list" data-url="<?php echo url_for('@control_point_terms_loading_change_quarter_status'); ?>">
            <thead>
            <tr>
                <td width="170">Дилер</td>

                <?php foreach (range(1,4 ) as $q): ?>
                    <td width="75" style='text-align: center;'>Квартал: <?php echo $q; ?> </td>
                <?php endforeach; ?>

            </tr>
            </thead>
            <tbody class="animated" id="dealers-list-container">
                <?php include_partial('dealers_list', array('control_point_terms_loading' => $control_point_terms_loading)); ?>
            </tbody>
        </table>

    </div>
</div>

<script>
    $(function() {
        new ControlPointTermsOfLoading({}).start();
    });
</script>
