<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.10.2016
 * Time: 14:36
 */

if ($model && $last_message):
    ?>
    <div class="date"><?php echo $last_message->getCreatedAt(); ?></div>
    <div class="title"><?php echo $model->getDealerActionText(); ?></div>
    <div class="text">
        <div class="scroller-short scroller-discussion-messages">
            <div class="scrollbar">
                <div class="track">
                    <div class="thumb">
                        <div class="end"></div>
                    </div>
                </div>
            </div>

            <div class="viewport scroller-wrapper">
                <div class="overview scroller-inner">
                    <?php if (count($users_messages) > 0): ?>
                        <?php foreach ($users_messages as $key => $msg): ?>
                            <p><?php echo $msg->getText(); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($messages_files) > 0): ?>
    <div class="notice"><?php echo !empty($msg_text) ? $msg_text : "Вам необходимо внести комментарии специалистов:"; ?></div>
    <div class="modal-file-wrapper preview input js-view-box">
        <div class="control">

            <div class="uploaded-files-header">
                <i class="view-list js-view-toggle" data-view="list" data-toggle="toggle-view-box-last-message-files"
                   title="Списком"></i>
                <i class="view-grid js-view-toggle" data-view="grid" data-toggle="toggle-view-box-last-message-files"
                   title="Таблицей"></i>
                <a href="javascript:" class="download-discussion-message-files" target="_blank"
                   data-url="<?php echo url_for('@agreement_model_discussion_load_files'); ?>"
                   data-from-messages="<?php echo implode(":", array_keys($users_messages->getRawValue())); ?>">Скачать
                    все</a>
            </div>

            <div class="scroller scroller-discussion-uploaded-files" style="height: 200px;">
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb">
                            <div class="end"></div>
                        </div>
                    </div>
                </div>
                <div class="viewport scroller-wrapper" style="height: 200px;">
                    <div class="overview scroller-inner">

                        <div class="d-popup-uploaded-files d-cb" data-toggled="toggle-view-box-last-message-files">
                            <?php foreach ($messages_files as $file): ?>
                                <a href="<?php echo url_for('@agreement_model_discussion_message_download_file?file=' . $file->getId()) ?>"
                                   target="_blank"
                                   data-toggle="tooltip"
                                   data-placement="top"
                                   title="<?php echo $file->getFile(); ?>"
                                   class="d-popup-uploaded-file <?php echo !F::isImage($file->getFileName()) ? 'odd ' . F::getFileExt($file->getFileName()) : ''; ?>">
                                    <?php if (F::isImage($file->getFile())): ?>
                                        <i><b><img src="/uploads/<?php echo MessageFile::FILE_PATH . '/' . $file->getFileName(); ?>"/></b></i>
                                    <?php else: ?>
                                        <i></i>
                                    <?php endif; ?>
                                    <strong><?php echo $file->getFile() ?></strong>
                                    <em><?php echo $file->getFileNameHelper()->getSmartSize() ?></em>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>
