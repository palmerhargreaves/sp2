<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

////dm-ng.palmer-hargreaves.ru/admin/files/company_types/<?php echo $consolidated_information->getActivity()->getCompanyType()->getImage()->getPath();

$manager = $consolidated_information->getManager();
?>

<main id="d-content">

    <div class="d-grid">

        <?php include_partial('quarters_list', array('consolidated_information' => $consolidated_information)); ?>

        <h2 class="h1 d-ttu fw_400"><?php echo $consolidated_information->getCompany()->getName(); ?></h2>

        <h2 class="report-campaign-title is-flexbox">
            <span><?php echo $consolidated_information->getActivity()->getId(); ?>.</span>
            <span><?php echo $consolidated_information->getActivity()->getName(); ?></span>
        </h2>
        <div class="report-campaign-box">
            <div class="report-campaign-descr is-flexbox">
                <div class="report-campaign-descr__img" style="background-image:url(http://dm.vw-servicepool.ru/pdf/img/ico_person.png)"></div>
                <div class="report-campaign-descr__txt is-flexbox">
                    <dl class="d-plain is-flexbox">
                        <dt class="is-flexbox is-flexbox_center">Сроки</dt>
                        <dd class="is-flexbox is-flexbox_center">6 февраля - 31 марта</dd>
                    </dl>
                    <dl class="d-plain is-flexbox">
                        <dt class="is-flexbox is-flexbox_center">Цель кампании</dt>
                        <dd class="is-flexbox is-flexbox_center">Увеличение продаж оригинальных аксессуаров в период
                            праздников: 14 февраля, 23 февраля, 8 марта.
                        </dd>
                    </dl>
                    <dl class="d-plain is-flexbox">
                        <dt class="is-flexbox is-flexbox_center">Целевая аудитория</dt>
                        <dd class="is-flexbox is-flexbox_center">Все владельцы автомобилей Volkswagen.</dd>
                    </dl>
                    <dl class="d-plain is-flexbox">
                        <dt class="is-flexbox is-flexbox_center">Механика кампании</dt>
                        <dd class="is-flexbox is-flexbox_center">Увеличение продаж оригинальных аксессуаров в период
                            праздников: 14 февраля, 23 февраля, 8 марта.
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <h2 class="report-campaign-title is-gray is-flexbox">
            <span>Общая статистика</span>
        </h2>
        <div class="report-campaign-box is-gray">
            <div class="report-campaign-funnel is-flexbox is-flexbox_center">
                <div class="report-campaign-funnel__values">
                    <div class="report-campaign-funnel__values__chart">
                        <ul class="d-plain is-flexbox">
                            <?php $dealers_information = $consolidated_information->getDealersInformation(); ?>

                            <li class="is-flexbox is-flexbox_center"><span><?php echo $dealers_information['count']; ?></span></li>
                            <li class="is-flexbox is-flexbox_center"><span><?php echo $dealers_information['service_action_count']; ?></span></li>
                            <li class="is-flexbox is-flexbox_center"><span><?php echo $dealers_information['models_count']; ?></span></li>
                            <li class="is-flexbox is-flexbox_center"><span><?php echo $dealers_information['statistic_completed_count']; ?></span></li>
                        </ul>
                    </div>
                </div>
                <div class="report-campaign-funnel__legend">
                    <ul class="report-campaign-chart-legend d-plain">
                        <li class="is-blue">Все дилеры <?php echo $manager ? "регионального менеджера" : ""; ?></li>
                        <li class="is-blue2">Дилеры - участники акции (подтвердили участие в акции)</li>
                        <li class="is-gray">Дилеры, приступившие к активности (согласовали хотя бы одну заявку)</li>
                        <li class="is-gray2">Дилеры, заполнившие статистику</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <h2 class="report-campaign-title is-flexbox">
            <span>Эффективность кампании</span>
        </h2>
        <div class="report-campaign-box">
            <dl class="report-campaign-effsum d-plain is-flexbox is-flexbox_center is-flexbox_justify">
                <dt>Средний % достижения цели</dt>
                <dd>50%</dd>
            </dl>
        </div>

    </div>
</footer>
