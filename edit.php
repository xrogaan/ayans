<?php
/**
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Original author: Ludovic Bellière <guru at xrogaan be>
 */

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

if (isset($_GET['id'])) {
    $idNews = intval($_GET['id']) ;
} else {
    die('News id needed. Please use the back button.');
}

try {
    $tpl = new templates();
    
    $tpl->action = 'edit.php?id='.$idNews;
    
    $pdo = new PDO(DBH);
    $q = $pdo->query('SELECT * FROM news WHERE id='.$pdo->quote($idNews));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        die('Nothing in database. Please use the back button.');
    }
    
    $tpl->password_fail = false;
    $tpl->updated = false;
    if (isset($_POST['password']) && PASSWORD != sha1($_POST['password'])) {
        $tpl->password_fail = true;
    } elseif (isset($_POST['password']) && PASSWORD == sha1($_POST['password'])) {
        $tpl->input_title = (!empty($_POST['title'])) ? trim($_POST['title']) : $data['title'];
        $tpl->input_text = (!empty($_POST['text'])) ? trim($_POST['text']) : $data['text'];
        $uq = $pdo->exec('UPDATE news SET text='. $pdo->quote($tpl->input_title) .', title='. $pdo->quote($tpl->input_text) .', editon='.$pdo->quote(time()).' WHERE id='.$pdo->quote($idNews));
        if ($uq === false) {
            echo "\nError on UPDATE : <br/>";
            $x = $pdo->errorInfo();
            echo 'errorCode: ',$x[0],'<br/>errorMessage: ',$x[2];
            die;
        } else {
            $tpl->updated = true;
        }
    }
    
    $tpl->addFile('_begin','header.tpl.php');
    $tpl->addFile('_end','footer.tpl.php');
    $tpl->addFile('save','save.tpl.php');
    
    $tpl->input_title = $data['title'];
    $tpl->input_text = $data['text'];
} catch (Exception $exception) {
    echo '<html><body><center>'
       . 'An exception occured while bootstrapping the application.';
    if (defined('APPLICATION_ENVIRONMENT')
        && APPLICATION_ENVIRONMENT != 'production'
    ) {
        do {
            $errornot = Services_ErrorNot::getInstance();
            $errornot->notifyException($exception);
            echo '<br /><br />' . $exception->getMessage() . '<br />'
               . '<div align="left">Stack Trace:'
               . '<pre>' . $exception->getTraceAsString() . '</pre></div>';
               if ($exception instanceof PDOException) {
                    $trace = $exception->getTrace();
                    echo '<div align="left">Query Trace:'
                    . '<pre>' . $trace[0]['args'][0] . '</pre></div>';
            }
        } while($exception = $exception->getPrevious());
    }
    echo '</center></body></html>';
    exit(1);
}

define('ON_EDIT', true);

$tpl->title = "AYANS edit news";
echo $tpl->render('save');
