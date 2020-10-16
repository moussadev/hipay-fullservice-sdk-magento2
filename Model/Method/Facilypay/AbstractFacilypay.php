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
namespace HiPay\FullserviceMagento\Model\Method\Facilypay;

use HiPay\FullserviceMagento\Model\Method\AbstractMethodAPI;
use HiPay\FullserviceMagento\Model\PhoneNumbers\PhoneHelper;
use \Magento\Framework\Exception\LocalizedException;

class AbstractFacilypay extends AbstractMethodAPI
{

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();

        $order = $info->getQuote();
        if ($info->getOrder()) {
            $order = $info->getOrder();
        }

        $billingAddress = $order->getBillingAddress();
        $country = $billingAddress->getCountryId();

        if (!PhoneHelper::isPhoneValid($billingAddress->getTelephone(), $country, $billingAddress)) {
            throw new LocalizedException(
                __(PhoneHelper::getInvalidMessageByCountry($country))
            );
        }

        return $this;
    }
}
