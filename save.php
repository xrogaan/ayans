<?php

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

$password_fail = false;
$title = (!empty($_POST['title'])) ? trim($_POST['title']) : 'Your title...';
$text = (!empty($_POST['text'])) ? trim($_POST['text']) : 'Your news...';

if (isset($_POST['password']) && PASSWORD != sha1($_POST['password'])) {
	$password_fail = true;die('fail !');
} elseif (isset($_POST['password']) && PASSWORD == sha1($_POST['password'])) {
    try {
        $pdo = new PDO(DBH);
        
        $dq = $pdo->exec('INSERT INTO news (title, text, author, postedon) VALUES('.
            $pdo->quote($title).','.
            $pdo->quote($text).','.
            $pdo->quote('xrogaan').','.
            $pdo->quote(time()).')');
        
        if ($dq === false) {
            echo "\nPDO::errorCode(): ";
            print $dq->errorCode();
            die;
        }
        
        $news = new news_node();
        $news->setPDO($pdo);
        $news->render(true);
    } catch  (PDOException $e) {
        die("pdo: ".$e->getMessage());
    }
	header('Location: index.php#n-'.$pdo->lastInsertId());
	die;

}

?>
<html>
<head>
<title>Insert News</title>
</head>
<body>
<div id="editor">
    <h1>Insert News</h1>
    <p><a href="index.php">back to home</a></p>
<?php
if ($password_fail) {
	echo "<div id=\"notice\">Can't login. Please check your password.</div>";
}
?>
    <div id="content">
        <form action="save.php" method="post">
            <input type="text" name="title" value="<?php echo $title ?>" size="80" /><br />
            <textarea name="text" id="text" cols="80" rows="23"><?php echo $text ?></textarea>
            <p><small>
            news documents are written using <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a>.
            </small></p>

            <p align="right">
                <strong>Password:</strong> <input type='password' name='password' size='20'/><br />
                <input type="submit" value="Share" />
            </p>
        </form>
    </div>
</div>
</body>
</html>