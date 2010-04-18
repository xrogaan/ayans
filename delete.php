<?php
/**
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Original author: Ludovic Bellière <guru at xrogaan be>
 */

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    die('id is needed');
}
$text = '';
if (isset($_POST['password']) && PASSWORD != sha1($_POST['password'])) {
	header('Location: index.php');
} elseif (isset($_POST['password']) && PASSWORD == sha1($_POST['password'])) {
	$pdo = new PDO(DBH);

    $dq = $pdo->query('SELECT COUNT(*) FROM news WHERE id='.$pdo->quote($id) ,PDO::FETCH_UNIQUE);
    $re = $dq->fetch();
    $re = array_shift($re);
    if ($re == 1) {
        $q = $pdo->query('DELETE FROM news WHERE id='. $pdo->quote($id, PDO::PARAM_INT));
        if (!$q) {
            $text = "The news is not deleted : " . implode(',', array($pdo->errorInfo()));
        } else {
            $text = "The news $id is successfully deleted.<br/><a href=\"index\">Back</a>";
            unlink('cache/n-'.$id);
            unlink('cache/n-'.$id.'.minimal');
        }
    } else {
        die('error');
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Delete a news</title>
</head>
<body>
<p>
<?php echo $text ?>
</p>
<form action="delete.php" method="post">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<p>
<strong>Password:</strong> <input type='password' name='password' size='20'/><br />
<input type="submit" value="Delete" />
</p>
</form>
</body>
</html>