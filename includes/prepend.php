<?php

if (APPLICATION_ENVIRONMENT != 'production') {
	error_reporting(E_ALL);
}

require_once('HTTP/Request2.php');
require_once(INCLUDES_PATH.'errornot.php');
$errornot = Services_ErrorNot::getInstance(true)
        ->setApi('cf12b1dfeb03cef327f92a1e')
        ->setUrl('http://127.0.0.1:3000')
        ->registerExceptionHandler();

/*
function error_handler($errno, $errstr, $errfile, $errline, $errcontext ) {
    global $old;
    $er = Services_ErrorNot::getInstance();

    $send = $er->notify($errstr,
            NULL,
            array("[internal]$errfile:$errline ..."),
            array('params' => array('post' => $_POST, 'get' => $_GET, 'cookies' => $_COOKIE)),
            $_SERVER,
            $errcontext);

    if (!$send) {
        echo "Can't send to ".$er->getUrl();
    }
}
$old = set_error_handler("error_handler");
*/
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

require INCLUDES_PATH.'markdown.php';
require INCLUDES_PATH.'templates.php';
require INCLUDES_PATH.'filters.php';
require INCLUDES_PATH.'news-node.php';
require INCLUDES_PATH.'Pages.php';

/**
 * Sans uniqid, la fonction génère un nouveau set d'équation/réponse,
 * l'insère dans la base de donnée et retourne un identifiant unique
 * et l'équation.
 * Si uniqid est fournis (!false), alors la fonction le recherche dans
 * la base de donnée. Si il existe, compare la réponse avec celle
 * envoyée par l'utilisateur et retourne un boolean (true si succès).
 *
 * A chaque appel, la fonction fait un garbage collect, supprimant
 * toute entrée vieille de plus de 2 heures.
 * 
 * @param string $uniqid
 * @return array|boolean
 */
function captcha($uniqid=false) {
    $pdo  = new PDO(DBH);
    
    // garbage collect
    $pdo->query("DELETE FROM captcha WHERE ".time()-7200." >= createdon  ");
    
    if (!$uniqid) {
        $min = 1;
        $max = 100;
        $operators = array('+','-');

        $uniqid = uniqid();
        $number = array();
        $offset = $max * 0.9;
        $number[0] = rand(1, $offset);
        $number[1] = rand($number[0]+1, $max);
        $eq = implode (' ' . $operators[rand(0,count($operators)-1)] . ' ', $number);
        $result = eval('return '. $eq .';');

        $pdo->query('INSERT INTO captcha ("id", "equation", "result", "createdon")
        VALUES (
            "' . $uniqid . '",
            "' . $eq . '",
            ' . $result . ',
            '.time().'
        )');
        return array('uid' => $uniqid, 'equation' => $eq);
    } else {
        $q = $pdo->query('SELECT * FROM captcha WHERE id=' . $pdo->quote($uniqid,PDO::PARAM_STR));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        if (!empty($data) && isset($_POST['answer'])) {
            $answer = (int) trim($_POST['answer']);
            if ($answer == (int) $data['result']) {
                return true;
            }
        }
        return false;
    }
};
