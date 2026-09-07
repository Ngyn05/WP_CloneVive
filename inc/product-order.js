(function () {
  'use strict';
  var config = window.themeViveProductOrder || {};
  var products = config.products || {};
  var modal;

  function createModal() {
    modal = document.createElement('div');
    modal.className = 'theme-vive-order-modal';
    modal.hidden = true;
    modal.innerHTML = '<div class="theme-vive-order-dialog" role="dialog" aria-modal="true" aria-labelledby="theme-vive-order-title">' +
      '<button class="theme-vive-order-close" type="button" aria-label="Đóng">&times;</button>' +
      '<h2 id="theme-vive-order-title">Thông tin đặt hàng</h2>' +
      '<div class="theme-vive-order-product"><img alt=""><div><h3></h3><p></p><div class="theme-vive-order-product-meta"></div></div></div>' +
      '<div class="theme-vive-order-notice"><span aria-hidden="true">i</span><p><strong>Vui lòng điền số điện thoại thật chính xác.</strong> Nhân viên VIVE Việt Nam sẽ liên hệ xác nhận đơn hàng và tư vấn hình thức nhận hàng thuận tiện nhất.</p></div>' +
      '<form><input type="hidden" name="action" value="theme_vive_submit_order"><input type="hidden" name="nonce"><input type="hidden" name="product_id"><input type="hidden" name="product_slug">' +
      '<label>Họ và tên</label><input name="name" autocomplete="name" placeholder="Nguyễn Văn A">' +
      '<label>Số điện thoại *</label><input name="phone" required inputmode="tel" autocomplete="tel" placeholder="0912345678">' +
      '<label>Email</label><input name="email" type="email" autocomplete="email" placeholder="email@example.com">' +
      '<label>Số lượng</label><input class="theme-vive-order-quantity" name="quantity" type="number" value="1" min="1" max="99">' +
      '<label>Địa chỉ nhận hàng</label><textarea name="address" rows="2" autocomplete="street-address" placeholder="Địa chỉ giao hàng..."></textarea>' +
      '<label>Ghi chú đơn hàng</label><textarea name="note" rows="3" placeholder="Yêu cầu thêm về thời gian giao hàng, tư vấn..."></textarea>' +
      '<button class="theme-vive-order-submit" type="submit">Gửi đơn hàng</button><p class="theme-vive-order-message" aria-live="polite"></p></form></div>';
    document.body.appendChild(modal);
    modal.querySelector('[name="nonce"]').value = config.nonce || '';
    modal.querySelector('.theme-vive-order-close').onclick = closeModal;
    modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
    modal.querySelector('form').addEventListener('submit', submitOrder);
  }

  function getSelectedVariants() {
    var selected = [];
    document.querySelectorAll('.product-variant select, .product-variant input[type="radio"]:checked').forEach(function(el) {
      var name = el.name || el.id || '';
      var val = el.value || '';
      // Shopify uses "Default Title" as an internal placeholder when a
      // product has no real variants. It is not useful order information.
      var normalizedVal = String(val).trim().toLowerCase();
      if (val && normalizedVal !== 'default title') {
        var label = el.closest('.product-variant') ? el.closest('.product-variant').querySelector('.product-variant__name') : null;
        var labelText = label ? label.textContent.trim() : 'Tùy chọn';
        selected.push(labelText + ': ' + val);
      }
    });
    return selected.join(' | ');
  }

  function getPageQuantity() {
    var qtyInput = document.querySelector('.product__quantity, [name="quantity"], #qty-template--27611584627005__main');
    if (qtyInput) {
      var val = parseInt(qtyInput.value, 10);
      if (!isNaN(val) && val > 0) return val;
    }
    return 1;
  }

  function openModal(product) {
    if (!modal) createModal();
    var summary = modal.querySelector('.theme-vive-order-product');
    summary.querySelector('img').src = product.image || '';
    summary.querySelector('img').alt = product.name || '';
    summary.querySelector('h3').textContent = product.name || '';
    summary.querySelector('p').textContent = product.short || product.description || '';
    
    var variantDetails = getSelectedVariants();
    var metaText = [product.sku ? 'SKU: ' + product.sku : '', variantDetails].filter(Boolean).join(' · ');
    summary.querySelector('.theme-vive-order-product-meta').textContent = metaText;
    
    modal.dataset.productId = product.id || '';
    modal.dataset.productSlug = product.slug || '';
    modal.querySelector('[name="product_id"]').value = product.id || '';
    modal.querySelector('[name="product_slug"]').value = product.slug || '';
    modal.querySelector('[name="quantity"]').value = getPageQuantity();
    if (variantDetails) {
      modal.querySelector('[name="note"]').value = 'Biến thể đã chọn: ' + variantDetails;
    }
    modal.querySelector('.theme-vive-order-message').textContent = '';
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    modal.querySelector('[name="name"]').focus();
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  function submitOrder(event) {
    event.preventDefault();
    var form = event.currentTarget;
    var button = form.querySelector('.theme-vive-order-submit');
    var message = form.querySelector('.theme-vive-order-message');
    button.disabled = true;
    button.textContent = 'Đang gửi…';
    message.className = 'theme-vive-order-message';
    fetch(config.endpoint || '/wp-admin/admin-post.php', { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        message.textContent = result.data && result.data.message ? result.data.message : 'Đã gửi yêu cầu.';
        if (!result.success) message.classList.add('is-error');
        if (result.success) {
          form.reset();
          form.querySelector('[name="nonce"]').value = config.nonce || '';
          form.querySelector('[name="product_id"]').value = modal.dataset.productId || '';
          form.querySelector('[name="product_slug"]').value = modal.dataset.productSlug || '';
        }
      })
      .catch(function () { message.textContent = 'Không thể gửi yêu cầu. Vui lòng thử lại.'; message.classList.add('is-error'); })
      .finally(function () { button.disabled = false; button.textContent = 'Gửi đơn hàng'; });
  }

  function handleCallbackForm(form) {
    if (form._viveBound) return;
    form._viveBound = true;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('button[type="submit"]');
      var msg = form.querySelector('.vive-woo-product__callback-message');
      if (btn) btn.disabled = true;
      if (msg) {
        msg.className = 'vive-woo-product__callback-message';
        msg.textContent = 'Đang gửi...';
      }
      fetch(form.getAttribute('action') || '/wp-admin/admin-post.php', {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin'
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (msg) {
          msg.textContent = res.data && res.data.message ? res.data.message : 'Đã gửi thông tin tư vấn thành công.';
          if (!res.success) msg.classList.add('is-error');
        }
        if (res.success) {
          var phoneInput = form.querySelector('input[name="phone"]');
          if (phoneInput) phoneInput.value = '';
        }
      })
      .catch(function () {
        if (msg) {
          msg.textContent = 'Không thể gửi yêu cầu lúc này. Vui lòng liên hệ trực tiếp Hotline.';
          msg.classList.add('is-error');
        }
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
    });
  }

  function render() {
    var renderedSlugs = {};
    document.querySelectorAll('[data-theme-vive-buy-slug]').forEach(function (button) {
      var slug = button.getAttribute('data-theme-vive-buy-slug');
      var product = products[slug];
      if (!product) {
        // Build fallback product object from page
        var pageTitle = document.querySelector('h1, .product-title, .title');
        var pageImg = document.querySelector('.product-gallery img, .card__image img, .lazy-image img');
        var pageShort = document.querySelector('.product__description, .llm-summary, .product-sub-title');
        product = {
          id: 0,
          slug: slug,
          name: pageTitle ? pageTitle.textContent.trim() : slug,
          image: pageImg ? (pageImg.currentSrc || pageImg.src) : '',
          short: pageShort ? pageShort.textContent.trim().slice(0, 160) : '',
          purchase_url: '#dat-hang'
        };
      }
      button.setAttribute('aria-label', 'Mua ngay ' + product.name);
      if (button.getAttribute('data-theme-vive-buy-mode') === 'popup' || button.classList.contains('vive-woo-product__action--buy')) {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          openModal(product);
        });
      } else {
        button.setAttribute('href', product.purchase_url || '#dat-hang');
      }
    });

    document.querySelectorAll('form[data-vive-callback-form], .vive-woo-product__callback').forEach(handleCallbackForm);

    // Quantity stepper
    document.querySelectorAll('.product-quantity, product-quantity').forEach(function (holder) {
      var input = holder.querySelector('input.product__quantity, [name="quantity"]');
      var minus = holder.querySelector('.qty-minus, [aria-label*="Decrease"], [aria-label*="Giảm"]');
      var plus = holder.querySelector('.qty-plus, [aria-label*="Increase"], [aria-label*="Tăng"]');
      if (input && minus && !minus._bound) {
        minus._bound = true;
        minus.addEventListener('click', function (e) {
          e.preventDefault();
          var val = parseInt(input.value, 10) || 1;
          if (val > 1) { input.value = val - 1; input.dispatchEvent(new Event('change', { bubbles: true })); }
        });
      }
      if (input && plus && !plus._bound) {
        plus._bound = true;
        plus.addEventListener('click', function (e) {
          e.preventDefault();
          var val = parseInt(input.value, 10) || 1;
          input.value = val + 1;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      }
    });

    // Swatch click feedback
    document.querySelectorAll('.product-variant__item--color input[type="radio"]').forEach(function (radio) {
      if (radio._swatchBound) return;
      radio._swatchBound = true;
      radio.addEventListener('change', function () {
        var container = radio.closest('.product-variant');
        if (container) {
          var label = container.querySelector('.product-variant__item-text-label');
          if (label) label.textContent = radio.value;
        }
      });
    });

    document.querySelectorAll('.js-slug-product[data-slug]').forEach(function (marker) {
      renderedSlugs[marker.getAttribute('data-slug')] = true;
      var product = products[marker.getAttribute('data-slug')];
      var card = marker.closest('.product-item');
      var group = marker.closest('.productGroup');
      if (!product) {
        (card && group && group.querySelectorAll('.js-slug-product').length > 1 ? card : group || card || marker).remove();
        return;
      }
      var scope = card && card.querySelector('.product-name-title') ? card : group || card;
      if (!scope) return;
      var title = scope.querySelector('.product-name-title,.text-category-title');
      var shortText = scope.querySelector('.text-sub');
      var description = scope.querySelector('.product-name-text,.text-des');
      if (title) title.textContent = product.name;
      if (shortText) shortText.textContent = product.short;
      if (description) description.textContent = product.description;
      scope.querySelectorAll('img').forEach(function (img) { if (product.image) { img.src = product.image; img.dataset.src = product.image; img.alt = product.name; } });
      var actions = scope.querySelector('.theme-vive-product-actions');
      if (!actions) {
        actions = document.createElement('div');
        actions.className = 'theme-vive-product-actions';
        actions.innerHTML = '<a class="theme-vive-detail-button">Xem giới thiệu sản phẩm</a><a class="theme-vive-buy-button">Mua ngay</a>';
        (scope.querySelector('.product-name') || scope.querySelector('.flex-item:not(.flex-item__image)') || scope).appendChild(actions);
      }
      var detail = actions.querySelector('.theme-vive-detail-button');
      var buy = actions.querySelector('.theme-vive-buy-button');
      detail.href = product.url;
      detail.setAttribute('aria-label', 'Xem giới thiệu sản phẩm ' + product.name);
      buy.setAttribute('aria-label', 'Mua ngay ' + product.name);
      buy.href = product.purchase_url;
    });
  }

  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', render) : render();
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeModal(); });
}());
