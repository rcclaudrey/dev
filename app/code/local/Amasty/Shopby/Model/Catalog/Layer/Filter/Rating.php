<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

class Amasty_Shopby_Model_Catalog_Layer_Filter_Rating extends Mage_Catalog_Model_Layer_Filter_Abstract
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'rating';
    }

    protected $stars = array(
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
    );

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int) $request->getParam($this->getRequestVar());
        if (!$filter || Mage::registry('am_rating_filter')) {
            return $this;
        }

        $collection = $this->getLayer()->getProductCollection();
        $select = $collection->getSelect();

        $minRating = (array_key_exists($filter, $this->stars))
            ? $this->stars[$filter]
            : 0;

        $reviewSummary = $collection->getResource()->getTable('review/review_aggregate');
        $select->joinLeft(
            array('rating' => $reviewSummary),
            sprintf('`rating`.`entity_pk_value`=`e`.entity_id
                    AND `rating`.`entity_type` = 1
                    AND `rating`.`store_id`  =  %d',
                Mage::app()->getStore()->getId()
            ),
            ''
        );
        $select->where('`rating`.`rating_summary` >= ?',
            $minRating);

        $state = $this->_createItem($this->getLabelHtml($filter), $filter)
                      ->setVar($this->_requestVar);

        $this->getLayer()->getState()->addFilter($state);

        Mage::register('am_rating_filter', true);

        return $this;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('amshopby')->__('Rating Filter');
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $data = array();
        $count = $this->_getCount();
        $currentValue = Mage::app()->getRequest()->getQuery($this->getRequestVar());

        for ($i=5;$i>=1;$i--) {
            $data[] = array(
                'label' => $this->getLabelHtml($i),
                'value' => ($currentValue == $i) ? null : $i,
                'count' => $count[($i-1)],
                'option_id' => $i,
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function _getCount()
    {
        $collection = $this->getLayer()->getProductCollection();

        $connection = $collection->getConnection();
        $connection
            ->query('SET @ONE :=0, @TWO := 0, @THREE := 0, @FOUR := 0, @FIVE := 0');

        $select = clone $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::WHERE);

        $reviewSummary = $collection->getResource()->getTable('review/review_aggregate');
        $select->joinLeft(
            array('rsc' => $reviewSummary),
            sprintf('`rsc`.`entity_pk_value`=`e`.entity_id
                AND `rsc`.entity_type = 1
                AND `rsc`.store_id  =  %d',
                Mage::app()->getStore()->getId()),
            'rsc.rating_summary AS rating'
        );

        $columns = new Zend_Db_Expr("
            IF(`rsc`.`rating_summary` <  40, @ONE := @ONE + 1, 0),
            IF(`rsc`.`rating_summary` >= 40 AND `rsc`.`rating_summary` < 60, @TWO := @TWO + 1, 0),
            IF(`rsc`.`rating_summary` >= 60 AND `rsc`.`rating_summary` < 80, @THREE := @THREE + 1, 0),
            IF(`rsc`.`rating_summary` >= 80 AND `rsc`.`rating_summary` < 100, @FOUR := @FOUR + 1, 0),
            IF(`rsc`.`rating_summary` >= 100, @FIVE := @FIVE + 1, 0)
        ");
        $select->columns($columns);
        $connection->query($select);
        $result = $connection->fetchRow('SELECT @ONE, @TWO, @THREE, @FOUR, @FIVE;');
        return array_values($result);
    }

    protected function _initItems()
    {
        $data  = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            $item = $this->_createItem(
                $itemData['label'],
                $itemData['value'],
                $itemData['count']
            );
            $item->setOptionId($itemData['option_id']);
            $items[] = $item;
        }
        $this->_items = $items;
        return $this;
    }

    /**
     * @param int $countStars
     *
     * @return string
     */
    protected function getLabelHtml($countStars)
    {
        $block = new Mage_Core_Block_Template();
        $block->setStar($countStars);
        $html = $block->setTemplate('amasty/amshopby/rating.phtml')->toHtml();
        return $html;
    }

}