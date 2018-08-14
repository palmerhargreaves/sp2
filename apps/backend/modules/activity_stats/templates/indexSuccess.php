<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">Статистика по активностям</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div class="alert alert-success" style="display: none;">

                </div>

                <form action="<?php echo url_for('activity_stats/index') ?>" method="get" class="form-inline"
                      id="activity-stats-form">

                    <ul class="nav nav-list">
                        <li class="nav-header">Активность</li>
                        <li>
                            <select id='activity_filter' name='activity_filter' style='width: 300px;'>
                                <option value="">Выберите активность ...</option>
                                <?php foreach ($activities as $activity): ?>
                                    <option
                                            value="<?php echo $activity->getId() ?>" <?php echo $activity_filter == $activity->getId() ? 'selected' : ''; ?>><?php echo sprintf('[%s] - %s', $activity->getId(), $activity->getName()) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>

                        <li class="nav-header">Категория</li>
                        <li>
                            <select id='activity_category_filter' name='activity_category_filter' style='width: 300px;'>
                                <option value="">Выберите категорию ...</option>
                                <?php foreach ($activities_categories as $category): ?>
                                    <option
                                            value="<?php echo $category->getId() ?>" <?php echo $activity_category_filter == $category->getId() ? 'selected' : ''; ?>><?php echo $category->getName(); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>

                        <li class="nav-header">Категория + Тип</li>
                        <li>
                            <select id='activity_category_type_filter' name='activity_category_type_filter'
                                    style='width: 300px;'>
                                <option value="">Выберите тип ...</option>
                                <?php foreach ($activities_categories_for_types as $category): ?>
                                    <?php $name = $category->getName(); ?>
                                    <?php if (empty($name)) {
                                        continue;
                                    } ?>

                                    <optgroup label="<?php echo $name; ?>">
                                        <?php foreach ($category->getCategoryTypes() as $category_type): ?>
                                            <option value="<?php echo $category_type->getId() ?>" <?php echo $activity_category_type_filter == $category_type->getId() ? 'selected' : ''; ?>><?php echo $category_type->getName(); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </li>

                        <li class="nav-header">Период</li>
                        <li>
                            <select id='filter_by_quarter' name='filter_by_quarter'>
                                <option value="-1">Выберите квартал ...</option>
                                <?php
                                for ($i = 1; $i <= 4; $i++) {
                                    $sel = '';
                                    if ($activity_filter_quarter == $i) $sel = 'selected';

                                    echo "<option value={$i} {$sel}>{$i}</option>";
                                }
                                ?>
                            </select>
                            -
                            <select id='filter_by_month' name='filter_by_month'>
                                <option value="-1">Выберите месяц ...</option>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    $sel = '';
                                    if ($activity_filter_month == $i) $sel = 'selected';

                                    echo "<option value='" . ($i < 10 ? '0' . $i : $i) . "' {$sel}>{$i}</option>";
                                }
                                ?>
                            </select>
                            -
                            <select id='filter_by_year' name='filter_by_year'>
                                <?php
                                for ($i = 2012; $i <= date('Y'); $i++) {
                                    echo "<option value={$i} " . ($activity_filter_year == $i ? 'selected' : '') . ">{$i}</option>";
                                }
                                ?>
                            </select>
                        </li>

                        <li>
                            <label for='report_complete'>Выполненные отчеты</label>
                            <input type='checkbox' name='report_complete'
                                   id='report_complete' <?php echo $activity_report_complete ? "checked" : "" ?>>
                        </li>

                        <li>
                            <label for='work_in_redactor'>Макет выполнен с помощью редактора</label>
                            <input type='checkbox' name='work_in_redactor'
                                   id='work_in_redactor' <?php echo $activity_filter_redactor ? "checked" : "" ?>>
                        </li>
                    </ul>

                </form>
            </div>
        </div>
    </div>
</div>

