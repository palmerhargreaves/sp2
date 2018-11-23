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

<?php $information = $information->getRawValue(); ?>
<body>
<div id="d-wrap" class="page-wrap is-flexbox">
    <header id="d-header">

        <div class="d-grid">
            <h1 class="d-ttu"><?php echo $information['quarter'];?> квартал</h1>
            <div class="d-header is-flexbox">
                <div class="d-header__company d-bsbb">
                    <h2 class="h1"><?php echo $information['dealer']['name']; ?></h2>
                    <h3><?php echo $information['dealer']['number']; ?></h3>
                </div>
                <div class="d-header__manager d-bsbb fs_xl">
                    <strong>Региональный менеджер</strong>
                    <?php echo $information['manager']->getPersonFullName(); ?>
                </div>
            </div>

        </div>

    </header>
