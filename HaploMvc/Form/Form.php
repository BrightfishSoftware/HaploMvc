<?php
namespace HaploMvc\Form;

use HaploMvc\Template\HaploTemplate;
use HaploMvc\Input\HaploInput;
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

    /**
     * @return bool
     */
    public function validate() {
        return true;
    }

    /**
     * @param array $filterTypes
     */
    public function assignFromPost(array $filterTypes = []) {
        foreach ($this->getPublicProperties() as $key => $value) {
            if (array_key_exists($key, $filterTypes)) {
                $this->$key = HaploInput::post($key, $this->$key, $filterTypes[$key]);
            } else {
                $this->$key = HaploInput::post($key, $this->$key);
            }
        }
    }

    /**
     * @param HaploTemplate $template
     * @param array $escapeTypes
     */
    public function assignToTemplate(HaploTemplate $template, array $escapeTypes = []) {
        foreach ($this->getPublicProperties() as $object) {
            $property = $object['name'];
            if (array_key_exists($property, $escapeTypes)) {
                $template->set($property, $this->$property, $escapeTypes[$property]);
            } else {
                $template->set($property, $this->$property);
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