<?php
if ($models) {
    ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">
                    <ul class="nav nav-list">
                        <li class="nav-header">Список макетов (всего: <?php echo count($models); ?>)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">

                    <input name="btExportToExcel" type="button" class="btn" value="Выгрузить в Excel"
                           style="float: right; margin-bottom: 5px;"
                           data-activity="<?php echo $activity_filter; ?>"
                           data-filter-by-quarter="<?php echo $activity_filter_quarter; ?>"
                           data-filter-by-month="<?php echo $activity_filter_month; ?>"
                           data-filter-by-year="<?php echo $activity_filter_year; ?>"
                           data-work-in-redactor="<?php echo $activity_filter_redactor; ?>"
                           data-report-complete="<?php echo $activity_report_complete; ?>"
                           data-category-filter="<?php echo $activity_category_filter; ?>"
                           data-category-type-filter="<?php echo $activity_category_type_filter; ?>"
                    >

                    <table class="table table-bordered table-striped " cellspacing="0">
                        <thead>
                        <tr>
                            <th>№ дилера</th>
                            <th>Дилер</th>
                            <th style="text-align: center;">Номер макета</th>
                            <th>Название макета</th>
                            <th style="text-align: center;">Категория</th>
                            <th style="text-align: center;">Тип</th>
                            <th>Размер (если есть)</th>
                            <th>Период</th>
                            <th>Дата создания</th>
                        </tr>
                        </thead>

                        <?php
                        foreach ($models as $key => $model_item) {
                            $model_item = $model_item->getRawValue();
                            if (isset($model_item['model'])) {
                                $model = array_values($model_item['model']);
                                $model = isset($model[0]) ? $model[0] : null;
                            } else {
                                $model = $model_item;
                            }

                            if (is_null($model)) {
                                continue;
                            }

                            $dealer = $model['Dealer'];
                            $fields = AgreementModelFieldTable::getInstance()->createQuery()->select()->where('model_type_id = ?', $model['model_type_id'])->andWhere('identifier = ? or identifier = ?', array('period', 'size'))->execute();
                            ?>
                            <tr>
                                <td class="span1"><?php echo substr($dealer['number'], -3); ?></td>
                                <td class="span3"><?php echo $dealer['name']; ?></td>
                                <td class="span3" style="text-align: center;"><?php echo $model['id']; ?></td>
                                <td class="span3">
                                    <?php echo $model['name']; ?> <br/>
                                    <?php echo $model['updated_at']; ?>
                                </td>
                                <td class="span3"><?php echo $model['ModelCategory']['category_name']; ?></td>
                                <td class="span3"><?php echo $model['ModelType']['type_name']; ?></td>
                                <td class="span3">
                                    <?php
                                    foreach ($fields as $field) {
                                        if ($field->getIdentifier() == 'size') {
                                            $value = AgreementModelValueTable::getInstance()->createQuery()->select()->where('model_id = ? and field_id = ?', array($model['id'], $field->getId()))->fetchOne();
                                            if ($value) {
                                                echo $value->getValue();
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="span3">
                                    <?php
                                    if (!empty($model['period'])) {
                                        echo $model['period'];
                                    } else {
                                        foreach ($fields as $field) {
                                            if ($field->getIdentifier() == 'period') {
                                                $value = AgreementModelValueTable::getInstance()->createQuery()->select()->where('model_id = ? and field_id = ?', array($model['id'], $field->getId()))->fetchOne();
                                                if ($value) {
                                                    echo $value->getValue();
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="span3">
                                    <?php echo $model['created_at']; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php } ?>

<script type="text/javascript">
    $(document).on('change', '#activity_filter, #filter_by_quarter, #filter_by_month, #filter_by_year, #activity_category_type_filter', function () {
        $('#activity-stats-form').submit();
    });

    $(document).on('change', '#activity_category_filter', function () {
        $('#activity_category_type_filter').val('');

        $('#activity-stats-form').submit();
    });


    $(document).on('click', '#work_in_redactor, #report_complete', function () {
        $('#activity-stats-form').submit();
    });

    $(document).on('click', 'input[name=btExportToExcel]', function () {
        window.location.href = "<?php echo url_for('@activity_export_to_excel'); ?>?activity_filter=" + $(this).data('activity')
            + "&filter_by_quarter=" + $(this).data('filter-by-quarter')
            + "&filter_by_month=" + $(this).data('filter-by-month')
            + "&filter_by_year=" + $(this).data('filter-by-year')
            + "&work_in_redactor=" + $(this).data('work-in-redactor')
            + "&report_complete=" + $(this).data('report-complete')
            + "&activity_category_filter=" + $(this).data('category-filter')
            + "&activity_category_type_filter=" + $(this).data('category-type-filter');
    });
</script>
