<?php decorate_with('layout_links') ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
    <title>Volkswagen Service</title>
    <link rel="icon" type="image/png" href="favicon.ico"/>
    <link media="screen" href="css/fonts.css" type="text/css" rel="stylesheet"/>
    <link media="screen" href="css/style.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/libs.js"></script>
</head>
<body>
<div id="wrap">
    <div id="layout">
        <div id="header">
            <div class="logo">
                <img src="images/logo_main.png" alt="Volkswagen Service">
            </div>
        </div>
        <div id="main">
            <div class="menu">
                <?php
                $dealer = $sf_user->getAuthUser()->getDealer();

                foreach (MainMenuItemsTable::getInstance()->createQuery()->select()->orderBy('position ASC')->execute() as $menu_item):
                    include_partial('nav_menu_item', array('menu_item' => $menu_item, 'dealer' => $dealer, 'user' => $sf_user->getAuthUser()));
                endforeach;
                ?>
                <div style="clear:both"></div>
            </div>
        </div>
        <div id="footer">
            <div class="copyright">
                &copy;<a href="" target="_blank"> Volkswagen</a> |
                <a href="http://www.volkswagen.ru/" target="_blank">Фольксваген Россия</a> |
                <a href="http://www.volkswagen.ru/ru/tools/navigation/footer/rights.html" target="_blank">Правовые
                    аспекты</a>
            </div>
            <div class="company">
                <a href="http://www.volkswagenag.com/content/vwcorp/content/de/homepage.html" target="_blank">Volkswagen
                    AG</a> |
                <a href="http://www.volkswagen.com/" target="_blank">Volkswagen International</a>
            </div>
        </div>


    </div>
</div>
</body>
</html>
