<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 03.11.2015
 * Time: 16:23
 */
?>

<table class="dealers-table" id="status-table" style="z-index:9;">
    <thead>
    <tr>
        <td class="header" style="height: 185px;">
            <!--<a href="#" class="save">���������</a>-->
            <h1 style="margin-top: 16px;">������ ���������� �����������</h1>

            <form action="<?php url_for('@agreement_module_activities_status') ?>" method="get">
                <select name="year">
                    <option value=''>�������� ���</option>
                    <?php
                    foreach ($budgetYears as $item):
                        $sel = "";
                        if ($item == $year)
                            $sel = "selected";
                        ?>
                        <option
                            value="<?php echo $item; ?>" <?php echo $sel; ?>><?php echo "������ �� " . $item . " �."; ?></option>
                        <?php
                    endforeach;
                    ?>
                </select>

                <select name="quarter">
                    <option value="">�� ���� ���</option>
                    <option value="1"<?php echo $quarter == 1 ? ' selected' : '' ?>>�� I �������</option>
                    <option value="2"<?php echo $quarter == 2 ? ' selected' : '' ?>>�� II �������</option>
                    <option value="3"<?php echo $quarter == 3 ? ' selected' : '' ?>>�� III �������</option>
                    <option value="4"<?php echo $quarter == 4 ? ' selected' : '' ?>>�� IV �������</option>
                </select>

                <input placeholder="������ �� �������" class="filter" type="text" name="dealer"
                       value="<?php echo $dealer ?>"/>
            </form>
        </td>
        <td class="activity" title="������� ���������� �������">
            <div>
                <span>������� ���������� �������</span>
            </div>
        </td>
        <td class="activity" title="����������� ����������� � ������ ����">
            <div>
                <span>��������� � ������ ����</span>
            </div>
        </td>
        <?php if ($quarter): ?>
            <td class="activity" title="����������� ����������� �� �������">
                <div>
                    <span>��������� �� �������</span>
                </div>
            </td>
        <?php endif; ?>
        <?php foreach ($activities as $activity_row): ?>
            <?php $activity = $activity_row['activity'];
            if ($activity): ?>
                <td class="activity" title="<?php echo $activity->getName() ?>">
                    <div><span style="overflow: initial; left: -57px;"><?php echo $activity->getName() ?></span></div>
                </td>
            <?php endif;
        endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td></td>
        <td title="������� % ���������� �������"><?php echo round($total['average_percent']) ?>%</td>
        <td></td>
        <?php if ($quarter): ?>
            <td><?php echo round($total['accepted_required']) ?>%</td>
        <?php endif; ?>
        <?php foreach ($total['accepted'] as $count): ?>
            <td title="���-�� �������, ����������� ����������"><?php echo $count; ?></td>
        <?php endforeach; ?>
    </tr>
    <tr class="dealer ">
        <td>���������� �������, ����������� ����������</td>
        <td></td>
    </tr>
    <tr class="dealer odd">
        <td>���������� �������, � ������</td>
        <td></td>
    </tr>

    <?php foreach ($managers as $manager): ?>
        <tr class="regional-manager filter-group">
            <td class="header">
                <div><?php echo $manager['manager'] ?></div>
            </td>
            <td></td>
            <td></td>
            <?php if ($quarter): ?>
                <td></td>
            <?php endif; ?>
            <?php for ($i = 0, $l = count($activities); $i < $l; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <?php foreach ($manager['dealers'] as $n => $dealer): ?>
            <tr class="dealer <?php if ($n % 2 == 0) echo ' odd'; ?>" data-filter="<?php echo $dealer ?>">
                <td class="header">
                    <div><span class="num"><?php echo $dealer->getShortNumber() ?></span> <a
                            href="/activity/module/agreement/dealers/<?php echo $dealer->getId() ?>"><?php echo $dealer->getName() ?></a>
                    </div>
                </td>
                <?php $dealer_stat = $builder->getDealerStat($dealer->getRawValue()); ?>
                <td><?php echo round($dealer_stat['budget']['percent']) ?>%</td>
                <td><?php echo round($dealer_stat['year_accepted']) ?></td>
                <?php if ($quarter): ?>
                    <td><?php echo round($dealer_stat['quarter_accepted']) ?></td>
                <?php endif; ?>
                <?php foreach ($activities as $activity_row): ?>
                    <?php $activity = $activity_row['activity'];
                    if (!$activity)
                        continue;
                    ?>
                    <td class="<?php echo $dealer_stat['statuses'][$activity->getRawValue()->getId()] ?>">
                        <?php
                        if ($dealer_stat['statuses'][$activity->getRawValue()->getId()] != 'none')
                            //url_for('@default?module=frontend&action=ActivitiesStatus&id='.$job->getId())
                            //'/activity/module/agreement/activities/".$activity->getRawValue()->getId()."'
                            echo "<a href='/activity/module/agreement/activities/" . $activity->getRawValue()->getId() . "?dealer=" . $dealer->getId() . "'>&nbsp</a>";
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
</table>

