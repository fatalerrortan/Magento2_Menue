<?php
namespace Nexorder\Menue\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
// * -1 if the first version is lower than the second,
// * 0 if they are equal, and
// * 1 if the second is lower.
//        if(version_compare($context->getVersion(), '1.0.1') < 0){
            $installer = $setup;
            $installer->startSetup();
            $table  = $installer->getConnection()
                ->newTable($installer->getTable('no_nutrition_goals'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'goal',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Goal Label'
                )
                ->addColumn(
                    'dishType',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Dish Type'
                )
                ->addColumn(
                    'attrCate',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => true],
                    'Attribute Categroy'
                )
                ->addColumn(
                    'goalAttr',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Attribute Categroy'
                )
                ->addColumn(
                    'goalOperator',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Goal Operator'
                )
                ->addColumn(
                    'goalValue',
                    Table::TYPE_FLOAT,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Goal Value'
                );
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }
//    }
}