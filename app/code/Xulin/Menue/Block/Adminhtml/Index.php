<?php
namespace Nextorder\Menue\Block\Adminhtml;

class Index extends \Magento\Framework\View\Element\Template{
    public $_helper;
    protected $_logger;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Nextorder\Menue\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ){
        $this->_helper = $helper;
        $this->_logger = $logger;
        parent::__construct($context, $data);
    }

    public function getGoalsTable(){
        $goalLabels = array_column($this->_helper->getGoalDefinition(),'label');

        return $goalLabels;
    }

    public function getAttrOptions(){
        $result = [];
        $attrCates = ['nof_animalproducts','nof_cropproducts','nof_processedfoods'];
        foreach ($attrCates as $attrCate){
            $options = $this->_helper->getProductAttrLabel($attrCate, true);
            $result[$attrCate] = $options;
        }
        $dailyAttrs = ['nof_calories', 'nof_protein', 'nof_fat', 'nof_carbs'];
        foreach ($dailyAttrs as $dailyAttr){
            $result['daily'][] = $this->_helper->getProductAttrLabel($dailyAttr);
        }
//        $this->_logger->addDebug(print_r($result, true));
        return $result;
    }
}