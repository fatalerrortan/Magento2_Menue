<?php
namespace Nextorder\Menue\Model;
//use Magento\Cron\Exception;
class Menue extends \Magento\Framework\Model\AbstractModel{
//            implements \Magento\Framework\DataObject\IdentityInterface{

//    const CACHE_TAG = 'nextorder_menue_test';
    /*
     * Tags for trigger
     */
//    protected $_cacheTag = 'nextorder_menue_test';
//    protected $_eventPrefix = 'nextorder_menue_test';
//    protected $_eventObject = 'nextorder_menue_test';
    protected $_dateTime;
    /*
     * Define resource model
     */
    protected function _construct(){
        $this->_init('Nextorder\Menue\Model\ResourceModel\Menue');
    }
    /*
     * The IdentityInterface will force Model class define the getIdentities() method which will return a unique id for the model.
     * You must only use this interface if your model required cache refresh after database operation and render information to the frontend page.
     */
//    public function getIdentities(){
//        return [self::CACHE_TAG . '_' . $this->getId()];
//    }

//    public function getDefaultValues(){
//        $values = [];
//
//        return $values;
//    }
    /*
     * implement interface
     */
//    public function getTestId(){}
//    public function setTestId(){}
//    public function getLabel(){}
//    public function setLabel(){}
//    public function getValue(){}
//    public function setValue(){}
}