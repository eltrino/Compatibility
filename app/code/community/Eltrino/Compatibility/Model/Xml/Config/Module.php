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
class Eltrino_Compatibility_Model_Xml_Config_Module extends Mage_Core_Model_Config_Base
{
    /** @var Mage_Core_Model_Config|null */
    protected $_config = null;

    protected $_moduleDir = null;

    protected $_etcModuleDir = null;

    protected $_moduleName = null;

    public function __construct($arg)
    {
        list($sourceData, $moduleName) = $arg;
        parent::__construct($sourceData);
        $this->_config = Mage::getConfig();
        $this->_moduleName = $moduleName;
        $this->_moduleDir = realpath(Mage::getModuleDir('', $moduleName));
        $this->_etcModuleDir = realpath(Mage::getModuleDir('etc', $moduleName));
    }

    public function loadModuleConfig()
    {
        /* Register Psr0 AutoLoader */
        Mage::getModel('eltrino_compatibility/splAutoloader')->register();

        $this->loadGlobalConfig();
        $this->loadDiConfig();
        $this->loadFrontendConfig();
        $this->loadCrontabConfig();
    }

    public function loadGlobalConfig()
    {
        /** @var Mage_Core_Model_Config_Element $globalNode */
        $globalNode = $this->_config->getNode('global');
        if (is_readable($this->_moduleDir.'/Block')) {
            /** @var Mage_Core_Model_Config_Element $blocks */
            $blocks = $globalNode->blocks;
            $namespace = str_replace('_', '\\', $this->_moduleName).'\\'.'Block';
            $child = $blocks->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);

            // Block factory not used autoloader
            // so we need to load file here
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_moduleDir.'/Block/')) as $filename) {
                if (pathinfo($filename, PATHINFO_EXTENSION) != 'php') {
                    continue;
                }
                if (is_readable($filename)) {
                    include_once $filename;
                }
            }
        }

        if (is_readable($this->_moduleDir.'/Model')) {
            /** @var Mage_Core_Model_Config_Element $models */
            $models = $globalNode->models;
            $namespace = str_replace('_', '\\', $this->_moduleName).'\\'.'Model';
            $child = $models->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);

            // Add resource model
            // TODO: Now works only with one resource model
            if (is_readable($this->_moduleDir.'/Model/Resource')) {
                $resourceModelName = strtolower($this->_moduleName).'_resource';
                $child->addChild('resourceModel', $resourceModelName);
                $resourceModelChild = $models->addChild($resourceModelName);
                $entities = $resourceModelChild->addChild('entities');
                foreach (glob($this->_moduleDir.'/Model/Resource/*.php') as $file) {
                    $entity = strtolower(basename($file, '.php'));
                    $entities->addChild($entity);
                    $resourceToParse = file_get_contents($file);
                    preg_match("~init\('(.*)'\,~", $resourceToParse, $ms);
                    if (!isset($ms[1])) {
                        continue;
                    }
                    $entities->$entity->addChild('table', $ms[1]);
                    $resourceModelChild->addChild('class', $namespace.'\Resource\\'.basename($file, '.php'));
                }
            }
        }

        if (is_readable($this->_moduleDir.'/sql')) {
            /** @var Mage_Core_Model_Config_Element $resources */
            $resources = $globalNode->resources;
            foreach (glob($this->_moduleDir.'/sql/*', GLOB_ONLYDIR) as $dir) {
                $nodeName = basename($dir);
                $resources->addChild($nodeName);
                $resources->$nodeName->addChild('setup');
                $resources->$nodeName->setup->addChild('module', $this->_moduleName);
            }
        }

        if (is_readable($this->_moduleDir.'/Helper')) {
            /** @var Mage_Core_Model_Config_Element $helpers */
            $helpers = $globalNode->helpers;
            $namespace = str_replace('_', '\\', $this->_moduleName).'\\'.'Helper';
            $child = $helpers->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);
        }
    }

    public function loadFrontendConfig()
    {
        $frontendConfigDir = realpath($this->_etcModuleDir.DIRECTORY_SEPARATOR.'frontend');
        if (!is_readable($frontendConfigDir)) {
            return;
        }
        /** @var Mage_Core_Model_Config_Element $frontend */
        $frontend = $this->_config->getNode('frontend');
        foreach (glob($frontendConfigDir.'/*.xml') as $file) {
            $sectionName = basename($file, '.xml');
            $newConfig = Mage::getModel('core/config_base');
            $newConfig->loadFile($file);
            if ($sectionName == 'events') {
                /** @var Mage_Core_Model_Config_Element $events */
                $events = $frontend->{$sectionName};
                foreach ($newConfig->getNode('event') as $newEvent) {
                    $eventName = (string) $newEvent['name'];
                    if (!isset($events->$eventName)) {
                        $child = $events->addChild((string) $newEvent['name']);
                        $observers = $child->addChild('observers');
                    } else {
                        $observers = $events->{$eventName}->observers;
                    }

                    $observerChild = $observers->addChild((string) $newEvent->observer['name']);
                    $observerChild->addChild('class', (string) $newEvent->observer['instance']);
                    $observerChild->addChild('method', (string) $newEvent->observer['method']);
                }
            }

            if ($sectionName == 'routes') {
                /** @var Mage_Core_Model_Config_Element $routers */
                $router = $frontend->routers->addChild(
                    $newConfig->getNode('router')->getAttribute('id')
                );
                $router->addChild('use', (string) $newConfig->getNode('router')->getAttribute('id'));
                $router->addChild('args');
                $router->args->addChild('frontName',
                    (string) $newConfig->getNode('router/route')->getAttribute('frontName'));
                $router->args->addChild('module',
                    (string) $newConfig->getNode('router/route/module')->getAttribute('name')
                );
            }
        }
    }

    public function loadDiConfig()
    {
        $diFile = realpath($this->_etcModuleDir.DIRECTORY_SEPARATOR.'di.xml');
        if (!is_readable($diFile)) {
            return;
        }
        $newConfig = Mage::getModel('core/config_base');
        $newConfig->loadFile($diFile);
        $preference = $newConfig->getNode('preference');
        foreach ($preference as $preferenceChild) {
            $parts = explode('\\', $preferenceChild['for']);
            $rewriteModule = strtolower($parts[1]);
            $rewriteType = strtolower($parts[2]).'s';
            unset($parts[0], $parts[1], $parts[2]);
            $rewriteFile = strtolower(implode('_', $parts));
            $rewriteModuleNode = $this->_config->getNode("global/{$rewriteType}/{$rewriteModule}");
            $rewriteModuleNode->addChild('rewrite');
            $rewriteModuleNode->rewrite->addChild($rewriteFile, (string) $preferenceChild['type']);

            // Block factory not used autoloader
            // so we need to include file here
            if ($rewriteType == 'blocks') {
                $newClassName = (string) $preferenceChild['type'];
                Mage::getModel('eltrino_compatibility/splAutoloader')->loadClass($newClassName);
            }
        }
    }

    public function loadCrontabConfig()
    {
        $crontabFile = realpath($this->_etcModuleDir.DIRECTORY_SEPARATOR.'crontab.xml');
        if (!is_readable($crontabFile)) {
            return;
        }

        /** @var Mage_Core_Model_Config_Element $jobs */
        $jobs = $this->_config->getNode('crontab/jobs');
        $newConfig = Mage::getModel('core/config_base');
        $newConfig->loadFile($crontabFile);
        /* @var Mage_Core_Model_Config_Element $group */
        foreach ($newConfig->getNode('group/job') as $job) {
            /** @var Mage_Core_Model_Config_Element $newJob */
            $newJob = $jobs->addChild((string) $job['name']);
            $newJob->addChild('schedule');
            $newJob->schedule->addChild('cron_expr', (string) $job->schedule);
            $newJob->addChild('run');
            $newJob->run->addChild('model', (string) $job['instance'].'::'.(string) $job['method']);
        }
    }
}
