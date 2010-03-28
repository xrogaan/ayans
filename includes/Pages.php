<?php
/**
 * @category Pages
 * @package Pages
 * @copyright Copyright (c) 2010, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

class Pages {

    protected $_meta = array();
    
    private $_filename;
    private $_pagename;
    private $_directory;
    private $_options = array(
        'cache' => TMP,
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
        $this->_filename = $pagename . '.mdtext';
        
        if (!file_exists($this->_directory . $this->_filename)) {
            throw new exception("Specified file doesn't exists.");
        } elseif (!is_readable($this->_directory . $this->_filename)) {
            throw new exception("Specified file can't be read.");
        }

        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
        
        self::process();
        return $this;
    }
    
    protected function process() {
        $handle = fopen($this->_directory . $this->_filename, 'r');
        
        $line   = 0;
        $onMeta = false;
        $content = $meta = '';
        while (!feof($handle)) {
            $line++;
            $buffer = fgets($handle, 4096);
            if (trim($buffer) == '---' && !is_null($onMeta)) {
                if ($onMeta === false) {
                    if ($line != 0) {
                        throw new LogicException("Meta information must be on the top of the file.");
                    }
                    $onMeta = true;
                } else {
                    $onMeta = null;
                }
            } else {
                if ($onMeta) {
                    $meta.= $buffer;
                } else {
                    $content.= $buffer;
                }
            }
        }
        fclose($handle);
        
        self::parseMeta($meta);
    }
    
    protected function parseMeta($meta) {
        $this->_meta = parse_ini_string($meta);

        if (!in_array('title',$this->_meta)) {
            throw new Exception('Unknow title for current page.');
        }

        return ($this->_meta != false) ? true : false;
    }
    
    public function cachedFileExists($filename=false, $directory=false) {
        if (!$filename) {
            $filename = $this->_directory . $this->_filename;
        }
        $this->_currentSha1 = sha1_file($file);
        self::populateCachedFiles();
        return in_array($this->_options['cache'] . $this->_pagename . '.' . $this->_currentSha1 . '.mdcache', $files);
    }

    /**
     * Return the current cached files
     * @return string
     */
    public function getCachedFilename() {
        return $this->_options['cache'] . $this->_pagename . '.' . $this->_currentSha1 . '.mdcache';
    }

    /**
     * Populate the self::_cachedFiles variable.
     *
     * @return void
     */
    protected function populateCachedFiles() {
        if (empty($this->_cachedFiles)) {
            $this->_cachedFiles = glob($this->_options['cache'] . $this->_pagename . '*.mdcache');
        }
    }

    /**
     * Do a garbage collect on older cached file relates to pagename.
     *
     * There is usually 2 files in the array.
     */
    protected function cacheGarbageCollect() {
        self::populateCachedFiles();
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

}

