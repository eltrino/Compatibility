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
namespace Magento\Framework\Model;

class AbstractModel extends \Mage_Core_Model_Abstract
{
    /**
     * Set resource names
     *
     * If collection name is ommited, resource name will be used with _collection appended
     *
     * @param string $resourceName
     * @param string|null $resourceCollectionName
     */
    protected function _setResourceModel($resourceName, $resourceCollectionName = null)
    {
        $resourceName = str_replace('\Model\Resource', '', $resourceName);
        list($scope, $module, $model) = explode('\\', $resourceName);
        /**
         * See: Eltrino_Compatibility_Model_Xml_Config_Module line:77
         */
        $resourceName = strtolower($scope . '_' . $module . '/' /*. $model */);

        $this->_resourceName = $resourceName;
        if (is_null($resourceCollectionName)) {
            $resourceCollectionName = $resourceName . '\\Collection';
        }
        $this->_resourceCollectionName = $resourceCollectionName;
    }
}
