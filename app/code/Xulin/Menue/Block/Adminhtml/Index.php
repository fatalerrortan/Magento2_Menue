<?php
namespace Nextorder\Menue\Block\Adminhtml;

class Index extends \Magento\Framework\View\Element\Template{
    public $_helper;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Nextorder\Menue\Helper\Data $helper,
        array $data = []
    ){
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    public function getGoalsTable(){
        $goalLabels = array_column($this->_helper->getGoalDefinition(),'label');
//        $this->getLayout()->addBlock('Nextorder\Menue\Block\Adminhtml\GoalTable','goalTable','goalAdmin')
//            ->setTemplate('Nextorder_Menue::goalTable.phtml');
//        $this->getChildBlock('goalTable')->setGoalLabels($goals);
//        return $this->getChildHtml('goalTable');
        return $goalLabels;
    }


}