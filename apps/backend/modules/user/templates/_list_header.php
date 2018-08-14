<?php $users_stats = new UsersStatistics(); ?>
<hr/>
<table class="table table-striped" style="width: 100%;">
    <?php $idx = 0; ?>
    <?php foreach ($users_stats->build() as $key => $item): ?>
        <?php echo $idx % 5 == 0 ? "<tr>" : ""; ?>
        <td class="span2"><?php echo $item['label']; ?></td>
        <td class="span1"><?php echo $item['count']; ?></td>
        <?php echo $idx % 5 == 4 ? "</tr>" : ""; ?>
        <?php $idx++; ?>
    <?php endforeach; ?>

</table>

<hr/>


