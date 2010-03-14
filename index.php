<?php

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

if (APPLICATION_ENVIRONMENT == 'production') {
    $firephp->setEnabled(false);
}

$page = (!empty($_GET['p']))?$_GET['p']:'index';
$firephp->log($page, 'pagename');

try {
    $pdo  = new PDO(DBH);
    $tpl  = new templates();
    $news = new news_node();
    $tpl->title = "empty";
    
    $tpl->addFile('_begin','header.tpl.php');
    $tpl->addFile('_end','footer.tpl.php');
    $tpl->addFile('index','index.tpl.php');
    
    switch($page)
    {
        default:
            if (file_exists('pages/' . $page . 'mdtxt') && is_readable('pages/' . $page . 'mdtxt')) {
                $pageContent = file_get_contents('pages/' . $page . 'mdtxt');
                file_put_contents(CACHE_PATH . "");
                $tpl->content = Markdown($pageContent);
            }
            break;
        case 'news':
            $news->setPDO($pdo);
            $news->setView(news_node::DEFAULT_VIEW);
            $news->add_filter('Markdown','text');

            // Comment this if you have some charset problems in cache
            $news->add_filter('utf8_decode','title');
            // -----

            $news->add_filter('htmlentities','title');
            $news->add_filter('set_bolder','author');
            
            $tpl->content = $news->render((isset($_GET['reload']) ? true : false), true, news_node::FORMAT_FULL);
            break;
    }
    
} catch (Exception $exception) {
    
    echo '<html><body><center>'
       . 'An exception occured while bootstrapping the application.';
    if (defined('APPLICATION_ENVIRONMENT')
        && APPLICATION_ENVIRONMENT != 'production'
    ) {
        echo '<br /><br />' . $exception->getMessage() . '<br />'
           . '<div align="left">Stack Trace:' 
           . '<pre>' . $exception->getTraceAsString() . '</pre></div>';
           if ($exception instanceof PDOException) {
                $trace = $exception->getTrace();
                echo '<div align="left">Query Trace:'
                . '<pre>' . $trace[0]['args'][0] . '</pre></div>';
        }
    }
    echo '</center></body></html>';
    exit(1);
}

echo $tpl->render('index');

?>