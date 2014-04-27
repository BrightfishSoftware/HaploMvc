<?php
namespace HaploMvc\Template;

use HaploMvc\HaploApp;
use HaploMvc\Security\HaploEscaper;
use HaploMvc\Exception\HaploInvalidTemplateException;
use HaploMvc\Exception\HaploTemplateFunctionNotFound;
use HaploMvc\Exception\HaploPostFilterFunctionNotFoundException;
use HaploMvc\Exception\HaploTemplateNotFoundException;

/**
 * Class HaploTemplate
 * @package HaploMvc
 */
class HaploTemplate
{
    /** @var HaploApp */
    protected $app;
    /**
     * Stores filename of template to render
     *
     * @var string
     **/
    protected $filename;
    /**
     * Stores file path to look for template in
     *
     * @var string
     **/
    protected $filePath;
    /**
     * @var array
     */
    protected $templateFunctions = array();
    /**
     * Stores reference to post filter functions to run against template
     * @var array
     */
    protected $postFilters = array();
    /**
     * @var array
     */
    protected $inherits = array();
    /**
     * Stores variables to pass to template
     *
     * @var array
     **/
    public $vars = array();
    /**
     * @var array
     **/
    public $regionNames = array();
    /**
     * @var array
     */
    public $regions = array();

    /**
     * Constructor for class
     *
     * @param HaploApp $app
     * @param string $filename Filename of template to render
     * @throws HaploInvalidTemplateException
     */
    public function __construct(HaploApp $app, $filename)
    {
        if (!preg_match('/^[a-z0-9\/_-]+\.(php|html|tpl)$/i', $filename)) {
            throw new HaploInvalidTemplateException("Invalid template filename specified ($filename). Characters allowed in the filename are a-z, 0-9, _ and -. The filename must also end in .php, .html or .tpl");
        }

        $this->app = $app;
        $this->filePath = $this->app->appBase;
        $this->filename = $filename;
    }

    /**
     * Include another template inside the main template (called within the template file).
     * Included template inherits parent templates variables and can optionally set its own
     * which live within the scope of that included template only.
     *
     * @param string $filename Filename for template to include - uses the same file paths as the parent
     * @param array $vars Optionally pass additional variables to the template
     **/
    protected function incTemplate($filename, array $vars = array())
    {
        $template = new HaploTemplate($this->app, $filename);
        $template->vars = $this->vars;
        
        if (count($vars)) {
            foreach ($vars as $key => $value) {
                $template->set($key, $value);
            }
        }

        echo $template->render();
    }

    /**
     * @param string $filename
     */
    public function inherits($filename)
    {
        $this->inherits[] = $filename;
    }

    /**
     * @param string $name
     * @param string $mode
     */
    public function region($name, $mode = 'replace')
    {
        $this->regionNames[] = array($name, $mode);
        ob_start();
    }

    public function endRegion()
    {
        list($name, $mode) = array_pop($this->regionNames);

        if (!isset($this->regions[$name])) {
            $this->regions[$name] = array('content' => ob_get_contents(), 'mode' => $mode);
        } else {
            switch ($this->regions[$name]['mode']) {
                case 'prepend':
                case 'before':
                    $this->regions[$name] = array(
                        'content' => $this->regions[$name]['content'].ob_get_contents(),
                        'mode' => $mode
                    );
                    break;
                case 'append':
                case 'after':
                    $this->regions[$name] = array(
                        'content' => ob_get_contents().$this->regions[$name]['content'],
                        'mode' => $mode
                    );
                    break;
            }
        }

        ob_end_clean();

        if ($mode === 'replace') {
            echo $this->regions[$name]['content'];
        }
    }

