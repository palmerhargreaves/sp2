<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 14.08.2017
 * Time: 11:32
 */

$text = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $message['text']);
$text = html_entity_decode($text);

$contact = null;
if ($message['contact_id'] != 0) {
    $contact = ContactsTable::getInstance()->find($message['contact_id']);
}

$contact_color = '';
$user_color = '';
if ($contact) {
    $contact_color = $contact->getColor();
    $user_color = '';

    $user_send_contact = ContactsTable::getInstance()->createQuery()->where('user_id = ?', $message['User']['id'])->fetchOne();
    if ($user_send_contact) {
        $user_color = $user_send_contact->getColor();
    }
}
?>

<div class="comment <?php echo $message_index % 2 == 0 ? '' : 'comment_reply' ; ?>" data-messages-type="<?php echo $message['system'] == 1 ? 'system' : 'ask'; ?>">
    <div class="comment__header">
        <strong>
            <?php if (!$contact): ?>
                <?php echo sprintf('%s %s', $message['User']['name'], $message['User']['surname']); ?>
            <?php else: ?>
                <?php echo sprintf('%s %s %s',
                    "<span style='color: {$user_color};'>" .$message['User']['name'], $message['User']['surname']."</span>",
                    "---> (<span style='color: {$contact_color};'>" . $contact->getUserName() . "</span>)"
                ); ?>
            <?php endif; ?>
        </strong>
        <span><?php echo date('d.m.Y', strtotime($message['created_at'])); ?></span>
        <span><?php echo date('H:i', strtotime($message['created_at'])); ?></span>

        <?php if ($contact): ?>
            <?php if ($message['contact_id'] != $sf_user->getAuthUser()->getId()): ?>
                <i class="js-reply-on-comment" data-message-id="<?php echo $message['id']; ?>" data-message-type="<?php echo $message_item['message_type']; ?>" data-msg="<?php echo htmlspecialchars($text);?>"></i>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($message['User']['id'] != $sf_user->getAuthUser()->getId()): ?>
                <i class="js-reply-on-comment" data-message-id="<?php echo $message['id']; ?>" data-message-type="<?php echo $message_item['message_type']; ?>" data-msg="<?php echo htmlspecialchars($text);?>"></i>
            <?php endif; ?>
        <?php endif; ?>
    </div>

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
                            <a href="<?php echo $file->getFile() ?>" target="_blank"><?php echo $file->getFileName() ?>
                                (<?php echo Utils::getRemoteFileSize($file->getFile()) ?>)</a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

