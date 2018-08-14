<div>
	<div class="heading dark">
		<h1>FAQ</h1>
	</div>

	<div class="anons-news">
	<?php
		foreach($faqs as $info):
			$img = $info->getImage();
	?>
		<div class="item">
			<div class="date"><?php echo $info->getCreatedAt(); ?></div>

			<div class="content">
				<div class="anons">
					<h3><span><?php echo $info->getRawValue()->getQuestion(); ?></span></h3>
					<p class="text"><?php echo Utils::trim_text($info->getRawValue()->getAnswer(), 150, true, false); ?></p>
				</div>
				<div class="full">
				<?php
					if(!empty($img)):
				?>
					<div class="preview"><img src="/uploads/news/images/<?php echo $img; ?>" alt="<?php echo $info->getRawValue()->getQuestion(); ?>"></div>
				<?php endif; ?>

					<h3><span><?php echo $info->getRawValue()->getQuestion(); ?></span></h3>
					<p class="text"><?php echo $info->getRawValue()->getAnswer(); ?></p>
				</div>
			</div>
			<div class="more"><span></span></div>
		</div>
	<?php endforeach; ?>

	</div>
</div>