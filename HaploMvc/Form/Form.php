<?php
namespace HaploMvc\Form;

use HaploMvc\Template\Template;
use HaploMvc\Input\Input;
use HaploMvc\App;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class Form
 * @package HaploMvc\Form
 */
abstract class Form
{
    /** @var array */
    protected $errors = [];
    /** @var  App */
    protected $app;
    /** @var \HaploMvc\Security\Nonce */
    public $nonce;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return bool
     */
    public function validate() {
        if (!$this->app->nonce->check()) {
            $this->errors['nonce'] = 'CSRF token check failed.';
        }

        return empty($this->errors);
    }

    /**
     * @param array $filterTypes
     */
    public function assignFromPost(array $filterTypes = []) {
        foreach ($this->getPublicProperties() as $object) {
            $property = $object->name;
            if (array_key_exists($property, $filterTypes)) {
                $this->$property = Input::post($property, $this->$property, $filterTypes[$property]);
            } else {
                $this->$property = Input::post($property, $this->$property);
            }
        }
    }

    /**
     * @param Template $template
     * @param array $escapeTypes
     */
    public function assignToTemplate(Template $template, array $escapeTypes = []) {
        $this->nonce = $this->app->nonce->get();

        foreach ($this->getPublicProperties() as $object) {
            $property = $object->name;
            if (array_key_exists($property, $escapeTypes)) {
                $template->set($property, $this->$property, [
                    'escapeMethod' => $escapeTypes[$property]
                ]);
            } else {
                $template->set($property, $this->$property, [
                    'escapeMethod' => 'escapeAttr'
                ]);
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return \ReflectionProperty[]
     */
    protected function getPublicProperties()
    {
        static $properties = null;

        if (is_null($properties)) {
            $properties = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        }
        return $properties;
    }
}
