<?php

namespace Magento\Framework;

class Registry extends \Varien_Object
{
    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        \Mage::register($key, $value, $graceful);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return \Mage::registry($key);
    }
}

