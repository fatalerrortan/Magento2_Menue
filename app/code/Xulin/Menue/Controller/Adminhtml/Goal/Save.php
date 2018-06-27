<?php
namespace Nextorder\Menue\Controller\Adminhtml\Goal;

class Save extends \Magento\Backend\App\Action
{
    protected $_logger;
    protected $_nGoalsFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Nextorder\Menue\Model\NgoalsFactory $nGoalsFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_logger = $logger;
        $this->_nGoalsFactory = $nGoalsFactory;
    }

    public function execute(){
        $goalDefs =json_decode($this->getRequest()->getParam('goalDefs'));
        $nGoalsModel = $this->_nGoalsFactory->create();
        $nGoalsModelCollection = $nGoalsModel->getCollection();
        $nGoalsModelCollection->addFieldToFilter('goal', $goalDefs->_goal)->walk('delete');

        foreach ($goalDefs->_defs as $def){
            $nGoalsModel->setGoal($goalDefs->_goal);
            $nGoalsModel->setData('dishType', $def->_dishType);
            $nGoalsModel->setData('goalType', $def->_goalType);
            $nGoalsModel->setData('attrCate', $def->_attrCate);
            $nGoalsModel->setData('goalAttr', $def->_goalAttr);
            $nGoalsModel->setData('goalOperator', $def->_goalOperator);
            $nGoalsModel->setData('goalValue', $def->_goalValue);
            $nGoalsModel->save();
            $nGoalsModel->unsetData();
        }
       echo "success";
    }
}
