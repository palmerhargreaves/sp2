<?php foreach ($messages as $message): ?>
    <div class="message<?php if ($message->user_id == $sf_user->getAuthUser()->getId()) echo ' answer' ?>"
         data-message="<?php echo $message->getId() ?>"
         style="<?php echo $message->user_id == $sf_user->getAuthUser()->getId() ? 'width: 415px; margin-left: 25%;' : "width: 615px;" ?>"">
    <div class="name online" data-user="<?php echo $message->getUserId() ?>">
        <div class="icon"></div><?php echo $message->getUserName() ?></div>
    <div class="time">
        <?php echo date('H:i', D::toUnix($message->created_at)) ?>
        <?php $date = D::toShortRus($message->created_at) ?>
        <?php if ($date): ?>
            <span><?php echo $date ?></span>
        <?php endif; ?>
    </div>
    <div class="body" style="word-wrap: break-word;">
        <?php
            $text = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $message->getRawValue()->getText());
        ?>

        <div class="corner"></div><?php echo nl2br($text) ?>
        <?php if (isset($files[$message->getId()])): ?>
            <div class="attachments">
                <div class="d-popup-uploaded-files d-cb" style="padding-bottom: 10px;">
                    <?php foreach ($files[$message->getId()] as $file): ?>

                        <?php if (!$file->getEditor()): ?>
                            <span style="width: 100px;"
                                  class="d-popup-uploaded-file <?php echo !F::isImage($file->getFile()) ? 'odd ' . F::getFileExt($file->getFile()) : ''; ?>"
                                  data-delete="false"
                                  data-toggle="tooltip"
                                  data-placement="top"
                                  title="<?php echo $file->getFile(); ?>">
                            <?php if (F::isImage($file->getFile())): ?>
                                <i><b><img src="/uploads/<?php echo MessageFile::FILE_PATH . '/' . $file->getFileName(); ?>"/></b></i>
                            <?php else: ?>
                                <i></i>
                            <?php endif; ?>
                                <strong>
                                    <a href="<?php echo url_for("@agreement_model_discussion_message_download_file?file=" . $file->getId()) ?>"
                                       target="_blank"><?php echo $file->getFile() ?>
                                    </a>
                                </strong>
                                <em>(<?php echo $file->getFileNameHelper()->getSmartSize() ?>)</a></em>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo $file->getFile() ?>" target="_blank"><?php echo $file->getFileName() ?>
                                (<?php echo Utils::getRemoteFileSize($file->getFile()) ?>)</a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else:
            $messageFiles = MessageFileTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();
            foreach ($messageFiles as $file):
                ?>
                <?php if (!$file->getEditor()): ?>
                <!--<a href="/uploads/<?php echo MessageFile::FILE_PATH, '/', $file->getFile() ?>" target="_blank"><?php echo $file->getFile() ?> (<?php echo $file->getFileNameHelper()->getSmartSize() ?>)</a>-->
                <a href="<?php echo url_for("@agreement_model_discussion_message_download_file?file=" . $file->getId()) ?>"
                   target="_blank"
                   data-toggle="tooltip"
                   data-placement="top"
                   title="<?php echo $file->getFile(); ?>"><?php echo $file->getFile() ?>
                    (<?php echo $file->getFileNameHelper()->getSmartSize() ?>)</a>
            <?php else: ?>
                <a href="<?php echo $file->getFile() ?>" target="_blank"><?php echo $file->getFile() ?>
                    (<?php echo Utils::getRemoteFileSize($file->getFile()) ?>)</a>
            <?php endif; ?>
                <?php
            endforeach;
            ?>

        <?php endif; ?>
    </div>
    </div>
<?php endforeach; ?>
