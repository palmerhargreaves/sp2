<?php
	$fileUrl = $sf_user->getAuthUser()->getGazetaFiles();
	$fileOffsetUrl = $sf_user->getAuthUser()->getGazetaOffsetFiles();
?>

<div id="gazeta-files-modal" class="intro modal" style="width: 440px; left: 40%;">
    <div class="modal-header">Уважаемый дилер!</div>
    <div class="modal-close"></div>
    <div class="modal-text">
    
    	<p>Мы подготовили новые макеты осеннего номера газеты Service Offensive. Используйте для печати только файл, размещенный ниже. </p>
		<p>Приносим извинения за неудобства.</p>

		<p>Новый файл с газетой <a href='<?php echo url_for('gazeta_file'); ?>' target='_blank'>(ссылка на скачивание)</a></p>
		<?php if(!empty($fileOffsetUrl)): ?>
			<p>Новый файл с газетой, версия для офсетной печати <a href='<?php echo url_for('gazeta_offset_file'); ?>' target='_blank'>(ссылка на скачивание)</a></p>
		<?php endif; ?>
		<p>Файл с требованиями <a href='/uploads/gazeta/trebovania_k_pechati.docx'>(ссылка на скачивание)</a></p>
    </div>
</div>

