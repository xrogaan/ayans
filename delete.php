<?php

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

if (empty($_GET['id'])) {
	die('id is needed');
}

$id = intval($_GET['id']);

if (isset($_POST['password']) && PASSWORD != sha1($_POST['password'])) {
	header('Location: index.php');
} elseif (isset($_POST['password']) && PASSWORD == sha1($_POST['password'])) {
	$pdo = new PDO(DBH);

    $dq = $pdo->query('SELECT COUNT(*) FROM news WHERE id='.$pdo->quote($id),PDO::FETCH_UNIQUE);
    $re = $dq->fetch();

    if ($re == 1) {
        $pdo->prepare('DELETE FROM news WHERE id=?');
        $dq->execute(array(intval($_POST['id'])));
        unlink('cache/n-'.$id);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Delete a news</title>
</head>
<body>
<form action="delete.php" method="post">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<p>
<strong>Password:</strong> <input type='password' name='password' size='20'/><br />
<input type="submit" value="Delete" />
</p>
</form>
</body>
</html>