<?php
namespace Nexorder\Menue\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface{

    protected $_logger;
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){

    $installer = $setup;
    $installer->startSetup();
    $this->_logger->addDebug(print_r($context->getVersion(),true));
    $this->_logger->addDebug(print_r('test',true));
//        exit("just for test");
//        if(version_compare($context->getVersion(), '1.0.1') === -1){
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('no_subaccounts_order'),
                'delivery_from',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => 255,
                    'comment' => 'Delivery Date Begin'
                )
            )
            ->addColumn(
                $installer->getTable('no_subaccounts_order'),
                'delivery_to',
                array(
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => 255,
                    'comment' => 'Delivery Date Begin'
                )
            );
//        }

    $installer->endSetup();
    }
}
