'use strict';

//
// Digital River
//
/* global readCookie, createCookie */
function CartProvider() {
    // init
    var countryCode = jQuery('body').data('site'),
        path = window.location.pathname.split('/'),
        base_url = void 0,
        store_url = void 0,
        apiKey = void 0,
        themeID = '',
        tokenKeyName = 'dr-htc-token-' + countryCode,
        regionLocale = $('header').data('region-locale'),
        regionCurrency = $('header').data('region-currency'),
        apiText = void 0;
    // US and CA countries
    if (countryCode === 'us' || countryCode === 'ca' || countryCode === 'ca-fr') {
        base_url = 'https://api.digitalriver.com/v1/shoppers/';
        store_url = 'https://store.us.htc.com/store/htcus/SessionToken';
        apiKey = 'UnZqOVp4laTfF1l71GqnzXR5e34MUG3M';
    } else if (countryCode === 'au') {
        // APAC countries: AU
        base_url = 'https://api.digitalriver.com/v1/shoppers/';
        store_url = 'https://shop.au.htc.com/store/htcapac/SessionToken';
        apiKey = 'cede30a1de9c44f6b9183ff9be9ae309';
        themeID = 40354300;
    } else if (countryCode === 'hk-en' || countryCode === 'hk-tc' || countryCode === 'sea') {
        base_url = 'https://api.digitalriver.com/v1/shoppers/';
        store_url = 'https://shop.apac.htc.com/store/htcapac/SessionToken';
        apiKey = 'e1a0f189b4f44b998fc8e47d515ab418';
        themeID = 40354300;
    } else {
        // active EMEA countries : UK, FR, DE, IE
        base_url = 'https://api.digitalriver.com/v1/shoppers/';
        store_url = 'https://shop.eu.htc.com/store/htcemea/SessionToken';
        apiKey = '864d71cdaffb4e03a43a535c23d3292a';
    } // END: checking regions
    if (typeof regionLocale !== 'undefined' && regionLocale !== undefined && regionLocale !== '' && typeof regionCurrency !== 'undefined' && regionCurrency !== undefined && regionCurrency !== '') {
        apiText = 'locale=' + regionLocale + '&currency=' + regionCurrency + '&';
    } else {
        apiText = '';
    }
    // Get Anonymous Shopping Token and Store in cookie
    this.createAnonToken = function (callback) {
        // console.log('tokenKeyName', tokenKeyName);
        var token = readCookie(tokenKeyName);
        if (token === null || token === '') {
            $.ajax({
                url: store_url + '?' + apiText + 'format=json&method=get&ThemeID=' + themeID + '&apiKey=' + apiKey,
                dataType: 'jsonp',
                success: function success(results) {
                    createCookie(tokenKeyName, results.access_token, 1);
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function error(xhr, textStatus, errorThrown) {
                    // console.log('createAnonToken error');
                }
            });
        } else if (typeof callback === 'function') {
            callback();
        }
    };
    // Add a product to cart
    this.addToCart = function (productId, quantity, callback) {
        this.createAnonToken(function () {
            var accessToken = readCookie(tokenKeyName);
            $.ajax({
                url: base_url + 'me/carts/active/line-items?format=json&method=post&productId=' + productId + '&quantity=' + quantity + '&token=' + accessToken,
                dataType: 'jsonp',
                tryCount: 0,
                retryLimit: 3,
                success: function success(results) {
                    // set cart active cookie
                    createCookie('isCartActive', 'true', 1);
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function error(xhr, textStatus, errorThrown) {}
            });
        });
    };
    // Remove Item from Cart
    this.removeItemFromCart = function (lineItemId, callback) {
        this.createAnonToken(function () {
            var accessToken = readCookie(tokenKeyName);
            $.ajax({
                url: base_url + 'me/carts/active/line-items/' + lineItemId + '?format=json&method=delete&token=' + accessToken,
                dataType: 'jsonp',
                tryCount: 0,
                retryLimit: 3,
                success: function success(results) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function error(xhr, textStatus, errorThrown) {}
            });
        });
    };
    // Get cart
    this.getCart = function (callback) {
        // check if cart is active
        var isCartActive = readCookie('isCartActive');
        // var isCartActive = 'true';
        if (isCartActive !== null && isCartActive === 'true') {
            this.createAnonToken(function () {
                var accessToken = readCookie(tokenKeyName);
                // console.log('1.getCart=' + base_url + 'me/carts/active?format=json&expand=lineitems.lineItem.product.sku&method=get&token=' + accessToken);
                $.ajax({
                    url: base_url + 'me/carts/active?format=json&expand=lineitems.lineItem.product.sku&method=get&token=' + accessToken,
                    dataType: 'jsonp',
                    tryCount: 0,
                    retryLimit: 3,
                    success: function success(data) {
                        if (data && data.errors) {
                            createCookie('tokenKeyName', '', -1);
                        }
                        var dataItems = [];
                        if (data.cart.lineItems.lineItem) {
                            dataItems = data.cart.lineItems.lineItem;
                        }
                        var cartItems = [];
                        $.each(dataItems, function (index, dataItem) {
                            var item = {
                                id: dataItem.id,
                                name: dataItem.product.displayName,
                                image: dataItem.product.thumbnailImage,
                                quantity: dataItem.quantity,
                                price: dataItem.pricing.formattedListPriceWithQuantity,
                                saleprice: dataItem.pricing.formattedSalePriceWithQuantity
                            };
                            cartItems[index] = item;
                        });
                        var cart = {
                            items: cartItems,
                            totalItems: data.cart.totalItemsInCart,
                            subTotal: data.cart.pricing.formattedSubtotal,
                            checkoutURL: base_url + 'me/carts/active/web-checkout?token=' + accessToken + '&ThemeID=' + themeID
                        };
                        if (typeof callback === 'function') {
                            callback(cart);
                        }
                    },
                    error: function error(xhr, textStatus, errorThrown) {}
                });
            });
        }
    };
    // Checkout
    this.checkout = function () {
        this.getCart(function (cart) {
            window.location = cart.checkoutURL;
        });
    };
    // Get product info
    this.getProduct = function (productSKU, callback) {
        this.createAnonToken(function () {
            // accessToken = readCookie(tokenKeyName); //not used
            $.ajax({
                url: base_url + 'me/products/' + productSKU + '?' + apiText + 'format=json&method=get&apiKey=' + apiKey + '&expand=pricing,inventoryStatus',
                dataType: 'jsonp',
                tryCount: 0,
                retryLimit: 3,
                success: function success(data) {
                    var itemStatus = {};
                    if (data.product) {
                        itemStatus = {
                            price: data.product.pricing.formattedListPrice,
                            saleprice: data.product.pricing.formattedSalePriceWithQuantity,
                            overrideOutOfStock: false,
                            availableStock: '',
                            isAvailable: data.product.inventoryStatus.productIsInStock,
                            isBackOrder: data.product.inventoryStatus.productIsAllowsBackorders,
                            isPreOrder: data.product.inventoryStatus.productIsAllowsBackorders
                        };
                    }
                    if (typeof callback === 'function') {
                        callback(itemStatus);
                    }
                },
                error: function error(xhr, textStatus, errorThrown) {
                    // console.log('getProduct error');
                }
            });
        });
    };
}
// init
window.cartProvider = new CartProvider();