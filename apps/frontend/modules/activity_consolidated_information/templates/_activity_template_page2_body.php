<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */
?>

<main id="d-content">

    <div class="d-grid">

        <?php include_partial('quarters_list', array('consolidated_information' => $consolidated_information)); ?>

        <h2 class="h1 d-ttu fw_400"><?php echo $consolidated_information->getCompany()->getName(); ?></h2>

        <h2 class="report-campaign-title is-flexbox">
            <span><?php echo $consolidated_information->getActivity()->getId(); ?>.</span>
            <span><?php echo $consolidated_information->getActivity()->getName(); ?></span>
        </h2>

        <h2 class="report-campaign-title is-flexbox">
            <span>Ключевая статистика</span>
        </h2>
        <div class="report-campaign-box">

            <div class="report-campaign-chart-row is-flexbox">
                <div class="report-campaign-chart-row__body">
                    <div class="report-campaign-funnel report-campaign-funnel_sm is-flexbox is-flexbox_center">
                        <div class="report-campaign-funnel__values">
                            <div class="report-campaign-funnel__values__chart">
                                <ul class="d-plain is-flexbox">
                                    <li class="is-flexbox is-flexbox_center"><span>100</span></li>
                                    <li class="is-flexbox is-flexbox_center"><span>10</span></li>
                                    <li class="is-flexbox is-flexbox_center"><span>50</span></li>
                                    <li class="is-flexbox is-flexbox_center"><span>30</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="report-campaign-chart-row__legend">
                    <h3>Количество клиентов по акции</h3>
                    <ul class="report-campaign-chart-legend d-plain">
                        <li class="is-blue">Количество клиентов, которым анонсировали акцию (чел.)</li>
                        <li class="is-blue2">Количество клиентов, записанных на акцию (чел.)</li>
                        <li class="is-gray2">Количество клиентов, приехавших на акцию (чел.)</li>
                    </ul>
                </div>
            </div>

            <div class="report-campaign-chart-row is-flexbox">
                <div class="report-campaign-chart-row__body">
                    <img src="./img/img_pie_0.jpg" alt=""/>
                </div>
                <div class="report-campaign-chart-row__legend">
                    <h3>Количество оказанных услуг</h3>
                    <ul class="report-campaign-chart-legend d-plain">
                        <li class="is-blue"><strong>Пакет</strong> Инспекционный сервис каждые 15 000 (чел.)</li>
                        <li class="is-blue2"><strong>Пакет</strong> Масляный сервис (чел.)</li>
                        <li class="is-gray2"><strong>Пакет</strong> Инспекционный сервис каждые 30 000</li>
                    </ul>
                </div>
            </div>

            <div class="report-campaign-chart-row is-flexbox">
                <div class="report-campaign-chart-row__body">
                    <img src="./img/img_pie_1.jpg" alt=""/>
                </div>
                <div class="report-campaign-chart-row__legend">
                    <h3>Источники информации об акции (%)</h3>
                    <ul class="report-campaign-chart-legend report-campaign-chart-legend_cols d-plain">
                        <li class="is-gray3">Наружная реклама</li>
                        <li class="is-blue2">Соц. сети</li>
                        <li class="is-gray">Радио</li>
                        <li class="is-gray">Другие источники</li>
                        <li class="is-blue">Интернет</li>
                    </ul>
                </div>
            </div>

            <div class="report-campaign-sum">
                <h3>Дополнительные показатели акции</h3>
                <dl class="d-plain is-flexbox is-flexbox_center">
                    <dt>Сумма проданных аксессуаров по акции (руб., без НДС)</dt>
                    <dd>2 043 991</dd>
                </dl>
                <dl class="d-plain is-flexbox is-flexbox_center">
                    <dt>Количество аксессуаров, проданных по акции (шт.)</dt>
                    <dd>1 578</dd>
                </dl>
            </div>

            <div class="report-campaign-sum">
                <h3>Результатиыность акции</h3>
                <dl class="d-plain is-flexbox is-flexbox_center">
                    <dt>Выручка - затраты, руб. (среднее значение)</dt>
                    <dd class="is-green">2 041 110</dd>
                </dl>
            </div>

        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="d-footer">
            <div class="d-footer__title">Примечание</div>
            <div>Данные дилерских центров, заполнивших статистику на портале dm.vw-servicepool.ru</div>
        </div>

    </div>
</footer>
