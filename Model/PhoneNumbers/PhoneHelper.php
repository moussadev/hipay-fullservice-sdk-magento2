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

class PhoneHelper
{
    private const ERROR_MSG = 'The format of the phone number must match a {COUNTRY} phone.';
    const PHONE_INFOS = [
        'FR' => [
            'code_geographic' => '33',
            'pattern' => '/^((\+|00)33|0)[1-9][0-9]{8}$/',
            'error_message' => 'French',
        ],
        'IT' => [
            'code_geographic' => '39',
            'pattern' => '/^((\+|00)39)?3[1-6][0-9]{8}$/',
            'error_message' => 'Italian',
        ],
        'BE' => [
            'code_geographic' => '32',
            'pattern' => '/^((\+|00)32)4(60|[789][0-9])[0-9]{6}$/',
            'error_message' => 'Belgian',
        ],
        'PT' => [
            'code_geographic' => '351',
            'pattern' => '/^((\+|00)351)?9[1236][0-9]{7}$/',
            'error_message' => 'Portuguese',
        ],
    ];

    public static function getErrorMessageFromCountry($country)
    {
        return str_replace('{COUNTRY}', self::PHONE_INFOS[$country]['error_message'], self::ERROR_MSG);
    }

    public static function getPatternFromCountry($country)
    {
        return self::PHONE_INFOS[$country]['pattern'];
    }

    public static function getCodeGeographicFromCountry($country)
    {
        return self::PHONE_INFOS[$country]['code_geographic'];
    }

    public static function isPhoneValid($phone, $country)
    {
        try {
            $phone = preg_replace('/ /', '', $phone);

            $countryPhoneCode = self::getCodeGeographicFromCountry($country);
            if (!preg_match('/^((\+|00)' . $countryPhoneCode . '|0)/', $phone)) {
                $phone = '+' . $countryPhoneCode . $phone;
            }

            if (preg_match(self::getPatternFromCountry($country), $phone)) {
                return $phone;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
