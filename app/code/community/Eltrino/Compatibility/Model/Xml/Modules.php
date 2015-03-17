<?php

/**
 * The MIT License (MIT)
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
class Eltrino_Compatibility_Model_Xml_Modules extends Mage_Core_Model_Config_Base
{
    /** @var Mage_Core_Model_Config_Base|null */
    protected $_magento2config = null;

    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData = null);
        $this->_magento2config = new Mage_Core_Model_Config_Base();
    }

    public function loadModules(array $files)
    {
        $this->loadString('<config/>');
        $loadedModules = array();
        foreach ($files as $file) {

            $this->_magento2config->loadFile($file);
            $moduleName = $this->_magento2config->getNode('module')->getAttribute('name');

            if ($this->isModuleLoaded($moduleName)) {
                $loadedModules[] = $moduleName;
                continue;
            }

            $loadedModules[] = $moduleName;
            $version = $this->_magento2config->getNode('module')->getAttribute('schema_version');
            $modules = Mage::getConfig()->getNode('modules');
            $child = $modules->addChild($moduleName);
            $child->addChild('active', 'true');
            $child->addChild('codePool', 'community');
            $child->addChild('version', $version);
            $this->loadString('<config/>');
        }

        return $loadedModules;
    }

    public function isModuleLoaded($moduleName)
    {
        $node = Mage::getConfig()->getNode('modules/' . $moduleName);

        return (bool)$node;
    }

}