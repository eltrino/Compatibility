<?php

class Eltrino_Compatibility_Model_Modules extends Eltrino_Compatibility_Model_Observer
{
    /**
     * Main point to load configuration files
     */
    public function loadModules()
    {
        $files = $this->fetchFiles();

        if (empty($files)) {
            return;
        }

        $loadedModules = Mage::getModel('eltrino_compatibility/xml_modules')->loadModules($files);

        if (empty($loadedModules)) {
            return;
        }

        static::$_loadedModules = array_merge(static::$_loadedModules, $loadedModules);

        Mage::getModel('eltrino_compatibility/xml_config')->loadConfig($loadedModules);

        Mage::getConfig()->saveCache();
    }

    /**
     * Retrieve list of magento2 modules on the basis of files "module.xml"
     *
     * @return array
     */
    public function fetchFiles()
    {
        if ($cache = $this->getCache()) {
            if ($files = $cache->load(static::COMPATIBILITY_MODULES_FILE_CACHE)) {
                return unserialize($files);
            }
        }

        $files = array();
        foreach ($this->_allowedPools as $pool) {
            foreach (glob(Mage::getBaseDir() . '/app/code/' . $pool . '/*/*/') as $moduleDir) {
                $file = realpath($moduleDir . 'etc/module.xml');
                if (file_exists($file)) {
                    $files[] = $file;
                }
            }
        }

        if ($cache) {
            $cache->save(serialize($files), static::COMPATIBILITY_MODULES_FILE_CACHE, array(static::CACHE_TAG));
        }

        return $files;
    }
}