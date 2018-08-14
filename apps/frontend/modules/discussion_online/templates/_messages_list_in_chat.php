<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 04.09.2017
 * Time: 9:58
 */

if (count($messages_list) > 0):
    foreach ($messages_list as $message_item):
        $message = $message_item['data'];

        $text = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $message['text']);
        $text = html_entity_decode($text);

        ?>

        <div class="chat-message clearfix">
            <div class="chat-message-content <?php echo $message['User']['id'] != $sf_user->getAuthUser()->getId() ? 'comment' : ''; ?> clearfix">
                <span class="chat-time"><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></span>
                <strong><?php echo sprintf('%s %s', $message['User']['name'], $message['User']['surname']); ?></strong>

                <div class="comment__body" style="word-wrap: break-word;">
                    <?php echo $text; ?>
                    <?php if (count($message_item['files']) > 0): ?>
                        <div class="attachments">
                            <div class="d-popup-uploaded-files d-cb" style="padding-bottom: 10px;">
                                <?php foreach ($message_item['files'] as $file): ?>

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
                                        <a href="<?php echo $file->getFile() ?>"
                                           target="_blank"><?php echo $file->getFileName() ?>
                                            (<?php echo Utils::getRemoteFileSize($file->getFile()) ?>)</a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <!-- end chat-message-content -->
        </div> <!-- end chat-message -->

    <?php endforeach; ?>

<?php else: ?>

<?php endif;

