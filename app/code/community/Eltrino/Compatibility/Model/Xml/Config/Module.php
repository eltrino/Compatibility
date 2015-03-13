<?php

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
        // Register Psr0 AutoLoader
        Mage::getModel('eltrino_compatibility/splAutoloader')->register();

        // TODO: refactoring. Should be changed to glob by etc folder
        if (is_readable($this->_etcModuleDir)) {
            $this->loadGlobalConfig();
            $diFile = realpath($this->_etcModuleDir . DIRECTORY_SEPARATOR . 'di.xml');
            if (is_readable($diFile)) {
                $this->loadDiConfig($diFile);
            }

            // load frontend config section
            $frontendConfigDir = realpath($this->_etcModuleDir . DIRECTORY_SEPARATOR . 'frontend');
            if (is_readable($frontendConfigDir)) {
                $this->loadFrontendConfig($frontendConfigDir);
            }

            $crontabFile = realpath($this->_etcModuleDir . DIRECTORY_SEPARATOR . 'crontab.xml');
            if (is_readable($crontabFile)) {
                $this->loadCrontab($crontabFile);
            }


            $layoutPath = realpath($this->_etcModuleDir . DIRECTORY_SEPARATOR . 'view/frontend/layout');


        }
        $this->_config->saveCache();
    }

    public function loadGlobalConfig()
    {
        /** @var Mage_Core_Model_Config_Element $globalNode */
        $globalNode = $this->_config->_xml->global;
        if (is_readable($this->_moduleDir . '/Block')) {
            /** @var Mage_Core_Model_Config_Element $blocks */
            $blocks = $globalNode->blocks;
            $namespace = str_replace('_', '\\', $this->_moduleName) . '\\' . 'Block';
            $child = $blocks->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);

            // Block factory not used autoloader
            // so we need to load file here
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_moduleDir . '/Block/')) as $filename) {
                if (pathinfo($filename, PATHINFO_EXTENSION) != 'php') {
                    continue;
                }
                if (is_readable($filename)) {
                    include_once($filename);
                }
            }
        }

        if (is_readable($this->_moduleDir . '/Model')) {
            /** @var Mage_Core_Model_Config_Element $models */
            $models = $globalNode->models;
            $namespace = str_replace('_', '\\', $this->_moduleName) . '\\' . 'Model';
            $child = $models->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);

            //add resource model
            // TODO: !!! Now works only with one resouce model !!!
            if (is_readable($this->_moduleDir . '/Model/Resource')) {
                $resourceModelName = strtolower($this->_moduleName) . '_resource';
                $child->addChild('resourceModel', $resourceModelName);
                $resourceModelChild = $models->addChild($resourceModelName);
                $entities = $resourceModelChild->addChild('entities');
                foreach (glob($this->_moduleDir . '/Model/Resource/*.php') as $file) {
                    $entity = strtolower(basename($file, '.php'));
                    $entities->addChild($entity);
                    $resourceToParse = file_get_contents($file);
                    preg_match("~init\('(.*)'\,~", $resourceToParse, $ms);
                    if (!isset($ms[1])) {
                        continue;
                    }
                    $entities->$entity->addChild('table', $ms[1]);
                    $resourceModelChild->addChild('class', $namespace . '\Resource\\' . basename($file, '.php'));
                }
            }

        }

        /**
         * TODO: add check for data installer
         *
         * <resources>
         *      <eltrino_test2_setup>
         *          <setup>
         *              <module>Eltrino_Test2</module>
         *          </setup>
         *      </eltrino_test2_setup>
         * </resources>
         */
        if (is_readable($this->_moduleDir . '/sql')) {
            /** @var Mage_Core_Model_Config_Element $resources */
            $resources = $globalNode->resources;
            foreach (glob($this->_moduleDir . '/sql/*', GLOB_ONLYDIR) as $dir) {
                $nodeName = basename($dir);
                $resources->addChild($nodeName);
                $resources->$nodeName->addChild('setup');
                $resources->$nodeName->setup->addChild('module', $this->_moduleName);
            }
            // TODO: find better place for this
            Mage_Core_Model_Resource_Setup::applyAllUpdates();
        }

        if (is_readable($this->_moduleDir . '/Helper')) {
            /** @var Mage_Core_Model_Config_Element $helpers */
            $helpers = $globalNode->helpers;
            $namespace = str_replace('_', '\\', $this->_moduleName) . '\\' . 'Helper';
            $child = $helpers->addChild(strtolower($this->_moduleName));
            $child->addChild('class', $namespace);
        }

    }

    public function loadFrontendConfig($dir)
    {
        /** @var Mage_Core_Model_Config_Element $frontend */
        $frontend = $this->_config->_xml->frontend;
        foreach (glob($dir . '/*.xml') as $file) {
            $sectionName = basename($file, '.xml');
            $newConfig = Mage::getModel('core/config_base');
            $newConfig->loadFile($file);
            if ($sectionName == 'events') {
                /** @var Mage_Core_Model_Config_Element $events */
                $events = $frontend->{$sectionName};
                foreach ($newConfig->_xml->event as $newEvent) {
                    $eventName = (string)$newEvent['name'];
                    if (!isset($events->$eventName)) {
                        $child = $events->addChild((string)$newEvent['name']);
                        $observers = $child->addChild('observers');
                    } else {
                        $observers = $events->{$eventName}->observers;
                    }

                    $observerChild = $observers->addChild((string)$newEvent->observer['name']);
                    $observerChild->addChild('class', (string)$newEvent->observer['instance']);
                    $observerChild->addChild('method', (string)$newEvent->observer['method']);
                }
            }

            if ($sectionName == 'routes') {
                /** @var Mage_Core_Model_Config_Element $routers */
                $routers = $frontend->routers;
                $router = $routers->addChild($newConfig->_xml->router->route['id']);
                $router->addChild('use', (string)$newConfig->_xml->router['id']);
                $router->addChild('args');
                $router->args->addChild('frontName', (string)$newConfig->_xml->router->route['frontName']);
                $router->args->addChild('module', (string)$newConfig->_xml->router->route->module['name']);
            }
        }
    }

    public function loadDiConfig($diFile)
    {
        $newConfig = Mage::getModel('core/config_base');
        $newConfig->loadFile($diFile);
        $preference = $newConfig->getNode('preference');
        foreach ($preference as $preferenceChild) {
            $parts = explode('\\', $preferenceChild['for']);
            $rewriteModule = strtolower($parts[1]);
            $rewriteType = strtolower($parts[2]) . 's';
            unset($parts[0], $parts[1], $parts[2]);
            $rewriteFile = strtolower(implode('_', $parts));
            $rewriteModuleNode = $this->_config->getNode("global/{$rewriteType}/{$rewriteModule}");
            $rewriteModuleNode->addChild('rewrite');
            $rewriteModuleNode->rewrite->addChild($rewriteFile, (string)$preferenceChild['type']);

            // Block factory not used autoloader
            // so we need to load file here
            if ($rewriteType == 'blocks') {
                $newClassName = (string)$preferenceChild['type'];
                Mage::getModel('eltrino_compatibility/splAutoloader')->loadClass($newClassName);
            }
        }
    }

    public function loadCrontab($crontabFile)
    {
        /** @var Mage_Core_Model_Config_Element $jobs */
        $jobs = $this->_config->_xml->crontab->jobs;
        $newConfig = Mage::getModel('core/config_base');
        $newConfig->loadFile($crontabFile);
        /** @var Mage_Core_Model_Config_Element $group */
        $group = $newConfig->_xml->group;
        foreach ($group->job as $job) {
            /** @var Mage_Core_Model_Config_Element $newJob */
            $newJob = $jobs->addChild((string)$job['name']);
            $newJob->addChild('schedule');
            $newJob->schedule->addChild('cron_expr', (string)$job->schedule);
            $newJob->addChild('run');
            $newJob->run->addChild('model', (string)$job['instance'] . '::' . (string)$job['method']);
        }
    }

}