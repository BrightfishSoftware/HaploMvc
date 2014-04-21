<?php
namespace HaploMvc\Form;

use HaploMvc\Template\HaploTemplate;
use HaploMvc\Input\HaploInput;
use HaploMvc\HaploApp;
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
    /** @var  HaploApp */
    protected $app;
    /** @var \HaploMvc\Security\HaploNonce */
    public $nonce;

    public function __construct(HaploApp $app)
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
                $this->$property = HaploInput::post($property, $this->$property, $filterTypes[$property]);
            } else {
                $this->$property = HaploInput::post($property, $this->$property);
            }
        }
    }

    /**
     * @param HaploTemplate $template
     * @param array $escapeTypes
     */
    public function assignToTemplate(HaploTemplate $template, array $escapeTypes = []) {
        $this->nonce = $this->app->nonce->get();

        foreach ($this->getPublicProperties() as $object) {
            $property = $object->name;
            if (array_key_exists($property, $escapeTypes)) {
                $template->set($property, $this->$property, array(
                    'escapeMethod' => $escapeTypes[$property]
                ));
            } else {
                $template->set($property, $this->$property, array(
                    'escapeMethod' => 'escapeAttr'
                ));
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
