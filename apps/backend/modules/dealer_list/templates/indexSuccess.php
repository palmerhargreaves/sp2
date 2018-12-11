<?php
/**
 * Created by PhpStorm.
 * User: averinbox
 * Date: 26.01.16
 * Time: 15:21
 */
$dealerType = array(Dealer::TYPE_PKW => 'PKW', Dealer::TYPE_NFZ => 'NFZ', Dealer::TYPE_NFZ_PKW => 'NFZ + PKW');
?>

<div class="container-fluid">
    <div class="row">
        <div>
            <form class="" action="/backend.php/dealer_list" method="get" id="dealer_form">


                <div class="controls controls-row">
                    <input type="text" name="search_num" class="pull-left" id="search_num" placeholder="Введите название или номер" value="<?= $search_num; ?>" style="margin-right: 5px;" />
                    <select name="city" id="city" class="pull-left" style="margin-right: 5px;">
                        <option value="">Выберите город</option>
                        <?php foreach($cities as $city): ?>
                            <option value="<?= $city->getId(); ?>" <?= $city_id == $city->getId() ? ' selected' : ''; ?>><?= $city->getName(); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="pull-left checkbox" style="margin-top: 5px; margin-right: 5px;">
                        <label><input type="checkbox" name="ch_dealer_status_disabled" id="ch_dealer_status_disabled" value="1" <?php echo $is_dealer_disabled   ? "checked" : "";?> />Не активные дилеры</label>
                    </div>

                    <div class="pull-left checkbox" style="margin-top: 5px;">
                        <label><input type="checkbox" name="ch_dealer_has_importer" id="ch_dealer_has_importer" value="1" <?php echo $is_dealer_importer   ? "checked" : "";?> />Импортеры</label>
                    </div>

                    <a data-href="/backend.php/dealer_list/export" class="dealers-export btn btn-danger pull-right" style="margin-left: 10px;">Экспорт</a>
                    <a href="/backend.php/dealer_list" class="btn btn-default pull-right">Сбросить поиск</a>
                    <a href="/backend.php/dealer_list/edit" class="btn btn-primary pull-right" style="margin-right: 10px;">Добавить дилера</a>

                </div>


            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>id</th>
                        <th><a href="/backend.php/dealer_list?order=number&direction=<?= $direction ?>">Номер</a></th>
                        <th><a href="/backend.php/dealer_list?order=name&direction=<?= $direction ?>">Имя дилера</a></th>

                        <?php foreach (DealerTable::getDealersGroupTypes() as $group_type): ?>
                            <th><a href="/backend.php/dealer_list?order=dealer_group_id&direction=<?= $direction ?>">Группа <?php echo strtoupper($group_type); ?></a></th>
                        <?php endforeach ; ?>

                        <th><a href="/backend.php/dealer_list?order=regional_manager_pkw&direction=<?= $direction ?>">Менеджер PKW</a></th>
                        <th><a href="/backend.php/dealer_list?order=regional_manager_nfz&direction=<?= $direction ?>">Менеджер NFZ</a></th>
                        <th><a href="/backend.php/dealer_list?order=city_id&direction=<?= $direction ?>">Город</a></th>
                        <th><a href="/backend.php/dealer_list?order=site&direction=<?= $direction ?>">Вебсайт</a></th>
                        <th><a href="/backend.php/dealer_list?order=dealer_type&direction=<?= $direction ?>">Тип</a></th>
                        <th><a href="/backend.php/dealer_list?order=status&direction=<?= $direction ?>">Статус</a></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($dealers as $dealer): ?>
                    <tr>
                        <td><?= $dealer->getId(); ?></td>
                        <?php $resultText = !is_null($search_num) ? implode("<span style='background: yellow;'>".$search_num."</span>", explode($search_num, $dealer->getNumber())) : ''; ?>
                        <td><?= empty($resultText) ? $dealer->getNumber() : $resultText; ?></td>
                        <?php $resultTextName = !is_null($search_num) ? implode("<span style='background: yellow;'>".$search_num."</span>", explode($search_num, $dealer->getName())) : ''; ?>
                        <td><a href="/backend.php/dealer_list/edit?id=<?= $dealer->getId(); ?>"><?= empty($resultTextName) ? $dealer->getName() : $resultTextName; ?></a></td>

                        <?php foreach (DealerTable::getDealersGroupTypes() as $group_type): ?>
                            <td><?php echo $dealer->getDealerGroupHeader($group_type); ?></td>
                        <?php endforeach; ?>

                        <td><?= $dealer->getRegionalManager() ? sprintf('%s %s', $dealer->getRegionalManager()->getSurname(), $dealer->getRegionalManager()->getFirstname()) : ''; ?></td>
                        <td><?= $dealer->getNfzRegionalManager() ? sprintf('%s %s', $dealer->getNfzRegionalManager()->getSurname(), $dealer->getNfzRegionalManager()->getFirstname()) : '';?></td>
                        <td><?= $dealer->getCity(); ?></td>
                        <td><a href="<?= $dealer->getSite(); ?>" target="_blank"><?= $dealer->getSite(); ?></a></td>
                        <td><?= $dealerType[$dealer->getDealerType()]; ?></td>
                        <td><?= $dealer->getStatus() ? '<span class="label label-success">Опубликован</span>' : '<span class="label label-important">Не опубликован</span>'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $('#city, #search_num, #ch_dealer_status_disabled, #ch_dealer_has_importer').on('change', function() {
           $(this).closest('form').submit();
        });

        $('.dealers-export').click(function() {
            var $element = $(this);

            $element.attr('disabled', true);
            $.post($element.data('href'), {}, function(result) {
                result = JSON.parse(result);
                if (result.success) {
                    window.location.href = result.exported_dealers_list_url;
                } else {
                    alert('Ошибка экспорта!');
                }

                $element.attr('disabled', false);
            });
        });
    })
</script>
