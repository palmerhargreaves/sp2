<div>
	<div class="heading dark">
		<h1>Все новости</h1>
	</div>

	<div class="anons-news">
	<?php
		foreach($news as $item):
			include_partial('item', array('info' => $item['item'], 'item' => $item));
	?>
	<?php endforeach; ?>

	</div>
</div>