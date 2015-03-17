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
namespace Magento\Framework\Model\Resource\Db;

class AbstractDb extends \Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _init($mainTable, $idFieldName)
    {
        $config = \Mage::getConfig()->getNode('global/models');
        $resource = $config->xpath("//*[table='{$mainTable}']")[0];
        $table = $resource->xpath('.')[0]->getName();
        $resourceName = $resource->xpath('../..')[0]->getName();
        $model = str_replace('_resource', '', $resourceName);

        parent::_init($model . '/' . $table, $idFieldName);
    }
}
