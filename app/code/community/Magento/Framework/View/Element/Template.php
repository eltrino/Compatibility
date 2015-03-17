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
namespace Magento\Framework\View\Element;

class Template extends \Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $parts = explode('\\', get_class($this));
        $moduleName = $parts[0] . '_' . $parts[1];
        $filePath = \Mage::getModuleDir('', $moduleName) . '/view/frontend/templates/' . $this->getTemplate();
        $html = '';
        if (is_readable($filePath)) {
            ob_start();
            $block = $this;
            include($filePath);
            $html = ob_get_clean();
        }
        $this->setTemplate('');

        return $html;
    }
}