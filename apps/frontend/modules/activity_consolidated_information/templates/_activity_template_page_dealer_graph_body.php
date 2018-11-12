<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');

$information = $information->getRawValue();

?>

<main id="d-content">
    <div class="d-grid">
        <?php if ($activity): ?>
            <table class="tbl_campaign_intro">
                <tbody>
                <tr>
                    <td>Тип кампании:</td>
                    <td><?php echo $activity['company_name']; ?></td>
                </tr>
                <tr>
                    <td>Название кампании:</td>
                    <td><strong><?php echo $activity['activity_name']; ?></strong></td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <h2 class="h1 d-ttu">Затраты на продвижение <span class="d-ttn">(руб.)</span></h2>

        <div class="report-chart">

            <div class="report-chart__row">
                <div class="report-chart__caption">Интернет-продвижение</div>
                <div class="report-chart__item" style="width:20%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:13%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:5%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Закупки</div>
                <div class="report-chart__item" style="width:50%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:30%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:10%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Мероприятия для клиентов</div>
                <div class="report-chart__item" style="width:20%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:13%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:5%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Электронные файлы</div>
                <div class="report-chart__item" style="width:50%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:30%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:10%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Печатные материалы</div>
                <div class="report-chart__item" style="width:20%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:13%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:5%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Текстовые файлы</div>
                <div class="report-chart__item" style="width:50%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:30%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:10%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Видеофайлы</div>
                <div class="report-chart__item" style="width:20%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:13%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:5%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__row">
                <div class="report-chart__caption">Аудиофайлы</div>
                <div class="report-chart__item" style="width:100%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-orange" style="width:30%;"><span> 13 333,99</span></div>
                <div class="report-chart__item is-blue" style="width:10%;"><span> 13 333,99</span></div>
            </div>

            <div class="report-chart__axis">
                <div><span>0</span></div>
                <div><span>5 000</span></div>
                <div><span>10 000</span></div>
                <div><span>15 000</span></div>
                <div><span>20 000</span></div>
                <div><span>25 000</span></div>
                <div><span>30 000</span></div>
                <div><span>35 000</span></div>
                <div><span>40 000</span></div>
            </div>

        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="d-footer">
            <div class="d-footer__title">Примечание</div>
            <div>На основе данных статистики, загруженной диллерским центром на портал dm.vw-servicepool.ru (последняя по дате загрузки)</div>
        </div>

    </div>
</footer>
