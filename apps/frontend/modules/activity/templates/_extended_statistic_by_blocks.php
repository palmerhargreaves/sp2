<?php
$sections = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->where('activity_id = ? and section_template_id != 0', $activity->getId())
    ->orderBy('section_template_id ASC')
    ->execute();

foreach ($sections as $section):
    $fields = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ? and parent_id = ?', array($activity->getId(), $section->getId()))->orderBy('position ASC')->execute();

    if (count($fields) == 0) {
        continue;
    }
    ?>
    <div class="group open">
        <div class="group-header">
            <span class="title"><?php echo $section->getHeader(); ?></span>
        </div>

        <div class="group-content">
            <table class="models">
                <tbody>
                <?php

                $n = 0;
                foreach ($fields as $field):
                    $fieldValue = $field->getFieldUserValue($activity, $sf_user->getRawValue(), $concept, $current_year, $current_q);
                    ?>
                    <?php if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_TEXT): ?>
                    <tr class="">
                        <td colspan="2">
                            <strong style='font-size: 12px;'><?php echo $field->getHeader(); ?></strong>
                        </td>
                    </tr>
                <?php else: ?>

                    <tr class="sorted-row model-row<?php if ($n++ % 2 == 0) echo ' even'; ?>">

                        <td style="width:605px; font-weight: bold; padding-left: 22px; <?php echo $field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC ? 'background: #D3D3D3' : ''; ?>">
                            <?php
                            echo $field->getHeader();
                            if ($field->getDescription())
                                echo sprintf(', (%s)', $field->getDescription());
                            ?>
                        </td>
                        <td class="darker"
                            style='<?php echo $field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC ? 'background: #D3D3D3' : ''; ?>'>
                            <?php
                            if ($field->getValueType() == "date") {
                                $period = explode("-", $fieldValue->getValue());

                                ?>
                                <a href="#" class="dates"/>
                                <div class="modal-input-wrapper input"
                                     style='width: 100px; margin: 7px; float: left;'>
                                    <input type='text' name="periodStart" class='with-date'
                                           style='height: 24px; padding: 5px; width: 110px;'
                                        <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                           placeholder='От'
                                           value="<?php echo isset($period[ 0 ]) && !empty($period[ 0 ]) ? $period[ 0 ] : ''; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$"
                                           data-field-id="<?php echo $fieldValue->getId(); ?>"
                                           data-step-id="<?php echo $step->getId(); ?>"
                                           required="true">
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                </div>
                                <div class="modal-input-wrapper input"
                                     style='width: 124px; margin: 7px; float: right;'>
                                    <input type='text' name="periodEnd" class='with-date'
                                           style='height: 24px; padding: 5px; width: 110px;'
                                        <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                           placeholder='До'
                                           value="<?php echo isset($period[ 1 ]) && !empty($period[ 1 ]) ? $period[ 1 ] : ''; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$"
                                           data-field-id="<?php echo $fieldValue->getId(); ?>"
                                           data-step-id="<?php echo $step->getId(); ?>"
                                           required="true">
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                </div>
                            <?php } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_MONEY) { ?>
                                <?php echo $field->getRequired() ? "<span title='Поле, обязательное для заполнения' style='color: red; float: right; margin: 24px 4px 0px 0px; font-size: 20px;'>*</span>" : ""; ?>

                                <?php $money = explode(':', $fieldValue->getValue()); ?>
                                <div class="modal-input-wrapper input" style='width: 100px; margin: 7px; float: left;'>
                                    <input type='text' name="moneyCurrency" style='height: 24px; padding: 5px; width: 100px;'
                                        <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                           placeholder='руб.'
                                           value="<?php echo isset($money[ 0 ]) && !empty($money[ 0 ]) ? $money[ 0 ] : ''; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="/^[0-9.]+$/"
                                           data-field-id="<?php echo $fieldValue->getId(); ?>"
                                           data-step-id="<?php echo $step->getId(); ?>"
                                        <?php echo $field->getRequired() ? "required" : ""; ?>>
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                </div>
                                <div class="modal-input-wrapper input" style='width: 115px; margin: 7px; float: right;'>
                                    <input type='text' name="moneyCoins"
                                           style='height: 24px; padding: 5px; width: 100px;'
                                        <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                           placeholder='коп.'
                                           value="<?php echo isset($money[ 1 ]) && !empty($money[ 1 ]) ? $money[ 1 ] : ''; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="/^[0-9.]+$/"
                                           data-field-id="<?php echo $fieldValue->getId(); ?>"
                                           data-step-id="<?php echo $step->getId(); ?>"
                                        <?php echo $field->getRequired() ? "required" : ""; ?>>
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                </div>
                            <?php } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC) { ?>
                                <strong class='calc-field calc-field-<?php echo $field->getId(); ?>'
                                        data-id='<?php echo $field->getId(); ?>'
                                        data-calc-fields='<?php echo $field->getCalcFields(); ?>'
                                        data-calc-type='<?php echo $field->getCalculateSymbol(); ?>'
                                        data-calc-parent-field='<?php echo $field->getParentCalcField(); ?>'>
                                    <?php echo $field->calculateValue($sf_user, '', $concept); ?>
                                </strong>
                            <?php } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_FILE) { ?>
                                <div class="file">
                                    <?php echo $field->getRequired() ? "<span title='Поле, обязательное для заполнения' style='color: red; float: right; margin: 24px 4px 0px 0px; font-size: 20px;'>*</span>" : ""; ?>
                                    <div class="modal-file-wrapper input" style="width: 290px;">
                                        <div id="container_model_extended_statistic_files"
                                             class="control dropzone"
                                             style="min-height: 158px">
                                            <div id="model_files_caption" class="caption">Для выбора
                                                файлов
                                                нажмите
                                                на
                                                кнопку
                                                или перетащите
                                                их сюда
                                            </div>

                                            <input type="file"
                                                   name="field_file_<?php echo $fieldValue->getId(); ?>"
                                                <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                                   data-step-id="<?php echo $step->getId(); ?>"
                                                   class='js-dealer-extended-statistics-upload-file field-<?php echo $fieldValue->getId(); ?>'
                                                   size="1"
                                                <?php echo $field->getRequired() && !$fieldValue->getValue() ? "required" : ""; ?>>
                                            <div class="modal-input-error-icon error-icon"></div>
                                            <div class="error message"
                                                 style='display: none; z-index: 1;'></div>
                                        </div>
                                    </div>

                                    <div class="value file-name">
                                        <?php if ($fieldValue): ?>
                                            <a href="<?php echo url_for('@on_download_activity_field_file?id=' . $fieldValue->getId() . '&type=extended'); ?>">
                                                <?php echo $fieldValue->getValue(); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="clear"></div>
                                    <div class="modal-form-uploaded-file"></div>
                                </div>

                            <?php } else { ?>
                                <?php echo $field->getRequired() ? "<span title='Поле, обязательное для заполнения' style='color: red; float: right; margin: 24px 4px 0px 0px; font-size: 20px;'>*</span>" : ""; ?>
                                <div class="modal-input-wrapper input"
                                     style='width: 124px; margin: 7px; float: right;'>
                                    <input type='text' class='field-<?php echo $field->getId(); ?>'
                                           placeholder='0'
                                           style='height: 24px; padding: 5px; width: 110px;'
                                        <?php echo !$allow_to_edit ? "disabled" : ""; ?>
                                           data-type="<?php echo $field->getValueType(); ?>"
                                        <?php if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_VALUE) { ?>
                                            data-regexp="/^[0-9.]+$/"
                                        <?php } else { ?>
                                            data-regexp="/^[0-9a-zA-Zа-яА-Я\_\(\)\+\-\= ]+$/"
                                        <?php } ?>
                                           data-field-id="<?php echo $fieldValue->getId(); ?>"
                                           value="<?php echo $fieldValue->getValue(); ?>"
                                        <?php echo $field->useInCalculate() ? "data-calc-field='true' data-calc-type='" . $field->getCalculateSymbol() . "' data-calc-parent-field='" . $field->getParentCalcField() . "'" : ''; ?>
                                        <?php echo $field->getRequired() ? "required" : ""; ?>>

                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message-modal" style="display: none;"></div>
                                </div>

                            <?php } ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
