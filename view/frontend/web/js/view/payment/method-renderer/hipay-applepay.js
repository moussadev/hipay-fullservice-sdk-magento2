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
 * @package HiPay\FullserviceMagento
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */

define([
  'ko',
  'jquery',
  'Magento_Checkout/js/view/payment/default',
  'Magento_Checkout/js/action/place-order',
  'Magento_Ui/js/model/messages'
], function (ko, $, Component, placeOrderAction, Messages) {
  'use strict';

  var canMakeApplePay = ko.observable(false);
  return Component.extend({
    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-applepay',
      creditCardToken: null,
      creditCardType: 'cb',
      instanceApplePay: null,
      eci: window.checkoutConfig.payment.hiPayFullservice.defaultEci,
      afterPlaceOrderUrl:
        window.checkoutConfig.payment.hiPayFullservice.afterPlaceOrderUrl
          .hipay_applepay,
      amount: window.checkoutConfig.totalsData.base_grand_total,
      currency: window.checkoutConfig.totalsData.base_currency_code,
      env: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.env
        : 'stage',
      apiUsernameTokenJs: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.apiUsernameTokenJs
        : '',
      apiPasswordTokenJs: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.apiPasswordTokenJs
        : '',
      displayName: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.display_name
        : '',
      buttonType: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.button_type
        : 'plain',
      buttonColour: window.checkoutConfig.payment.hipay_applepay
        ? window.checkoutConfig.payment.hipay_applepay.button_colour
        : 'black',
      locale:
        window.checkoutConfig.payment.hiPayFullservice &&
        window.checkoutConfig.payment.hiPayFullservice.locale
          ? window.checkoutConfig.payment.hiPayFullservice.locale.hipay_applepay
          : 'en_us'
    },

    placeOrderHandler: null,
    validateHandler: null,

    initialize: function () {
      var self = this;
      self._super();

      var hipaySdk = new HiPay({
        username: self.apiUsernameTokenJs,
        password: self.apiPasswordTokenJs,
        environment: 'custom',
        // environment: self.env,
        lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en',
        custom_urls: {
          hipay_api: 'https://release-1-1-0-ibkxm2b6nq-ew.a.run.app'
        }
      });

      const total = {
        label: self.displayName,
        amount: Number(self.amount).toFixed(2)
      };

      const request = {
        countryCode:
          self.locale.length > 3 ? self.locale.substr(3).toUpperCase() : 'US',
        currencyCode: self.currency,
        total: total
      };

      const configApplePay = {
        displayName: self.displayName,
        request: request,
        selector: 'hipay-apple-pay-button',
        applePayStyle: {
          type: self.buttonType,
          color: self.buttonColour
        }
      };

      var count = 0;
      var checkApplePay = setInterval(function () {
        if ($(`#${configApplePay.selector}`).length) {
          clearInterval(checkApplePay);
          self.instanceApplePay = hipaySdk.create(
            'paymentRequestButton',
            configApplePay
          );

          if (self.instanceApplePay) {
            self.instanceApplePay.on('paymentAuthorized', function (
              tokenHipay
            ) {
              self.creditCardToken(tokenHipay.token);
              self.creditCardType(
                tokenHipay.brand.toLowerCase().replace(/ /g, '-') ||
                  self.creditCardType
              );

              var deferred = $.Deferred();
              $.when(placeOrderAction(self.getData(), new Messages()))
                .fail(function (response) {
                  deferred.reject(Error(response));
                  self.instanceApplePay.completePaymentWithFailure();
                })
                .done(function () {
                  deferred.resolve(true);
                  self.instanceApplePay.completePaymentWithSuccess();
                  $.mage.redirect(self.afterPlaceOrderUrl);
                });
              $('body').loader('hide');
            });
          }
        } else {
          count++;
          if (count > 10) {
            clearInterval(checkApplePay);
            $('#payment-method-applepay').remove();
          }
        }
      }, 500);
    },
    isApplePayAllowed: function () {
      if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
        canMakeApplePay(true);
        return true;
      }
    },

    /**
     * @param {Function} handler
     */
    setPlaceOrderHandler: function (handler) {
      this.placeOrderHandler = handler;
    },

    /**
     * @param {Function} handler
     */
    setValidateHandler: function (handler) {
      this.validateHandler = handler;
    },

    initObservable: function () {
      this._super().observe(['creditCardToken', 'creditCardType', 'eci']);

      return this;
    },

    /**
     * @returns {Boolean}
     */
    isShowLegend: function () {
      return true;
    },
    context: function () {
      return this;
    },
    /**
     * @override
     */
    getCode: function () {
      return 'hipay_applepay';
    },
    getData: function () {
      return {
        method: this.item.method,
        additional_data: {
          card_token: this.creditCardToken(),
          eci: this.eci(),
          cc_type: this.creditCardType()
        }
      };
    }
  });
});
