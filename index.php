<?php
/**
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * Original author: Ludovic Bellière <guru at xrogaan be>
 */

require 'includes/config.inc.php';
require INCLUDES_PATH.'prepend.php';

$page = (!empty($_GET['p']))?$_GET['p']:'index';

try {
    $pdo  = new PDO(DBH);
    $tpl  = new templates();
    $news = new news_node();
    $tpl->title = "empty";
    
    $tpl->addFile('_begin','header.tpl.php');
    $tpl->addFile('_end','footer.tpl.php');
    
    $news->setPDO($pdo);
    $news->add_filter('Markdown',    'text');
    $news->add_filter('utf8_decode', 'title');
    $news->add_filter('htmlentities','title');
    $news->add_filter('set_bolder',  'author');
    
    switch($page)
    {
        default:
            if (file_exists('pages/' . $page . '.mdtxt') && is_readable('pages/' . $page . '.mdtxt'))
            {
                $pages = new Pages($page, 'pages/', array('filters'=>array('Markdown')));
                
                $layout = $pages->get_layout();
                if (!$tpl->templateExists($layout . '.tpl.php')) {
                    if ($layout != 'default' && $tpl->templateExists('default.tpl.php')) {
                        $layout = 'default';
                    } else {
                        throw new ErrorNotException("Can't load desired page.", 0, array('layout'=>$layout));
                    }
                }

                $tpl->addFile('page', $layout.'.tpl.php');
                $tpl->page    = $page;
                $tpl->content = $pages->get_content();
                $tpl->meta    = $pages->get_meta();
                $tpl->news    = $news->render(true, false, news_node::FORMAT_LIGHT);
                echo $tpl->render('page');
            } else {
                ob_start();
                require_once($tpl->getTemplatePath() . '404.tpl.php');
                echo ob_get_clean();
                die;
            }
            break;
        case 'news':
            $tpl->addFile('news','news.tpl.php');
            $news->setView(news_node::DEFAULT_VIEW);
            $tpl->content = $news->render((isset($_GET['reload']) ? true : false), true, news_node::FORMAT_FULL);
            echo $tpl->render('news');
            break;
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
               . '<pre>' . htmlentities($exception->getTraceAsString()) . '</pre></div>';
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

