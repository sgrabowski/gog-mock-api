<?php

namespace App\Mapping;

use AutoMapperPlus\PropertyAccessor\PropertyAccessor;
use Doctrine\Common\Inflector\Inflector;

class MutatorPropertyAccessor extends PropertyAccessor
{
    private $setterPrefix = "set";
    private $ignoreNulls = false;

    /**
     * @param string $setterPrefix
     */
    public function setSetterPrefix(string $setterPrefix): void
    {
        $this->setterPrefix = $setterPrefix;
    }

    /**
     * @param bool $ignoreNulls
     */
    public function setIgnoreNulls(bool $ignoreNulls): void
    {
        $this->ignoreNulls = $ignoreNulls;
    }

    public function setProperty($object, string $propertyName, $value): void
    {
        if ($this->hasSetter($object, $propertyName)) {

            if(is_array($value) || $value instanceof \Traversable) {
                foreach ($value as $item) {
                    if($this->ignoreNulls && $value === null) continue;
                    $object->{$this->resolveMethodName($propertyName)}($item);
                }
            } else {
                if($this->ignoreNulls && $value === null) return;
                $object->{$this->resolveMethodName($propertyName)}($value);
            }

            return;
        }

        parent::setProperty($object, $propertyName, $value);
    }

    protected function hasSetter($object, $propertyName)
    {
        return is_callable([$object, $this->resolveMethodName($propertyName)]);
    }

    private function resolveMethodName($propertyName)
    {
        return $this->setterPrefix . ucfirst($this->normalizeProperty($propertyName));
    }

    private function normalizeProperty($propertyName)
    {
        return Inflector::singularize($propertyName);
    }
}