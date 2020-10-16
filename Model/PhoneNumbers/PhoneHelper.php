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
        'FR' => [
            'code_geographic' => '33',
            'pattern' => '/^((\+|00)33|0)[1-9][0-9]{8}$/',
            'invalid_message' => 'a French',
        ],
        'IT' => [
            'code_geographic' => '39',
            'pattern' => '/^((\+|00)39)?3[1-6][0-9]{8}$/',
            'invalid_message' => 'an Italian',
        ],
        'BE' => [
            'code_geographic' => '32',
            'pattern' => '/^((\+|00)32)4(60|[789][0-9])[0-9]{6}$/',
            'invalid_message' => 'a Belgian',
        ],
        'PT' => [
            'code_geographic' => '351',
            'pattern' => '/^((\+|00)351)?9[1236][0-9]{7}$/',
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
