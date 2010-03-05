<?php

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

try {
    $pdo = new PDO(DBH);
} catch (PDOException $e) {
    die("pdo error: ".$e->getMessage());
}

$tpl = new templates();
$tpl->addFile('_begin','header.tpl.php');
$tpl->addFile('_end','footer.tpl.php');
$tpl->addFile('index','index.tpl.php');

$tpl->title = "AYANS";


$news = new news_node();
$news->setPDO($pdo);
$news->setView(news_node::DEFAULT_VIEW);
$news->add_filter('Markdown','text');

// Comment this if you have some charset problems in cache
$news->add_filter('utf8_decode','title');
// -----

$news->add_filter('htmlentities','title');
$news->add_filter('set_bolder','author');
$tpl->content = $news->render((isset($_GET['reload']) ? true : false), true, news_node::FORMAT_FULL);



$tpl->render('index');

?>