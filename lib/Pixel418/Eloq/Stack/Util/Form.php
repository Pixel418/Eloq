<?php

/* This file is part of the Eloq project, which is under MIT license */

namespace Pixel418\Eloq\Stack\Util;

class Form
{


    /* ATTRIBUTES
     *************************************************************************/
    const INPUT_ARRAY = 0;
    private $namespace;
    protected $population;
    protected $populationType;
    protected $inputs = array();
    protected $isTreated = FALSE;


    /* CONSTRUCTOR
     *************************************************************************/
    public function __construct($populationType = INPUT_POST)
    {
        $this->setPopulationType($populationType);
        $this->namespace = \UObject::getNamespace($this);
    }


    /* SETTER METHODS
     *************************************************************************/
    public function setPopulationType($populationType)
    {
        $this->populationType = $populationType;
        return $this;
    }

    public function setPopulation(array $population){
        $this->setPopulationType(self::INPUT_ARRAY);
        $this->population = $population;
        return $this;
    }

    public function addInput($name, $address=NULL, $populationType=NULL)
    {
        $inputClass = $this->namespace.'\\FormInput';
        $input = new $inputClass($name);
        $this->inputs[$name] = $input;
        if ($address) {
            $this->setInputAddress($name, $address, $populationType);
        }
        return $this;
    }

    public function removeInput($name)
    {
        unset($this->inputs[$name]);
        return $this;
    }

    public function setInputAddress($name, $address, $populationType=NULL) {
        $input = $this->getInput($name);
        $input->address = $address;
        $input->populationType = $populationType;
        return $this;
    }

    public function setInputDefaultValue($name, $defaultValue) {
        $input = $this->getInput($name);
        $input->defaultValue = $defaultValue;
        return $this;
    }


    /* FORM GETTER METHODS
     *************************************************************************/
    public function treat()
    {
        if(!$this->isTreated) {
            $population = $this->getPopulation();
            $this->initFetchValues($population);
            $this->isTreated = TRUE;
            if ($this->isActive()) {
                $this->validFetchValues();
            }
        }
        return $this;
    }

    public function isActive()
    {
        $this->treat();
        foreach ($this->inputs as $input) {
            if ($input->isActive) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function isValid()
    {
        $this->treat();
        foreach ($this->inputs as $input) {
            if (!$input->isValid()) {
                return FALSE;
            }
        }
        return TRUE;
    }


    /* INPUT GETTER METHODS
     *************************************************************************/
    public function __get($name)
    {
        return $this->getInputValue($name);
    }

    public function getInputValue($name)
    {
        $input = $this->getInput($name);
        return $input->getValue();
    }

    public function getInputError($name){
        $input = $this->getInput($name);
        return $input->error;
    }

    public function isInputValid($name)
    {
        $this->treat();
        $input = $this->getInput($name);
        return $input->isValid();
    }


    /* PROTECTED METHODS
     *************************************************************************/
    protected function getPopulation($populationType=NULL){
        if (is_null($populationType)) {
            $populationType = $this->populationType;
        }
        if ($populationType==self::INPUT_ARRAY) {
            return $this->population;
        }
        return filter_input_array($populationType);
    }

    protected function initFetchValues($population)
    {
        foreach ($this->inputs as $input) {
            $input->initFetchValue($population);
        }
    }

    protected function validFetchValues()
    {
        foreach ($this->inputs as $input) {
            $input->validFetchValue();
        }
    }

    protected function getInput($name)
    {
        if (!isset($this->inputs[$name])) {
            throw new \RuntimeException('Try to get an unknown input: '.$name);
        }
        return $this->inputs[$name];
    }
}