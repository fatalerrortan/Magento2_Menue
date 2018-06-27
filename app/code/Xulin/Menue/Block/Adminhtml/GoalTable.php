<?php
namespace Nextorder\Menue\Block\Adminhtml;

class GoalTable extends \Magento\Framework\View\Element\Template{
    public $_helper;
    public $_goalLabels;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Nextorder\Menue\Helper\Data $helper,
        array $data = []
    ){
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    public function setGoalLabels($goalLabels){
        return $this->_goalLabels = $goalLabels;
    }
}