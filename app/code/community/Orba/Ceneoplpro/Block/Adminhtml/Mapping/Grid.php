<?php

class Orba_Ceneoplpro_Block_Adminhtml_Mapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ceneo_mapping_grid');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ceneoplpro/mapping')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('ceneoplpro')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $ceneo_categories = Mage::getModel('ceneoplpro/category')->toOptionHash(false);
        $this->addColumn('ceneopro_category_id', array(
            'header' => Mage::helper('ceneoplpro')->__('Ceneo Category'),
            'align' => 'left',
            'index' => 'ceneopro_category_id',
            'type' => 'options',
            'options' => $ceneo_categories,
            'filter_condition_callback' => array(
                $this,
                '_filterCeneoCategoriesCondition'
            )
        ));
        $this->addColumn('priority', array(
            'header' => Mage::helper('ceneoplpro')->__('Priority'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'priority',
        ));

        if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('ceneoplpro')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('ceneoplpro')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('ceneoplpro')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('ceneoplpro')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('ceneoplpro')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('ceneoplpro')->__('XML'));
        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('ceneo_massaction');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('ceneoplpro')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('ceneoplpro')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('ceneoplpro')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('ceneoplpro')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('ceneoplpro')->__('Enabled'),
                            '0' => Mage::helper('ceneoplpro')->__('Disabled'),
                        )
                    )
                )
            )
        );
        return $this;
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }


    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }


    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }

    protected function _filterCeneoCategoriesCondition($collection, $column) {
        $value = $column->getFilter()->getValue();
        if ($value && !empty($value)) {
            $ids = array($value) + Mage::getModel('ceneoplpro/category')->getChildrenIds($value);
            $this->getCollection()->addFieldToFilter('ceneopro_category_id', array(
                'in' => $ids,
                'notnull' => true,
                'neq' => ''
            ));
        }
    }
}
