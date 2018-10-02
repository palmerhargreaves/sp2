<?php
$sections = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->where('activity_id = ? and status = ?', array($activity->getId(), true))
    ->orderBy('section_template_id ASC')
    ->execute();

foreach ($sections as $section):
    ?>
    <div class="group open">
        <div class="group-header">
            <span class="title"><?php echo $section->getHeader(); ?></span>
        </div>

        <div class="group-content">
            <?php if ($section->getDescription()): ?>
                <div class="alert alert-callout alert-info" role="alert" style="display: block; margin-top: 5px;">
                    <strong><?php echo $section->getDescription(); ?></strong>
                </div>
            <?php endif; ?>

            <table class="models">
                <tbody>
                <?php
                $fields = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()
                    ->where('activity_id = ? and parent_id = ?', array($activity->getId(), $section->getId()))->orderBy('position ASC')->execute();

                $n = 0;
                foreach ($fields as $field):
                    $dealer_group = $field->getDealersGroup();
                    if (!empty($dealer_group) && $field->isLimitedAccessForUser($sf_user)) {
                        continue;
                    }

                    if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC && !$field->getShowInStatistic()) {
                        continue;
                    }

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
                                           style='height: 24px; padding: 5px; width: 110px;' placeholder='От'
                                           value="<?php echo $period[0]; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$"
                                           data-field-id="<?php echo $fieldValue->getFieldId(); ?>" required="true">
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                </div>
                                <div class="modal-input-wrapper input"
                                     style='width: 124px; margin: 7px; float: right;'>
                                    <input type='text' name="periodEnd" class='with-date'
                                           style='height: 24px; padding: 5px; width: 110px;' placeholder='До'
                                           value="<?php echo $period[1]; ?>"
                                           data-type="<?php echo $field->getValueType(); ?>"
                                           data-regexp="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$"
                                           data-field-id="<?php echo $fieldValue->getFieldId(); ?>" required="true">
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
                                        <div id="container_model_extended_statistic_files" class="control dropzone"
                                             style="min-height: 158px">
                                            <div id="model_files_caption" class="caption">Для выбора файлов нажмите
                                                на
                                                кнопку
                                                или перетащите
                                                их сюда
                                            </div>

                                            <input type="file" name="field_file_<?php echo $fieldValue->getFieldId(); ?>"
                                                   class='js-dealer-extended-statistics-upload-file field-<?php echo $fieldValue->getFieldId(); ?>'
                                                   size="1"
                                                <?php echo $field->getRequired() ? "required" : ""; ?>>
                                            <div class="modal-input-error-icon error-icon"></div>
                                            <div class="error message" style='display: none; z-index: 1;'></div>
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
                                           data-type="<?php echo $field->getValueType(); ?>"
                                        <?php if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_VALUE) { ?>
                                            data-regexp="/^[0-9.]+$/"
                                        <?php } else { ?>
                                            data-regexp="/^[0-9a-zA-Zа-яА-Я\_\(\)\+\-\= ]+$/"
                                        <?php } ?>
                                           data-field-id="<?php echo $fieldValue->getFieldId(); ?>"
                                           value="<?php echo !$field->getEditable() && $field->getDefValue() != 0 ? $field->getDefValue() : $fieldValue->getValue(); ?>"
                                        <?php if (!$field->getEditable()): ?>
                                            disabled
                                        <?php endif; ?>
                                        <?php echo !$allow_to_edit ? "disabled" : "" ; ?>
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
