<?php
namespace Magento\Framework\App\Action;

class Context extends \Varien_Object
{

    /** @var \Magento\Framework\App\Action\Action */
    protected $_action;

    /**
     * @param $action \Magento\Framework\App\Action\Action
     */
    public function __construct($action)
    {
        parent::__construct();
        $this->_action = $action;
    }

    public function getView()
    {
        return $this->_action;
    }
}