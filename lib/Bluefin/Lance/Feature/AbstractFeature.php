<?php

namespace Bluefin\Lance\Feature;

use Bluefin\Lance\Entity;
 
class AbstractFeature
{
    /**
     * @var \Bluefin\Lance\Entity
     */
    protected $_entity;
    protected $_modifiers;
    protected $_featureName;
    protected $_fields;

    public function __construct($featureName, Entity $entity, array $modifiers = array())
    {
        $this->_featureName = $featureName;
        $this->_entity = $entity;
        $this->_modifiers = $modifiers;
        $this->_fields = [];
    }

    private function setEntity(Entity $entity)
    {
        $this->_entity = $entity;
    }

    public function cloneFeatureTo(Entity $entity)
    {
        $feature = clone $this;
        $feature->setEntity($entity);
        return $feature;
    }

    public function getFeatureName()
    {
        return $this->_featureName;
    }

    public function getRelatedFields()
    {
        return $this->_fields;
    }
}
