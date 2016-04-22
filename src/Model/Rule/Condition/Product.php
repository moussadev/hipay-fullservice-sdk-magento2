<?php
/*
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 *
 */
namespace HiPay\FullserviceMagento\Model\Rule\Condition;

/**
 * Product rule condition data model
 *
 */
class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
	
	protected $methodCode = null;
	
    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_qty'] = __('Quantity in cart');
        $attributes['quote_item_price'] = __('Price in cart');
        $attributes['quote_item_row_total'] = __('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        //@todo reimplement this method when is fixed MAGETWO-5713
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $model->getProduct();
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $product = $this->productRepository->getById($model->getProductId());
        }

        $product->setQuoteItemQty(
            $model->getQty()
        )->setQuoteItemPrice(
            $model->getPrice() // possible bug: need to use $model->getBasePrice()
        )->setQuoteItemRowTotal(
            $model->getBaseRowTotal()
        );

        return parent::validate($product);
    }
    
    public function setMethodCode($methodCode){
    	$this->methodCode = $methodCode;
    	$this->setData('method_code',$this->methodCode);
    	return $this;
    }
    
    public function setConfigPath($configPath){
    	$this->elementName = 'rule_' . $configPath;
    	$this->setData('config_path',$configPath);
    	return $this;
    }
    
    /**
     * @return AbstractElement
     */
    public function getTypeElement()
    {
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__type',
    			'hidden',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
    					'value' => $this->getType(),
    					'no_span' => true,
    					'class' => 'hidden'
    			]
    			);
    }
    
    /**
     * @return $this
     */
    public function getAttributeElement()
    {
    	if (null === $this->getAttribute()) {
    		foreach (array_keys($this->getAttributeOption()) as $option) {
    			$this->setAttribute($option);
    			break;
    		}
    	}
    	$elt = $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__attribute',
    			'select',
    			[
    					'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
    					'values' => $this->getAttributeSelectOptions(),
    					'value' => $this->getAttribute(),
    					'value_name' => $this->getAttributeName()
    			]
    			)->setRenderer(
    					$this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
    					);
    			$elt->setShowAsText(true);
    			return $elt;
    }
    
    
    /**
     * Retrieve Condition Operator element Instance
     * If the operator value is empty - define first available operator value as default
     *
     * @return \Magento\Framework\Data\Form\Element\Select
     */
    public function getOperatorElement()
    {
    	$options = $this->getOperatorSelectOptions();
    	if ($this->getOperator() === null) {
    		foreach ($options as $option) {
    			$this->setOperator($option['value']);
    			break;
    		}
    	}
    
    	$elementId = sprintf('%s__%s__operator', $this->getPrefix(), $this->getId() . '_' . $this->getConfigPath());
    	$elementName = sprintf($this->elementName . '[%s][%s][operator]', $this->getPrefix(), $this->getId());
    	$element = $this->getForm()->addField(
    			$elementId,
    			'select',
    			[
    					'name' => $elementName,
    					'values' => $options,
    					'value' => $this->getOperator(),
    					'value_name' => $this->getOperatorName()
    			]
    			);
    	$element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
    
    	return $element;
    }
    
    /**
     * @return $this
     */
    public function getValueElement()
    {
    	$elementParams = [
    			'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
    			'value' => $this->getValue(),
    			'values' => $this->getValueSelectOptions(),
    			'value_name' => $this->getValueName(),
    			'after_element_html' => $this->getValueAfterElementHtml(),
    			'explicit_apply' => $this->getExplicitApply(),
    	];
    	if ($this->getInputType() == 'date') {
    		// date format intentionally hard-coded
    		$elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
    		$elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
    	}
    	return $this->getForm()->addField(
    			$this->getPrefix() . '__' . $this->getId() . '_' . $this->getConfigPath() . '__value',
    			$this->getValueElementType(),
    			$elementParams
    			)->setRenderer(
    					$this->getValueElementRenderer()
    					);
    }
    
}
