<?php
/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 *
 */
namespace HiPay\FullserviceMagento\Controller\Redirect;

use HiPay\FullserviceMagento\Controller\Fullservice;

/**
 * Cancel controller
 *
 * Used to redirect the customer when payment is cancelled
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class Cancel extends Fullservice
{


    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * */
    public function execute()
    {

        $lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
        if ($lastOrderId) {
            /** @var $order  \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($lastOrderId);
            if ($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {

                /* @var $cart \Magento\Checkout\Model\Cart */
                $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
                $items = $order->getItemsCollection();
                foreach ($items as $item) {
                    try {
                        $cart->addOrderItem($item);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                            $this->messageManager->addNotice($e->getMessage());
                        } else {
                            $this->messageManager->addError($e->getMessage());
                        }

                    } catch (\Exception $e) {
                        $this->messageManager->addException($e,
                            __('We can\'t add this item to your shopping cart right now.'));

                    }
                }

                $cart->save();
            }
        }

        $this->messageManager->addNoticeMessage(__('Your order was canceled.'));

        $this->_redirect('checkout/cart');


    }

}
