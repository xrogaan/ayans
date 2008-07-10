<?php
/**
* @category News
* @package News_Node
* @copyright Copyright (c) 2008, Bellière Ludovic
* @license http://opensource.org/licenses/mit-license.php MIT license
*/

class News_Node_Exception extends Exception {}

/**
* @author Bellière Ludovic
* @category News
* @package News_Node
* @copyright Copyright (c) 2008, Bellière Ludovic
* @license http://opensource.org/licenses/mit-license.php MIT license
*/
class news_node implements Countable {

    protected $_content = array();
    protected $_count = 0;
    private $_filters = array();
    private $_requiered_keynode;
    private $_PDO;
    private $_view;
    
    const DEFAULT_VIEW = 0xEFFFF69C;
    const SEARCH_VIEW = 0xE925030B;
    const ARCHIVE_VIEW = 0x9A2B1B01;

    const FORMAT_LIGHT = 'formatLight';
    const FORMAT_FULL  = 'formatFull';
    
    public $_default_view_options = array(
        'sql_limit' => '0, 10',
    );
    
    function __construct() {
        $this->_view = self::DEFAULT_VIEW;
    }
    
    public function setView($view) {
        $this->_view = $view;
    }

    public function add_nodes(array $content) {
        foreach ($content as $node) {
            if (!isset($this->_content['n-'.$node['id']])) {
                $this->_content['n-'.$node['id']] = $node;
                $this->_count++;
            }
        }
    }
    
    public function setPDO (PDO $pdo) {
        $this->_PDO = $pdo;
    }
    
    public function get ($key, $raw=false) {
        if (is_array($key)) {
            foreach ($key as $parent_key => $child_key) {
                if (array_key_exists($child_key,$this->_content[$parent_key])) {
                    return (!is_array($this->_content[$parent_key]) && $raw == true) ? self::escape($this->_content[$parent_key]) : $this->_content[$parent_key];
                }
            }
        }

        if (array_key_exists($key,$this->_content)) {
            return (!is_array($this->_content[$parent_key]) && $raw == true) ? self::escape($this->_content[$parent_key]) : $this->_content[$parent_key];
        }
    }

    /**
    * Add a filter function for the node $node
    *
    * @param string $function
    * @param string $node
    * @param integer $place
    * @return void
    * @throws News_Node_Exception for invalid type / not a function
    */
    public function add_filter($function,$node='all',$place=null) {
        if (is_callable($function,true)) {
            if (is_null($place)) {
                $this->_filters[$node][] = $function;
            } else {
                if (!is_int($place)) {
                    throw new News_Node_Exception('The param `place\' is not an integer');
                }

                if (isset($this->_filters[$place])) {
                    $for = (is_array($function)) ? $function[0].'->'.$function[1] : $function ;
                    trigger_error('Replacement of filter number '.$place.' for content <em>'.$for.'</em>',E_USER_NOTICE);
                }

                $this->_filters[$node][$place] = $function;
            }
        } else {
            throw new News_Node_Exception('The <em>function</em> parameter "'.$function.'" is not callable.');
        }
    }

    public function render($reload=false,$paginate=false) {
        try {
            self::getNews($reload,$paginate);
        } catch (News_Node_Exception $e) {
            die ($e->getMessage());
        }
        $txt = '';
        if ($reload) {
            $txt = self::reloadCache();
        } else {
            foreach ($this->_content as $node) {
                $txt.= $node['data'];
            }
        }
        return $txt;
    }

    protected function escape ($content,$tag) {
        foreach($this->_filters as $node => $filters) {
            if ($node == 'all' || $tag == $node) {
                foreach ($filters as $filter) {
                    $content = call_user_func($filter,$content);
                }
            }
        }
        return $content;
    }
    
    private function getNews ($reload=false,$paginate=false) {
        if (!$reload) {
            $cacheData = scandir(CACHE_PATH);
            // custom action for ARCHIVE_VIEW
            if ($this->_view == self::ARCHIVE_VIEW) {
                $elements = $paginate->elements;
                $limit = explode(', ',str_replace('LIMIT ','',$paginate->get_sql_limit_statement()));
            }
            // ---
            foreach ($cacheData as $file) {
                if ($file[0] == '.') {
                    continue;
                }
                $id = substr($file,2);
                // custom action for ARCHIVE_VIEW
                if ($this->_view == self::ARCHIVE_VIEW) {
                    if ($id-1 < intval($limit[0])) {
                        continue;
                    }
                    if ($id-1 > intval($limit[1])) {
                        break;
                    }
                }
                // ---
                if ($format == self::FORMAT_LIGHT) {
                    $file.= '.minimal';
                }
                $allnodes[] = array (
                    'id' => $id,
                    'type' => 'cache',
                    'data' => file_get_contents(CACHE_PATH.$file)
                );
            }
            if (!isset($allnodes)) {
                $this->_content[]['data'] = '<p>no news in database</p>';
            } else {
                $allnodes = array_reverse($allnodes);
                self::add_nodes($allnodes);
            }
        } else {
            switch ($this->_view) {
                case self::DEFAULT_VIEW:
                    $PDOStatement = $this->_PDO->query('SELECT * FROM news ORDER BY id DESC LIMIT '.$this->_default_view_options['sql_limit']);
                    break;
                case self::SEARCH_VIEW:
                    break;
                case self::ARCHIVE_VIEW:
                    if ($format == self::FORMAT_FULL) {
                        $PDOStatement = $this->_PDO->query('SELECT * FROM news ORDER BY id DESC LIMIT '.$this->_default_view_options['sql_limit']);
                    } else {
                        if ($paginate instanceof paginate) {
                            $PDOStatement = $this->_PDO->query('SELECT * FROM news ORDER BY id DESC '.$paginate->get_sql_limit_statement());
                        } else {
                            throw new News_Node_Exception('$paginate is not a instance of paginate object.');
                        }
                    }
                    break;
                default:
                    throw new News_Node_Exception('View \''.$this->_view.'\' not registered.');
            }
            self::add_nodes($PDOStatement->fetchAll());
        }
    }
    
    /**
     * Make all files in cache directory
     */
    private function reloadCache() {
        $txt='';
        foreach($this->_content as $key => $data) {
            $txt_tmp = '<h2 id="'.$key.'">'.self::escape($data['title'],'title').'</h2>'."\n";
            $txt_tmp.= '<p><sup><a href="#'.$key.'">#'.$key.'</a> On '.date('r',$data['postedon']).' by '.self::escape($data['author'],'author')."</sup></p>\n";
            $txt_tmp.= ''.self::escape($data['text'],'text')."\n";
            file_put_contents(CACHE_PATH.$key,$txt_tmp);
            $txt.=$txt_tmp;
            unset($txt_tmp);
        }
        return $txt;
    }

    ///////
    // overload
    ///////

    public function __get($key) {
        return $this->_content[$key];
    }

    protected function __isset($name) {
        return isset($this->_content[$name]);
    }

    public function count() {
        return $this->_count;
    }

}