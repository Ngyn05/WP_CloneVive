
    (function() {
      var preconnectOrigins = ["https://cdn.shopify.com","https://extensions.shopifycdn.com"];
      var scripts = ["/cdn/shopifycloud/checkout-web/assets/c1/polyfills-legacy.IaGeyDRJ.js","/cdn/shopifycloud/checkout-web/assets/c1/app-legacy.aFrbZzvw.js","/cdn/shopifycloud/checkout-web/assets/c1/esnext-vendor-legacy.CerKYpGQ.js","/cdn/shopifycloud/checkout-web/assets/c1/context-browser-legacy.BmgWSm-L.js","/cdn/shopifycloud/checkout-web/assets/c1/stopwatch-legacy.DUHY9kN4.js","/cdn/shopifycloud/checkout-web/assets/c1/receipt-mapper-load-recovery-legacy.CBUhyEQF.js","/cdn/shopifycloud/checkout-web/assets/c1/receipt-eager-mappers-legacy.BeHKAToW.js","/cdn/shopifycloud/checkout-web/assets/c1/helpers-setAddressErrors-legacy.BsXkYSHV.js","/cdn/shopifycloud/checkout-web/assets/c1/shop-pay-normalizeBuyerDetails-legacy.DYRi9LR3.js","/cdn/shopifycloud/checkout-web/assets/c1/addresses-is-address-empty-legacy.CXZ9j_xq.js","/cdn/shopifycloud/checkout-web/assets/c1/sections-shared-legacy.DYy4Ii_X.js","/cdn/shopifycloud/checkout-web/assets/c1/consent-manager-shared-legacy.CVz09pXL.js","/cdn/shopifycloud/checkout-web/assets/c1/error-logger-report-graphql-error-legacy.Bdw4oHh-.js","/cdn/shopifycloud/checkout-web/assets/c1/helpers-derivations-legacy.BmvFy5Wn.js","/cdn/shopifycloud/checkout-web/assets/c1/mappers-checkout-policy-legacy.DTFlNRsU.js","/cdn/shopifycloud/checkout-web/assets/c1/utilities-browser-legacy.DAY4iF32.js","/cdn/shopifycloud/checkout-web/assets/c1/hydrate-legacy.Dm9jTwpD.js","/cdn/shopifycloud/checkout-web/assets/c1/shop-pay-installments-monorail-legacy.DwPsGhj8.js","/cdn/shopifycloud/checkout-web/assets/c1/locale-en-legacy.DGryzrlI.js","/cdn/shopifycloud/checkout-web/assets/c1/OnePage-legacy.g6XmzVAC.js","/cdn/shopifycloud/checkout-web/assets/c1/components-DeliveryTransition-legacy.DnYQFL1D.js","/cdn/shopifycloud/checkout-web/assets/c1/useShopPayButtonClassName-legacy.BJyxOmZG.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useUnauthenticatedErrorModal-legacy.apogVEvG.js","/cdn/shopifycloud/checkout-web/assets/c1/cross-border-hooks-legacy.BK30OsXv.js","/cdn/shopifycloud/checkout-web/assets/c1/crypto-constants-legacy.CF5PjvLV.js","/cdn/shopifycloud/checkout-web/assets/c1/ChangeCompanyLocationLink-legacy.BG-OWu5J.js","/cdn/shopifycloud/checkout-web/assets/c1/BillingAddressForm-legacy.R8eNbFFM.js","/cdn/shopifycloud/checkout-web/assets/c1/PhoneField-legacy.D05-7D0m.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useSuppressShopPayModalOnLoad-legacy.CpNHWhSY.js","/cdn/shopifycloud/checkout-web/assets/c1/components-RedirectionNotice.module-legacy.BhnFfWQv.js","/cdn/shopifycloud/checkout-web/assets/c1/Popover-legacy.CqLf474k.js","/cdn/shopifycloud/checkout-web/assets/c1/Choice-legacy.CPUl5NMR.js","/cdn/shopifycloud/checkout-web/assets/c1/shop-pay-installments-types-legacy.BDBbOrLW.js","/cdn/shopifycloud/checkout-web/assets/c1/Checkbox-legacy.B17ae-HI.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useForceShopPayUrl-legacy.GdV4hXAn.js","/cdn/shopifycloud/checkout-web/assets/c1/shipping-methods-grouping-legacy.CEUFqRlb.js","/cdn/shopifycloud/checkout-web/assets/c1/utilities-previous-legacy.C3t3ShGv.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useShowShopPayOptin-legacy.LJSp1JD8.js","/cdn/shopifycloud/checkout-web/assets/c1/ShopPayLogo-legacy.DgEJaj9I.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useWalletsTimeout-legacy.B-_VvOvn.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-usePostPurchase-legacy.CLDjtZoQ.js","/cdn/shopifycloud/checkout-web/assets/c1/graphql-PaymentSessionMutation-legacy.8CD31F7p.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useWalletsMonorailTrack-legacy.CCaFEA4C.js","/cdn/shopifycloud/checkout-web/assets/c1/MarketsProDisclaimer-legacy.DM-2s_Lh.js","/cdn/shopifycloud/checkout-web/assets/c1/IncentiveBadge-legacy.CL-gKlps.js","/cdn/shopifycloud/checkout-web/assets/c1/AutocompleteField-hooks-legacy.CDKFyZrM.js","/cdn/shopifycloud/checkout-web/assets/c1/PendingShipping-legacy.dBIy-Ugd.js","/cdn/shopifycloud/checkout-web/assets/c1/useAddressMutationsWithNegotiation-legacy.DFsHXXLx.js","/cdn/shopifycloud/checkout-web/assets/c1/PaymentIcon-legacy.CnnsFRQA.js","/cdn/shopifycloud/checkout-web/assets/c1/PaymentLine-legacy.DxYgkcrh.js","/cdn/shopifycloud/checkout-web/assets/c1/Theme-ThemeOverride-legacy.BDtwWgms.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useUpdateCheckoutAddress-legacy.BnMY4JjA.js","/cdn/shopifycloud/checkout-web/assets/c1/payment-usePaymentExemptionReason-legacy.CKNSJx0Q.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useShopPayProgressIntercepts-legacy.BEdh0dI9.js","/cdn/shopifycloud/checkout-web/assets/c1/Section-legacy.DJ7VasFv.js","/cdn/shopifycloud/checkout-web/assets/c1/Section-SectionStyleOverride-legacy.DB79QS2k.js","/cdn/shopifycloud/checkout-web/assets/c1/PaymentErrorBanner-legacy.DsxYz665.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useGeneralPaymentErrorMessage-legacy.aGlF9CBp.js","/cdn/shopifycloud/checkout-web/assets/c1/StickyPayButton-StickyPayButton.module-legacy.BVaCojDb.js","/cdn/shopifycloud/checkout-web/assets/c1/PayButton-helpers-legacy.VrhSkwMc.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-payment-button-legacy.EfW-XcqO.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-usePreselectSpi-legacy.BrqBvnNX.js","/cdn/shopifycloud/checkout-web/assets/c1/Switch-legacy.CwlFk5CE.js","/cdn/shopifycloud/checkout-web/assets/c1/hooks-useAvailableShopPromotionDiscounts-legacy.hWBgydJl.js","/cdn/shopifycloud/checkout-web/assets/c1/checkout-as-guest-amazon-pay-legacy.E7aVWimp.js","/cdn/shopifycloud/checkout-web/assets/c1/Middot-legacy.YKx6XEqi.js","/cdn/shopifycloud/checkout-web/assets/c1/EstimatedDeliveryContent-legacy.D28O2YD1.js","/cdn/shopifycloud/checkout-web/assets/c1/ShippingMethodRateLabel-legacy.CZ63nF7p.js","/cdn/shopifycloud/checkout-web/assets/c1/shipping-methods-consolidated-included-legacy.DWTtaCjr.js","/cdn/shopifycloud/checkout-web/assets/c1/ShippingLines-legacy.CejaETgX.js","/cdn/shopifycloud/checkout-web/assets/c1/ShipmentBreakdown-legacy.uOstuCGk.js","/cdn/shopifycloud/checkout-web/assets/c1/MerchandiseModal-legacy.BlZO3jRC.js","/cdn/shopifycloud/checkout-web/assets/c1/ShippingMethodSelector-legacy.Bv5665D2.js","/cdn/shopifycloud/checkout-web/assets/c1/TextArea-legacy.KhxAxl5y.js","/cdn/shopifycloud/checkout-web/assets/c1/SubscriptionPriceBreakdown-legacy.CUevQLoJ.js","/cdn/shopifycloud/checkout-web/assets/c1/StockProblems-StockProblemsLineItemList-legacy.BgWquqsH.js","/cdn/shopifycloud/checkout-web/assets/c1/extensibility-browser-engine-legacy.BOlcm7OF.js","/cdn/shopifycloud/checkout-web/assets/c1/utilities-extension-execution-errors-legacy.BN5RApCD.js","/cdn/shopifycloud/checkout-web/assets/c1/extensions-rpc-legacy.BUeWMB46.js","/cdn/shopifycloud/checkout-web/assets/c1/component-RuntimeExtension-legacy.Di08ALWe.js","/cdn/shopifycloud/checkout-web/assets/c1/AnnouncementRuntimeExtensions-legacy.DJHXQjwd.js","/cdn/shopifycloud/checkout-web/assets/c1/QRCode-legacy.DWuBD2KS.js","/cdn/shopifycloud/checkout-web/assets/c1/utilities-dates-legacy.DCA0mtXE.js","/cdn/shopifycloud/checkout-web/assets/c1/NumberField-legacy.CUUbFoRp.js","/cdn/shopifycloud/checkout-web/assets/c1/extensions-remote-dom-legacy.ak4DttL_.js","/cdn/shopifycloud/checkout-web/assets/c1/EmailField-legacy.DwmDC9OG.js","/cdn/shopifycloud/checkout-web/assets/c1/Sheet-legacy.DOpWue63.js","/cdn/shopifycloud/checkout-web/assets/c1/useShopPaySessionTokenStorage-legacy.BVaAZnt9.js","/cdn/shopifycloud/checkout-web/assets/c1/extension-targets-rendering-extension-targets-legacy.Cjqt8JcG.js","/cdn/shopifycloud/checkout-web/assets/c1/dist-v4-legacy.hxLzMo8h.js","/cdn/shopifycloud/checkout-web/assets/c1/ExtensionsInner-legacy.DfGkljx9.js","/cdn/shopifycloud/checkout-web/assets/c1/adapter-host-legacy.DJ6MA_cN.js","/cdn/shopifycloud/checkout-web/assets/c1/sandbox.yFnJdgO9.worker.js","/cdn/shopifycloud/checkout-web/assets/c1/sandbox-2025-07.6kKHlBw6.worker.js","https://extensions.shopifycdn.com/shopifycloud/checkout-web/assets/c1/polyfills-entry-legacy.BhbDnkUj.worker.js"];
      var styles = [];
      var fontPreconnectUrls = [];
      var fontPrefetchUrls = [];
      var imgPrefetchUrls = ["https://cdn.shopify.com/s/files/1/0926/0489/1453/files/VIVE_Logo_Digital_Blue-DarkBlue_RGB_x320.png?v=1751965126"];

      function preconnect(url, callback) {
        var link = document.createElement('link');
        link.rel = 'dns-prefetch preconnect';
        link.href = url;
        link.crossOrigin = '';
        link.onload = link.onerror = callback;
        document.head.appendChild(link);
      }

      function preconnectAssets() {
        var resources = preconnectOrigins.concat(fontPreconnectUrls);
        var index = 0;
        (function next() {
          var res = resources[index++];
          if (res) preconnect(res, next);
        })();
      }

      function prefetch(url, as, callback) {
        var link = document.createElement('link');
        if (link.relList.supports('prefetch')) {
          link.rel = 'prefetch';
          link.fetchPriority = 'low';
          link.as = as;
          if (as === 'font') link.type = 'font/woff2';
          link.href = url;
          link.crossOrigin = '';
          link.onload = link.onerror = callback;
          document.head.appendChild(link);
        } else {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', url, true);
          xhr.onloadend = callback;
          xhr.send();
        }
      }

      function prefetchAssets() {
        var resources = [].concat(
          scripts.map(function(url) { return [url, 'script']; }),
          styles.map(function(url) { return [url, 'style']; }),
          fontPrefetchUrls.map(function(url) { return [url, 'font']; }),
          imgPrefetchUrls.map(function(url) { return [url, 'image']; })
        );
        var index = 0;
        function run() {
          var res = resources[index++];
          if (res) prefetch(res[0], res[1], next);
        }
        var next = (self.requestIdleCallback || setTimeout).bind(self, run);
        next();
      }

      function onLoaded() {
        try {
          if (parseFloat(navigator.connection.effectiveType) > 2 && !navigator.connection.saveData) {
            preconnectAssets();
            prefetchAssets();
          }
        } catch (e) {}
      }

      if (document.readyState === 'complete') {
        onLoaded();
      } else {
        addEventListener('load', onLoaded);
      }
    })();
  