<?php

class templates_exception extends Exception {}

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
    protected $_templatePath;

    private $_escape = array('htmlentities');

    public function __construct($template_path='./templates/') {
        $this->_templatePath = $template_path;
    }

    public function getTemplatePath() {
        return $this->_templatePath;
    }

    public function addFile($tag,$name) {
        $this->_files[$tag] = $name;
    }

    public function render($tag) {
        try {
            if (!is_array($tag)) {
                ob_start();
                if (isset($this->_files['_begin'])) {
                    include $this->_file('_begin');
                }
                include $this->_file($tag);
                if (isset($this->_files['_end'])) {
                    include $this->_file('_end');
                }
                //return ob_end_clean();
            } else {
                ob_start();
                if (isset($this->_files['_begin'])) {
                    include $this->_file('_begin');
                }
                $tags = $tag;
                unset($tag);
                foreach ($tags as $tag) {
                    include $this->_file($tag);
                }
                if (isset($this->_files['_end'])) {
                    include $this->_file('_end');
                }
           }
           ob_end_flush();
        } catch (templates_exception $e) {
            die ($e->getMessage());
        } catch (Exception $e) { die('ex error: '.$e->getMessage()); }
    }

    private function _file($tag) {
        if (is_readable($this->_templatePath.$this->_files[$tag])) {
            return $this->_templatePath.$this->_files[$tag];
        } else {
            throw new templates_exception('The file <em>'.$this->_templatePath.$this->_files[$tag]. '</em> is not readable');
        }
    }

    public function setEscape($ref) {
        if (!in_array($ref,$this->_escape)) {
            $this->_escape[] = $ref;
        }
    }
    
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

    public function __isset($key) {
        $strpos = strpos($key,'_');
        if (!is_bool($strpos) && $strpos !== 0) {
            return isset($this->$key);
        }
        return false;
    }
}
