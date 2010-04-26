<?php
/**
* @category News
* @package News_Templates
* @copyright Copyright (c) 2008, Bellière Ludovic
* @license http://opensource.org/licenses/mit-license.php MIT license
*/

class templates_exception extends Exception {}

/**
* @author Bellière Ludovic
* @category News
* @package News_Templates
* @copyright Copyright (c) 2008, Bellière Ludovic
* @license http://opensource.org/licenses/mit-license.php MIT license
*/
class templates {
    /**
     * File list
     *
     * @var array
     */
    protected $_files = array();

    /**
     * Path to the templates files
     *
     * @var string
     */
    static protected $_templatePath = './templates/';
    
    protected $_options = array();

    private $_escape = array('htmlentities');

    public function __construct($template_path='',$options=array()) {
        if (!$template_path) {
            static::$_templatePath = $template_path;
        }
        $this->_options = array_merge($this->_options,$options);
    }
    
    /**
     * Return an option
     *
     * @param string $key search for $key in $_options
     * @param string|null $default default value returned instead of empty data
     * @return string|array
     */
    function getOptions($key=null,$default=null) {
        if (is_null($key)) {
            return $this->_options;
        } elseif (isset($this->_options[$key])) {
            return $this->_options[$key];
        } else {
            return $default;
        }
    }

    /**
     * Return the current tempalte path
     *
     * @return string
     */
    static public function getTemplatePath() {
        return static::$_templatePath;
    }

    /**
     * Add a template file
     *
     * @param string $tag Template id
     * @param string $name Template filename.
     * @return void
     */
    public function addFile($tag,$name) {
        $this->_files[$tag] = $name;
    }

    /**
     * Process the templates files by $tag
     * $tag can be an array for multi-templates page.
     *
     * @param array|string $tag
     * @return string
     */
    public function render($tag) {
        ob_start();
        try {
            if (isset($this->_files['_begin'])) {
                include_once $this->_file('_begin');
            }
            if (!is_array($tag)) {
                include_once $this->_file($tag);
            } else {
                $tags = $tag;
                unset($tag);
                foreach ($tags as $tag) {
                    include_once $this->_file($tag);
                }
           }
            if (isset($this->_files['_end'])) {
                include_once $this->_file('_end');
            }
        } catch (templates_exception $e) {
            ob_end_clean();
            die ($e->getMessage());
        } catch (Exception $e) {
            ob_end_clean();
            die('ex error: '.$e->getMessage());
        }
        $out = ob_get_clean();
        return $out;
    }

    /**
     * Check if the template file is readable and returns its name
     *
     * @param string $tag
     * @return string
     * @throw template_exception on missing file
     */
    private function _file($tag) {
        if (is_readable(static::$_templatePath.$this->_files[$tag])) {
            return static::$_templatePath.$this->_files[$tag];
        } else {
            throw new templates_exception('The file <em>'.$this->_templatePath.$this->_files[$tag]. '</em> is not readable');
        }
    }

    /**
     * Return true if the given template file exists.
     *
     * @param string $file
     * @return boolean
     */
    static public function templateExists($file) {
        if (!file_exists(static::$_templatePath . $file)) {
            return false;
        }
        return true;
    }

    /**
     * Used to set some functions who escape the content
     *
     * @param function $ref
     */
    public function setEscape($ref) {
        if (!in_array($ref,$this->_escape)) {
            $this->_escape[] = $ref;
        }
    }

    /**
     * Used to remove a function from the pool of escape
     *
     * @param function $ref
     * @param boolean $id
     * @return boolean
     */
    public function remEscape($ref,$id=false) {
        if ($id && isset($this->_escape[$id])) {
            unset($this->_escape[$id]);
            return true;
        } elseif (!$id) {
            foreach ($this->_escape as $key => $val) {
                if ($val == $ref) {
                    unset($this->_escape[$key]);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Force string $var escaping.
     */
    public function escape($var) {
        foreach($this->_escape as $fnct) {
            if (in_array($fnct, array('htmlentities','htmlspecialchars'))) {
                $var = call_user_func($fnct, $var, ENT_COMPAT, 'utf8');
            } else {
                $var = call_user_func($fnc, $var);
            }
        }
        return $var;
    }
    
    public function assign($name,$data=null) {
        try {
            if (is_string($name)) {
                self::__set($name, $data);
            } elseif (is_array($name)) {
                foreach ($name as $key => $value) {
                    self::__set($key,$value);
                }
            } else {
                throw new templates_exception('Argument 2 passed to ' . __CLASS__ . '::' . __FUNCTION__ . ' must be a string or an array, ' . gettype($arguments) . ' given.');
            }
        } catch (templates_exception $e) {
            throw $e;
        }
        return $this;
    }
    
    /**
     * Remove all variable assigned via __set()
     * @return void
     */
    public function clearVars () {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (substr($key, 0, 1) != '_' && !in_array($key,$this->_persistentVars)) {
                unset($this->$key);
            }
        }
    }

    public function setGlobal($name,$data) {
        self::__set($name,$data);
        $this->_persistentVars[] = $name;
    }

    public function __set($name,$data) {
        if ('_' != substr($name, 0, 1)) {
            $this->$name = $data;
            return;
        }

        throw new templates_exception('Setting private or protected class members is not allowed.',$this);
    }

    public function __unset($name) {
        if ('_' != substr($name, 0, 1) && isset($this->$name)) {
            unset($this->$name);
        }
    }
    
    public function __isset($key) {
        $strpos = strpos($key,'_');
        if (!is_bool($strpos) && $strpos !== 0) {
            return isset($this->$key);
        }
        return false;
    }
}
