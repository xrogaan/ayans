<?php
/** vim: set ts=4 expandtab:
 * ErrorNot Notifier http://github.com/errornot/ErrorNot
 * Copyright (C) 2010  François de Metz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @see ErrorNotException
 */
require_once dirname(__FILE__).'/errornot_exception.php';

/**
 * @package Services_ErrorNot
 */
class Services_ErrorNot
{
    /**
     * singleton instance for Services_ErrorNot
     * 
     * @var Services_ErrorNot
     */
    protected static $instance = null;
    
    protected $url;

    protected $api_key;

    protected $version = '0.1.0';

    protected $adapter = null;

    /**
     * Dump session data when a exception is raised
     *
     * @var boolean
     */
    protected $dump_session_data = false;

    private $previous_exception_handler = null;

    private function __construct() {
    }

    /**
     * Create a Services_ErrorNot object and store it for singleton access
     *
     * @return Services_ErrorNot
     */
    public static function init()
    {
        return static::$instance = new static();
    }

    /**
     * Create singleton instance of Services_ErrorNot
     *
     * @param bool $auto_create True to create an instance if none exits
     * @return Services_ErrorNot
     */
    public static function getInstance($auto_create = false)
    {
        if ((bool) $auto_create && is_null(static::$instance)) 
        {
            static::init();
        }
        return static::$instance;
    }

    /**
     * Store the api key
     *
     * @param string $api
     * @return Services_ErrorNot
     */
    public function setApi($api)
    {
        if (!$this->api_key)
        {
            $this->api_key = $api;
        }
        return static::$instance;
    }

    /**
     * Store the url
     *
     * @param string $url
     * @return Services_ErrorNot
     */
    public function setUrl($url)
    {
        if (!$this->url)
        {
            $this->url = $url;
        }
        return static::$instance;
    }

    /**
     * Register Services_ErrorNot as exception handler
     *
     * @return void
     */
    public function registerExceptionHandler()
    {
        $this->installExceptionHandler();
        return static::$instance;
    }

    /**
     * Toggle Services_ErrorNot::dump_session_data
     */
    public function dumpSessionData()
    {
        $this->dump_session_data = !($this->dump_session_data);
    }

    /**
     * Set HTTP_Request2 Adapter
     * Useful for unit testing
     */
    public function setNetworkAdapter(HTTP_Request2_Adapter $http_request2_adapter)
    {
        $this->adapter = $http_request2_adapter;
    }

    /**
     * Notify Exception
     * @param Exception $exception
     */
    public function notifyException(Exception $exception)
    {
        $data = array();
        if ($exception instanceof ErrorNotException)
        {
            $data['exception_data'] = $exception->getData();
        }
        
        if ($this->dump_session_data && isset($_SESSION))
        {
            $data['SESSION'] = $_SESSION;
        }

        $this->notify($exception->getMessage(),
                      null, // auto now
                      $exception->getTrace(),
                      array('params' => array('post' => $_POST, 'get' => $_GET, 'cookies' => $_COOKIE)),
                      $_SERVER,
                      $data);
        if (!is_null($this->previous_exception_handler))
        {
            call_user_func($this->previous_exception_handler, $exception);
        }
    }

    /**
     * Notify a new error
     * @param String $message
     * @param Date $raised_at UTC date
     * @param array $backtrace
     * @param array $request
     * @param array $environnement
     * @param array $data
     * @return boolean
     */
    public function notify($message, $raised_at = null, $backtrace = array(), $request = null, $environnement = null, $data = null)
    {
        $http_request = new HTTP_Request2($this->formatUrl() , HTTP_Request2::METHOD_POST);
        if (!is_null($this->adapter))
        {
            $http_request->setAdapter($this->adapter);
        }
        if (is_null($raised_at))
        {
            $raised_at = date('c');
        }

        if (count($backtrace) > 1 && !isset($backtrace[0]['file'])) {
            array_shift($backtrace);
        }

        $http_request->addPostParameter('api_key', $this->api_key);
        $http_request->addPostParameter('version', $this->version);
        $http_request->addPostParameter('error', array('message'     => $message,
                                                       'raised_at'   => $raised_at,
                                                       'backtrace'   => $backtrace,
                                                       'request'     => $request,
                                                       'environment' => $environnement,
                                                       'data'        => $data));

        try
        {
            $response = $http_request->send();
            if ($response->getStatus() == 200)
            {
                return true;
            }
            return false;
        }
        catch (HTTP_Request2_Exception $e)
        {
            return false;
        }
    }

    /**
     * Install exception handler
     * Handler not caught exceptions
     * Preserve previous exception handler
     */
    public function installExceptionHandler()
    {
        $this->previous_exception_handler = set_exception_handler(array($this, 'notifyException'));
    }

    protected function formatUrl()
    {
        return $this->url . (($this->url[strlen($this->url) - 1] == '/') ? '' : '/') . 'errors/';
    }
}
