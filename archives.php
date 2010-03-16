<?php
/**
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Original author: Ludovic Bellière <guru at xrogaan be>
 */

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';
require INCLUDES_PATH.'paginate.php';

try {
    $pdo = new PDO(DBH);
} catch (PDOException $e) {
    die("pdo error: ".$e->getMessage());
}

$tpl = new templates();

$tpl->addFile('_begin','header.tpl.php');
$tpl->addFile('_end','footer.tpl.php');
$tpl->addFile('archives','archives.tpl.php');

$tpl->title = "AYANS archives";

$news = new news_node();
$news->setPDO($pdo);
$news->setView(news_node::ARCHIVE_VIEW);
$news->add_filter('Markdown','text');

// Comment this if you have some charset problems
$news->add_filter('utf8_decode','title');

$news->add_filter('htmlentities','title');
$news->add_filter('set_bolder','author');

$dq = $pdo->query('SELECT COUNT(*) as elems FROM news');
$num = $dq->fetch(PDO::FETCH_ASSOC);
$pages = new paginate($num['elems'],20);

$tpl->content = $news->render((isset($_GET['reload']) ? true : false),$pages);


echo $tpl->render('archives');



?>