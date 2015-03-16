<?php

class Eltrino_Compatibility_Model_Observer
{
    const CACHE_TAG = 'COMPATIBILITY';
    const CACHE_GROUP = 'eltrino_compatibility';

    const COMPATIBILITY_MODULES_FILE_CACHE = 'compatibility_modules_file_cache';

    /** @var array */
    protected $_cacheTags = array();

    /** @var array */
    static $_loadedModules = array();

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
            static::CACHE_TAG
        );
    }

    /**
     * This is a first observer in magento
     * where we can update module list and configuration
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
