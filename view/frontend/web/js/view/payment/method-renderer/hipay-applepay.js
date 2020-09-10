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
  'mage/storage'
], function (ko, $, Component, placeOrderAction, storage) {
  'use strict';

  var canMakeApplePay = ko.observable(false);
  return Component.extend({
    defaults: {
      template: 'HiPay_FullserviceMagento/payment/hipay-applepay',
      creditCardToken: null,
      creditCardType: 'cb',
      instanceApplePay: null,
      eci: window.checkoutConfig.payment.hiPayFullservice.defaultEci,
      placeOrderStatusUrl:
        window.checkoutConfig.payment.hiPayFullservice.placeOrderStatusUrl
          .hipay_applepay,
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
      this._super();
    },
    isApplePayAllowed: function () {
      var canMakePayment = false;
      try {
        canMakePayment = window.ApplePaySession.canMakePayments();
      } catch (e) {
        return false;
      }
      if (canMakePayment) {
        canMakeApplePay(true);

        var self = this;

        var hipaySdk = new HiPay({
          username: self.apiUsernameTokenJs,
          password: self.apiPasswordTokenJs,
          environment: self.env,
          lang: self.locale.length > 2 ? self.locale.substr(0, 2) : 'en'
        });

        self.instanceApplePay = hipaySdk.create('paymentRequestButton', {
          displayName: self.displayName,
          request: {
            countryCode:
              self.locale.length > 3
                ? self.locale.substr(3).toUpperCase()
                : 'US',
            currencyCode: self.currency,
            total: {
              label: self.displayName,
              amount: Number(self.amount).toFixed(2)
            }
          },
          selector: 'hipay-apple-pay-button',
          applePayStyle: {
            type: self.buttonType,
            color: self.buttonColour
          }
        });

        if (self.instanceApplePay) {
          self.instanceApplePay.on('paymentAuthorized', function (token) {
            self.paymentAuthorized(self, token);
          });
        }
        return true;
      } else {
        return false;
      }
    },

    paymentAuthorized: function (self, tokenHipay) {
      var body = $('body');
      self.creditCardToken(tokenHipay.token);
      self.creditCardType(
        tokenHipay.brand.toLowerCase().replace(/ /g, '-') || self.creditCardType
      );

      var deferred = $.Deferred();
      $.when(placeOrderAction(self.getData(), self.messageContainer))
        .fail(function (error) {
          deferred.reject(Error(error));
          self.instanceApplePay.completePaymentWithFailure();
        })
        .done(function () {
          deferred.resolve(true);
          storage
            .get(self.placeOrderStatusUrl)
            .done(function (response) {
              if (response && response.statusOK === true) {
                self.instanceApplePay.completePaymentWithSuccess();
              } else {
                self.instanceApplePay.completePaymentWithFailure();
              }
              $.mage.redirect(response.redirectUrl);
            })
            .fail(function () {
              self.instanceApplePay.completePaymentWithFailure();
              $.mage.redirect(self.afterPlaceOrderUrl);
            });
        });
      body.loader('hide');
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
