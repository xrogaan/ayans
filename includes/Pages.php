<?php
/**
 * @category Pages
 * @package Pages
 * @copyright Copyright (c) 2010, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

class Pages {

    protected $_meta = array();
    protected $_filters = array();
    
    private $_filename;
    private $_pagename;
    private $_directory;
    private $_options = array(
        'cache' => PAGES_CACHE_PATH,
    );

    /**
     * Cached files relates to the current pagename
     * @var array
     */
    private $_cachedFiles = array();

    /**
     * Current sha1 of the file.
     * @var string
     */
    private $_currentSha1;

    /**
     * Current content of the file
     * @var string
     */
    private $_fileContent;

    /**
     * Check if the page exists and call self::process().
     * If the file does exists but is not readable, throw an exception.
     *
     * @param string $pagename
     * @param string $directory
     * @param array $options
     * @throw Exception if the page doesn't exists or isn't readable.
     * @return Pages
     */
    public function __construct($pagename, $directory='', array $options=array()) {
        if (!empty($directory)) {
            if (substr($directory, -1) != '/') {
                $directory.= '/';
            }
            $this->_directory = $directory;
        }
        $this->_pagename = $pagename;
        $this->_filename = $pagename . '.mdtxt';
        
        if (!file_exists($this->_directory . $this->_filename) OR !is_readable($this->_directory . $this->_filename)) {
            ob_start();
            templates::getTemplatePath() . '404.tpl.php';
            echo ob_get_clean();
            die;
        }

        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }

        self::parse_options();
        self::process();
        return $this;
    }

    protected function parse_options() {
        if (array_key_exists('filters', $this->_options)) {
            if (!is_array($this->_options['filters'])) {
                throw new Exception('Filters must be an array. "'.gettype($this->_options['filters']).'" given.');
            }
            foreach($this->_options['filters'] as $id => $filter) {
                self::add_filter($filter,$id);
            }
            unset($this->_options['filters']);
        }
    }
    
    protected function process() {
        $this->_currentSha1 = sha1_file($this->_directory . $this->_filename);
        $handle = fopen($this->_directory . $this->_filename, 'r');
        
        $line   = 0;
        $onMeta = false;
        $content = $meta = '';
        while (!feof($handle)) {
            $line++;
            $buffer = fgets($handle, 4096);
            if (trim($buffer) == '---' && !is_null($onMeta)) {
                if ($onMeta === false) {
                    if ($line >= 2) {
                        throw new LogicException("Meta information must be on the top of the file (line: $line).");
                    }
                    $onMeta = true;
                } else {
                    $onMeta = null;
                }
            } else {
                if ($onMeta) {
                    $meta.= trim($buffer)."\n";
                } else {
                    $content.= $buffer;
                }
            }
        }
        fclose($handle);
        $this->_fileContent = $content;
        self::parse_meta($meta);
        self::apply_filters();
        self::fills_cache();

    }

    /**
     * Parse the meta string from the page. It must be in the ini format
     *
     * @param string $meta
     * @return boolean
     */
    protected function parse_meta($meta) {
        $this->_meta = parse_ini_string($meta);

        if (!array_key_exists('title',$this->_meta)) {
            $this->_meta['title'] = $this->_pagename;
        }

        return ($this->_meta != false) ? true : false;
    }

    protected function fills_cache() {
        if (!self::cached_file_exists()) {
            file_put_contents(self::get_cached_filename(), $this->_fileContent, LOCK_EX);
        }
        self::cache_garbage_collect();

        return true;
    }
    
    public function cached_file_exists($directory=false) {
        if ($directory) {
            if (file_exists($this->_options['cache'] . $directory)) {
                $filename = $this->_options['cache'] . $directory . self::get_cached_filename(false);
            } elseif (file_exists($directory)) {
                $filename = $directory . self::get_cached_filename(false);
            } else {
                // using default cache setting
                $directory = false;
            }
        }
        if (!$directory) {
            $filename = self::get_cached_filename();
        }

        self::list_cached_files();
        return in_array($filename, $this->_cachedFiles);
    }

    /**
     * Return the current cached files
     * @return string
     */
    public function get_cached_filename($appendCacheDir=true) {
        $appendCacheDir = (bool) $appendCacheDir;
        $file           = '';
        
        if ($appendCacheDir) {
            $file.= $this->_options['cache'];
        }
        $file.= $this->_pagename . '.' . $this->_currentSha1 . '.mdcache';

        return $file;
    }

    /**
     * Populate the self::_cachedFiles variable.
     *
     * @return void
     */
    protected function list_cached_files() {
        if (empty($this->_cachedFiles)) {
            $this->_cachedFiles = glob($this->_options['cache'] . $this->_pagename . '*.mdcache');
        }
    }

    /**
     * Do a garbage collect on older cached file relates to pagename.
     *
     * There is usually 2 files in the array.
     */
    protected function cache_garbage_collect() {
        self::list_cached_files();
        if (count($this->_cachedFiles) > 1)
        {
            foreach ($this->_cachedFiles as $filename)
            {
                if (!strpos($filename, $this->_currentSha1))
                {
                    unlink($filename);
                }
            }
        }
    }

    /**
     * Add a filter on the page content.
     *
     * A filter must be a function with a single argument.
     *
     * @param mixed $funcName
     * @param int $id
     * @return Pages
     */
    public function add_filter($funcName,$id=false) {
        if (!$id) {
            $this->_filters[] = $funcName;
        } else {
            $this->_filters[$id] = $funcName;
        }
        ksort($this->_filters);
        return $this;
    }

    protected function apply_filters() {
        if (!empty($this->_filters) && !empty($this->_fileContent)) {
            foreach($this->_filters as $filter) {
                $this->_fileContent = call_user_func($filter, $this->_fileContent);
            }
        }
    }

    public function get_content() {
        return $this->_fileContent;
    }

    public function get_meta() {
        return $this->_meta;
    }

    public function get_layout() {
        if (array_key_exists('layout', $this->_meta)) {
            return $this->_meta['layout'];
        } else {
            return 'default';
        }
    }
}

