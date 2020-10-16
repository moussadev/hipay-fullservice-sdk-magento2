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

namespace HiPay\FullserviceMagento\Model\PhoneNumbers;

/**
 * Class API PaymentMethod
 *
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 */
class PhoneHelper
{
    /**
     * @var string Base error message
     */
    private const ERROR_MSG_NOT_VALID = 'The format of the phone number must match {COUNTRY} phone.';

    /**
     * @var array Regroup all phone infos
     */
    const PHONE_INFOS = [
        /**
         * Fixed: +33 2 51 xx xx xx or +33 9 xx xx xx xx
         * Mobile: +33 6 64 xx xx xx
         */
        'FR' => [
            'code_geographic' => '33',
            'pattern' => '/^(\+|00)33[1-9]\d{8}$/',
            'invalid_message' => 'a French',
        ],
        /**
         * Fixed: +39 051 xxxx xxxx or +39 06 xxxx xxxx
         * Mobile: +39 310 xxx xxxxx
         */
        'IT' => [
            'code_geographic' => '39',
            'pattern' => '/^(\+|00)39(3\d{9}|0[1-9]{1,2}\d{7,8})$/',
            'invalid_message' => 'an Italian',
        ],
        /**
         * Fixed: +32 2 512 xx xx or +32 50 33 xx xx
         * Mobile: +32 478 xx xx xx
         */
        'BE' => [
            'code_geographic' => '32',
            'pattern' => '/^(\+|00)32(4\d{8}|[1-9]{1,2}\d{6,7})$/',
            'invalid_message' => 'a Belgian',
        ],
        /**
         * Fixed: +351 962 xxx xxx or +351 261 xxx xxx
         * Mobile: +351 925 xxx xxx
         */
        'PT' => [
            'code_geographic' => '351',
            'pattern' => '/^(\+|00)351(9[01236]\d{7}|\d{9})$/',
            'invalid_message' => 'a Portuguese',
        ],
    ];

    /**
     * Return phone error message by country
     *
     * @param string $country
     * @return string
     */
    public static function getInvalidMessageByCountry($country)
    {
        return str_replace('{COUNTRY}', self::PHONE_INFOS[$country]['invalid_message'], self::ERROR_MSG_NOT_VALID);
    }

    /**
     * Return phone pattern by country
     *
     * @param string $country
     * @return string
     */
    public static function getPatternByCountry($country)
    {
        return self::PHONE_INFOS[$country]['pattern'];
    }

    /**
     * Return phone geographic code by country
     *
     * @param string $country
     * @return string
     */
    public static function getCodeGeographicByCountry($country)
    {
        return self::PHONE_INFOS[$country]['code_geographic'];
    }

    /**
     * Check if $phone is valid according to $country
     *
     * @param string $phone
     * @param string $country
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     * @return boolean
     */
    public static function isPhoneValid($phone, $country, $billingAddress)
    {
        try {
            $phone = preg_replace('/ /', '', $phone);

            $countryPhoneCode = self::getCodeGeographicByCountry($country);
            if (!preg_match('/^((\+|00)' . $countryPhoneCode . '|0)/', $phone)) {
                $phone = '+' . $countryPhoneCode . $phone;
            }
            if (preg_match('/^0/', $phone)) {
                $phone = preg_replace('/^0/', '+' . $countryPhoneCode, $phone);
            }

            if ($billingAddress) {
                $billingAddress->setTelephone($phone);
            }

            return preg_match(self::getPatternByCountry($country), $phone);
        } catch (\Exception $e) {
            $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
            $logger->critical($e);
            return false;
        }
    }
}
