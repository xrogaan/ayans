<?php

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';
require INCLUDES_PATH.'paginate.php';

$pdo = new PDO(DBH);
$dq = $pdo->query('SELECT COUNT(*) as elems FROM news');
$num = $dq->fetch(PDO::FETCH_ASSOC);

$pages = new paginate($num['elems'],20);
$dq = $pdo->query('SELECT * FROM news ORDER BY id DESC '.$pages->get_sql_limit_statement());
$rows = $dq->fetchall();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>News - archives</title>
</head>
<body>
<h1>News - archives</h1>
<p><a href="index.php">back to home</a></p>
<?php
if (empty($rows)) {
	echo "<p>No news</p>\n";
} else {
	echo "<p>".$pages->paginate('archives.php')."</p>";
	$news = new news_node();
	$news->add_nodes($rows);
	$news->add_filter('Markdown','text');
	$news->add_filter('htmlentities','title');
	$news->add_filter('set_bolder','author');
	$reload = isset($_GET['reload']) ? true : false;
	echo $news->render($reload);
}
?>
</body>
</html>