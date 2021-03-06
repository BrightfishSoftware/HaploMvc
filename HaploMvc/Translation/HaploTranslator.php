<?php
namespace HaploMvc\Translation;

use HaploMvc\HaploApp;
use HaploMvc\Exception\HaploLangFileNotFoundException;
use HaploMvc\Exception\HaploTranslationKeyNotFoundException;

class HaploTranslator
{
    /**
     * Stores a reference to application object
     *
     * @var HaploApp
     **/
    protected $app;
    /**
     * Stores the selected language
     *
     * @var string
     **/
    protected $lang;
    /**
     * Stores the path to the translation files
     *
     * @var string
     **/
    protected $translationsDir;
    /**
     * Stores the path to the data representing processed translation files
     *
     * @var string
     **/
    protected $translationsCacheDir;
    /**
     * Stores whether or not a user can show translation keys by passing ?showKeys=true
     *
     * @var string
     **/
    protected $allowShowKeys;
    /**
     * Stores the path to the selected translation file
     *
     * @var string
     **/
    protected $file;
    /**
     * Stores the path to the cache file for the selected translation
     *
     * @var string
     **/
    protected $cacheFile;
    /**
     * A key/value pair containing translations found
     *
     * @var array
     **/
    protected $translations = array();

    /**
     * Constructor for class
     *
     * @param HaploApp $app
     * @throws HaploLangFileNotFoundException
     * @return HaploTranslator
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
        $this->lang = $this->app->config->getKey('translations', 'lang', 'en-US');
        $this->defaultLang = $this->app->config->getKey('translations', 'defaultLang', 'en-US');
        $this->allowShowKeys = $this->app->config->getKey('translations', 'allowShowKeys', false);
        $this->translationsDir = $this->app->appBase.'/Translations';
        $this->translationsCacheDir = $this->app->appBase.'/Cache';
        $this->file = $this->translationsDir.'/'.$this->lang.'.txt';
        
        if (!file_exists($this->file)) {
            $this->file = $this->translationsDir.'/'.$this->defaultLang.'.txt';
            
            if (file_exists($this->file)) {
                $this->lang = $this->defaultLang;
            } else {
                throw new HaploLangFileNotFoundException("Specified language file ($this->lang) and default ($this->defaultLang) do not exist.");
            }
        }
        
        $this->cacheFile = $this->translationsCacheDir.'/haplo-translations-'.md5($this->lang).'.cache';
        
        // after first pass translations are stored in serialised PHP array for speed
        // does a cache exist for the selected language
        if (file_exists($this->cacheFile)) {
            // grab current cache
            $cache = unserialize(file_get_contents($this->cacheFile));
            
            // if the recorded timestamp varies from the selected language file then the translations have changed
            // update the cache
            if (
                $cache['timestamp'] == filemtime($this->file) && 
                (!isset($cache['parent_filename']) || 
                $cache['parent_timestamp'] == filemtime($cache['parent_filename']))
            ) { // not changed
                $this->translations = $cache['translations'];
            } else { // changed
                $this->process();
            }
        } else {
            $this->process();
        }
    }
    
    /**
     * Processes selected translation file
     **/
    protected function process()
    {
        // create array for serialising
        $cache = array();
        
        // read translation file into array
        $file = file($this->file);
        
        // does this translation file inherit from another - only one level of inheritance 
        // supported at the moment
        $inheritsMatches = array();
        if (isset($file[0]) && preg_match("/^\s*{inherits\s+([^}]+)}.*$/", $file[0], $inheritsMatches)) {
            $parentFile = $this->translationsDir.trim($inheritsMatches[1]).'.txt';
            // read parent file into array
            $parentFile = file($parentFile);
            // merge lines from parent file into main file array, lines in the main file override lines in the parent
            $file = array_merge($parentFile, $file);
            // store filename of parent
            $cache['parent_filename'] = $parentFile;
            // store timestamp of parent
            $cache['parent_timestamp'] = filemtime($parentFile);
        }
        
        // read language array line by line
        foreach ($file as $line) {
            $translationMatches = array();
            
            // match valid translations, strip comments - both on their own lines and at the end of a translation
            // literal hashes (#) should be escaped with a backslash
            if (preg_match("/^\s*([0-9a-z\._-]+)\s*=\s*((\\\\#|[^#])*).*$/iu", $line, $translationMatches)) {
                $this->translations[$translationMatches[1]] = trim(str_replace('\#', '#', $translationMatches[2]));
            }
        }
        // add current timestamp of translation file
        $cache['timestamp'] = filemtime($this->file);
        // add translations
        $cache['translations'] = $this->translations;
        // write cache
        file_put_contents($this->cacheFile, serialize($cache));
    }

    /**
     * Get translation for specified key
     *
     * @param string $key Key to look up translation for
     * @throws HaploTranslationKeyNotFoundException
     * @return string
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->translations)) { // key / value pair exists
            $translation = $this->translations[$key];
            
            // number of arguments can be variable as user can pass any number of substitution values
            $numArgs = func_num_args();
            if ($numArgs > 1) { // complex translation, substitution values to process
                $firstArg = func_get_arg(1);
                if (is_array($firstArg)) { // named substitution variables
                    foreach ($firstArg as $key => $value) {
                        $translation = str_replace('{'.$key.'}', $value, $translation);
                    }
                } else { // numbered substitution variables
                    for ($i = 1; $i < $numArgs; $i++) {
                        $param = func_get_arg($i);
                        // replace current substitution marker with value
                        $translation = str_replace('{'.($i - 1).'}', $param, $translation);
                    } 
                } 
            }
        
            // whilst translating the user has the option to switch out all values with the corresponding key
            // this helps to see what translated text will appear where
            // set ALLOW_SHOW_KEYS false to disable - might be preferable in production
            if ($this->allowShowKeys && (isset($_GET['show_keys']) || isset($_POST['show_keys']))) {
                return $key;
            } else {
                return $translation;
            }
        }
        // key / value doesn't exist, throw exception
        throw new HaploTranslationKeyNotFoundException("Translation key ($key) does not exist in selected language file($this->file).");
    }
}
