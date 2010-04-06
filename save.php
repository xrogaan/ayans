<?php
/**
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Original author: Ludovic Bellière <guru at xrogaan be>
 */

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

try {
    $tpl = new templates();

    $tpl->action = 'save.php';

    $tpl->addFile('_begin','header.tpl.php');
    $tpl->addFile('_end','footer.tpl.php');
    $tpl->addFile('save','save.tpl.php');

    $tpl->password_fail = false;
    $tpl->input_title = (!empty($_POST['title'])) ? trim($_POST['title']) : 'Your title...';
    $tpl->input_text = (!empty($_POST['text'])) ? trim($_POST['text']) : 'Your news...';

    $tpl->title = "AYANS save news";

    if (isset($_POST['password']) && PASSWORD != sha1($_POST['password'])) {
	    $tpl->password_fail = true;
    } elseif (isset($_POST['password']) && PASSWORD == sha1($_POST['password'])) {
        try {
            $pdo = new PDO(DBH);

            $dq = $pdo->exec('INSERT INTO news (title, text, author, postedon) VALUES('.
                $pdo->quote($tpl->input_title).','.
                $pdo->quote($tpl->input_text).','.
                $pdo->quote('xrogaan').','.
                $pdo->quote(time()).')');

            if ($dq === false) {
                echo "\nPDO::error : ";
                $x = $pdo->errorInfo();
                echo 'errorCode: ',$x[0],'<br/>errorMessage: ',$x[2];
                die;
            }

            $news = new news_node();
            $news->add_filter('Markdown','text');
            $news->add_filter('htmlentities','title');
            $news->add_filter('set_bolder','author');
            $news->setPDO($pdo);
            $news->render(true);
        } catch  (PDOException $e) {
            die("pdo: ".$e->getMessage());
        }
	    header('Location: index.php#n-'.$pdo->lastInsertId());
	    die;

    }
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

echo $tpl->render('save');

