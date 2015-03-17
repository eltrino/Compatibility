<?php

/**
 * The MIT License (MIT).
 *
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Eltrino_Compatibility_Model_Observer
{
    const CACHE_TAG = 'COMPATIBILITY';
    const CACHE_GROUP = 'eltrino_compatibility';

    const COMPATIBILITY_MODULES_FILE_CACHE = 'compatibility_modules_file_cache';

    /** @var array */
    protected $_cacheTags = array();

    /** @var array */
    public static $_loadedModules = array();

    /** @var array */
    protected $_allowedPools = array(
        'local',
        'community',
    );

    /** @var bool */
    protected $_useCache = false;

    public function __construct()
    {
        $this->_useCache = Mage::app()->useCache(static::CACHE_GROUP);
        $this->_cacheTags = array(
            Mage_Core_Model_Config::CACHE_TAG,
            static::CACHE_TAG,
        );
    }

    /**
     * This is a first observer in magento
     * where we can update module list and configuration.
     *
     * @param $observer
     */
    public function resourceGetTableName($observer)
    {
        if ($observer->getTableName() !== 'core_website') {
            return;
        }

        try {
            Mage::getSingleton('eltrino_compatibility/modules')->loadModules();
            Mage_Core_Model_Resource_Setup::applyAllUpdates();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function controllerActionLayoutRenderBefore($observer)
    {
        try {
            Mage::getSingleton('eltrino_compatibility/layout')->addLayoutUpdates();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function getLoadedModules()
    {
        return static::$_loadedModules;
    }

    public function addLoadedModules($modules)
    {
        if (!is_array($modules)) {
            $modules = array($modules);
        }
        static::$_loadedModules = array_merge(static::$_loadedModules, $modules);
    }

    public function getCache()
    {
        if ($this->_useCache) {
            return Mage::app()->getCache();
        }

        return false;
    }
}
