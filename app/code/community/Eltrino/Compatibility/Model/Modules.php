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
class Eltrino_Compatibility_Model_Modules extends Eltrino_Compatibility_Model_Observer
{
    /**
     * Main point to load configuration files.
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
     * Retrieve list of magento2 modules on the basis of files "module.xml".
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
            foreach (glob(Mage::getBaseDir().'/app/code/'.$pool.'/*/*/') as $moduleDir) {
                $file = realpath($moduleDir.'etc/module.xml');
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