    /**
     * Set a variable (make it available within the scope of the template)
     *
     * @param string $name Name of variable to set
     * @param mixed $value Value to give to variable
     * @param array $options
     */
    public function set($name, $value, array $options = array())
    {
        $defaultOptions = array(
            'stripHtml' => $this->app->config->getKey('templates', 'stripHtml', true),
            'escape' => $this->app->config->getKey('templates', 'escape', true),
            'escapeMethod' => $this->app->config->getKey('templates', 'escapeMethod', 'escapeHtml'),
            'convertEntities' => $this->app->config->getKey('templates', 'convertEntities', true),
            'encoding' => $this->app->config->getKey('templates', 'encoding', 'UTF-8')
        );
        $options = array_merge($defaultOptions, $options);
        HaploEscaper::setEncoding($options['encoding']);

        // is variable a scalar
        if (is_scalar($value)) {
            if ($options['stripHtml']) {
                $value = strip_tags($value);
            }

            if ($options['escape']) {
                $method = $options['escapeMethod'];
                $value = HaploEscaper::$method($value);
            }
        }
        
        // is variable an array
        if (is_array($value)) {
            array_walk_recursive($value, function(&$value, $key, $options) {
                if (!($value instanceof HaploTemplate) && is_scalar($value)) {
                    if ($options['stripHtml']) {
                        $value = strip_tags($value);
                    }

                    if ($options['convertEntities']) {
                        $method = $options['escapeMethod'];
                        $value = HaploEscaper::$method($value);
                    }
                }
            }, $options);
        }
        
        $this->vars[$name] = $value;
    }

    /**
     * @param string $name
     * @param callable|string $value
     * @throws HaploTemplateFunctionNotFound
     */
    public function addFunction($name, $value)
    {
        if (is_callable($value)) {
            $this->templateFunctions[$name] = $value;
        } else {
            $file = $this->filePath.'/TemplateFunctions/'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name))).'.php';
            if (file_exists($file)) {
                require_once $file;

                $this->templateFunctions[$name] = $value;
            } else {
                throw new HaploTemplateFunctionNotFound(sprintf('Template function (%s) could not be found in (%s)', $name, $file));
            }
        }
    }

    /**
     * Add a post filter - a function which is run against the generated template before outputting
     *
     * @param callable|string $postFilter
     * @throws HaploPostFilterFunctionNotFoundException
     */
    public function addPostFilter($postFilter)
    {
        if (is_callable($postFilter)) {
            $this->postFilters[] = $postFilter;
        } else {
            $file = $this->filePath.'/PostFilters/'.str_replace(' ', '', ucwords(str_replace('_', ' ', $postFilter))).'.php';
            if (file_exists($file)) {
                require_once $file;

                $this->postFilters[] = $postFilter;
            } else {
                throw new HaploPostFilterFunctionNotFoundException(sprintf('Post filter (%s) could not be found in (%s)', $postFilter, $file));
            }
        }
    }

    /**
     * Render template
     *
     * @throws HaploTemplateNotFoundException
     * @return string
     */
    public function render()
    {
        $output = '';

        // looping rather than using extract because we need to determine the value type before assigning
        foreach ($this->vars as $key => &$value) {
            // is this variable a reference to a sub-template
            if ($value instanceof HaploTemplate) {
                // pass variables from parent to sub-template but don't override variables in sub-template 
                // if they already exist as they are more specific
                foreach ($this->vars as $subKey => $subValue) {
                    if (!($subValue instanceof HaploTemplate) && !array_key_exists($subKey, $value->vars)) {
                        $value->vars[$subKey] = $subValue;
                    }
                }
                // display sub-template and assign output to parent variable
                $$key = $value->render();
            } else {
                $$key = $value;
            }
        }

        // use output buffers to capture data from require statement and store in variable
        ob_start();

        $path = $this->filePath.'/Templates/'.$this->filename;
        if (file_exists($path)) {
            require $path;
        } else {
            throw new HaploTemplateNotFoundException("Template ($this->filename) doesn't exist.");
        }
        
        $output .= ob_get_clean();

        while ($inherit = array_pop($this->inherits)) {
            $parent = new HaploTemplate($this->app, $inherit);
            $parent->vars = $this->vars;
            $parent->regionNames = $this->regionNames;
            $parent->regions = $this->regions;
            $output = $parent->render();
        }

        // process content against defined post filters
        foreach ($this->postFilters as $postFilter) {
            $output = $postFilter($output);
        }
        return $output;
    }

    /**
     * Output rendered template
     *
     * @return void
     * @author Ed Eliot
     **/
    public function display()
    {
        echo $this->render();
    }
}
