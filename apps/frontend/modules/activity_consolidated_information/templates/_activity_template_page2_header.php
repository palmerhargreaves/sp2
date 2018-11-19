<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <style></style>
</head>

<?php $manager = $consolidated_information->getManager(); ?>
<body>
<div id="d-wrap" class="page-wrap is-flexbox">
    <header id="d-header">
        <div class="d-grid">

            <div class="d-header d-header_sm is-flexbox">
                <div class="d-header__manager d-bsbb fs_xl">
                    <?php if ($manager): ?>
                        <strong>Региональный менеджер</strong>
                        <?php echo $manager->getPersonFullName(); ?>
                    <?php else: ?>
                        <strong>Все дилеры</strong>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>


