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
    
    $tpl->addFile('_begin','header.tpl.php');
    $tpl->addFile('_end','footer.tpl.php');
    
    $news->setPDO($pdo);
    $news->add_filter('trim',        'text');
    $news->add_filter('Markdown',    'text');
    $news->add_filter('utf8_decode', 'title');
    $news->add_filter('htmlentities','title');
    $news->add_filter('set_bolder',  'author');
    
    switch($page)
    {
        case 'contact':
            require INCLUDES_PATH . '/recaptchalib.php';
            $resp  = null;
            $tpl->error = null;
            if (isset($_POST['recaptcha_response_field'])) {
                $resp = recaptcha_check_answer(RECAPTCHA_PRIVATEKEY,
                                               $_SERVER['REMOTE_ADDR'],
                                               $_POST['recaptcha_challenge_field'],
                                               $_POST['recaptcha_response_field']);
                if ($resp->is_valid) {
                    // send mail
                    // name, email, comment
                    $name    = trim($_POST['name']);
                    $email   = trim($_POST['email']);
                    $comment = trim($_POST['comment']);
                    $message = "Name : $name\nEmail : $email\n\n----\n\nMessage : $comment";
                    $headers = 'From: '. EMAIL . "\r\n" .
                               'Reply-To: '. EMAIL . "\r\n" .
                               'X-Mailer: PHP/' . phpversion();
                    $tpl->mailer = mail(EMAIL, '[poyoninfo.be] New message from '.$name, $message, $headers);
                } else {
                    $tpl->error = $resp->error;
                }
            }
        default:
            $pages = new Pages($page, 'pages/', array('filters'=>array('Markdown')));

            $layout = $pages->get_layout();
            if (!templates::templateExists($layout . '.tpl.php')) {
                if ($layout != 'default' && templates::templateExists('default.tpl.php')) {
                    $layout = 'default';
                } else {
                    throw new ErrorNotException("Can't load desired page ($layout).", 0, array('layout'=>$layout));
                }
            }

            $tpl->addFile('page', $layout.'.tpl.php');
            $tpl->assign($pages->get_meta());
            $tpl->assign('content', $pages->get_content());
            $tpl->assign('news', $news->render(false, false, news_node::FORMAT_LIGHT));
            $tpl->page    = $page;
            echo $tpl->render('page');
            break;
        case 'news':
            $tpl->addFile('news','news.tpl.php');
            $news->setView(news_node::DEFAULT_VIEW);
            $tpl->node    = "Nouvelles";
            $tpl->title   = "Nouvelles";
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

