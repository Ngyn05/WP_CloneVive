
/* === Interactive Client-side Collection Filter Engine === */
(function() {
  function initViveFilters() {
    const grid = document.getElementById('main-collection-product-grid');
    if (!grid) return;
    
    const items = Array.from(grid.querySelectorAll('.product-item'));
    if (!items.length) return;

    // 1. Hide duplicate mobile drawer on desktop
    const mobileDrawer = document.getElementById('site-filters-sidebar');
    if (mobileDrawer && window.innerWidth >= 1024) {
      mobileDrawer.style.display = 'none';
    }

    // 2. Extract metadata for all products in grid
    const productData = items.map((el, idx) => {
      const titleEl = el.querySelector('.product-item__title');
      const title = titleEl ? titleEl.textContent.trim() : '';
      const lowerTitle = title.toLowerCase();

      // Determine product type
      let type = 'Accessories';
      if (
        lowerTitle.includes('pin') ||
        lowerTitle.includes('bộ sạc') ||
        lowerTitle.includes('battery') ||
        lowerTitle.includes('charger') ||
        lowerTitle.includes('dock') ||
        lowerTitle.includes('giá đỡ pin') ||
        lowerTitle.includes('chia nguồn') ||
        lowerTitle.includes('power')
      ) {
        type = 'Batteries and chargers';
      } else if (
        lowerTitle.includes('flow') ||
        lowerTitle.includes('focus') ||
        lowerTitle.includes('xr elite') ||
        lowerTitle.includes('tai nghe') ||
        lowerTitle.includes('kính') ||
        lowerTitle.includes('headset') ||
        lowerTitle.includes('pro 2') ||
        lowerTitle.includes('pro eye')
      ) {
        type = 'Headsets';
      } else if (lowerTitle.includes('tracker') || lowerTitle.includes('theo dõi')) {
        type = 'Trackers';
      }

      // Extract price
      let price = 0;
      const priceEl = el.querySelector('.product-price--original, .product-price');
      if (priceEl) {
        const numStr = priceEl.textContent.replace(/[^0-9]/g, '');
        price = parseFloat(numStr) || 0;
      }

      return { el, title, type, price, index: idx };
    });

    // 3. Parse URL search parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const urlCategory = urlParams.get('filter.p.m.custom.vive_vr_headsets');
    const urlSort = urlParams.get('sort_by');

    if (urlCategory) {
      document.querySelectorAll(`input[name="filter.p.m.custom.vive_vr_headsets"]`).forEach(cb => {
        if (cb.value.toLowerCase() === urlCategory.toLowerCase()) {
          cb.checked = true;
        }
      });
    }

    if (urlSort) {
      document.querySelectorAll('#sidebar-SortBy, #drawer-SortBy').forEach(s => {
        s.value = urlSort;
      });
    }

    function applyFilters() {
      // Get all checked types
      const checkedTypes = [];
      document.querySelectorAll('input[name="filter.p.m.custom.vive_vr_headsets"]:checked').forEach(cb => {
        if (!checkedTypes.includes(cb.value)) {
          checkedTypes.push(cb.value);
        }
      });

      // Get sort value
      const sortSelect = document.getElementById('sidebar-SortBy') || document.getElementById('drawer-SortBy');
      const sortVal = sortSelect ? sortSelect.value : 'best-selling';

      let visibleCount = 0;

      productData.forEach(p => {
        let match = true;
        if (checkedTypes.length > 0) {
          if (!checkedTypes.includes(p.type)) {
            match = false;
          }
        }

        if (match) {
          p.el.style.display = '';
          visibleCount++;
        } else {
          p.el.style.display = 'none';
        }
      });

      // Sorting
      if (sortVal === 'price-ascending') {
        items.sort((a, b) => {
          const pa = productData.find(x => x.el === a).price;
          const pb = productData.find(x => x.el === b).price;
          return pa - pb;
        }).forEach(el => grid.appendChild(el));
      } else if (sortVal === 'price-descending') {
        items.sort((a, b) => {
          const pa = productData.find(x => x.el === a).price;
          const pb = productData.find(x => x.el === b).price;
          return pb - pa;
        }).forEach(el => grid.appendChild(el));
      } else if (sortVal === 'title-ascending') {
        items.sort((a, b) => {
          const ta = productData.find(x => x.el === a).title;
          const tb = productData.find(x => x.el === b).title;
          return ta.localeCompare(tb, 'vi');
        }).forEach(el => grid.appendChild(el));
      } else if (sortVal === 'title-descending') {
        items.sort((a, b) => {
          const ta = productData.find(x => x.el === a).title;
          const tb = productData.find(x => x.el === b).title;
          return tb.localeCompare(ta, 'vi');
        }).forEach(el => grid.appendChild(el));
      } else {
        items.sort((a, b) => {
          const ia = productData.find(x => x.el === a).index;
          const ib = productData.find(x => x.el === b).index;
          return ia - ib;
        }).forEach(el => grid.appendChild(el));
      }

      // Update count text
      const countEls = document.querySelectorAll('#CollectionProductCount');
      countEls.forEach(c => {
        c.textContent = `Hiển thị ${visibleCount} sản phẩm`;
      });

      // Show/remove empty message
      let emptyMsg = grid.querySelector('.no-products-found-msg');
      if (visibleCount === 0) {
        if (!emptyMsg) {
          emptyMsg = document.createElement('div');
          emptyMsg.className = 'no-products-found-msg';
          emptyMsg.style.cssText = 'grid-column: 1 / -1; padding: 60px 20px; text-align: center; font-size: 16px; color: #6b7280; width: 100%;';
          emptyMsg.textContent = 'Không tìm thấy sản phẩm phù hợp với bộ lọc.';
          grid.appendChild(emptyMsg);
        }
      } else if (emptyMsg) {
        emptyMsg.remove();
      }

      // Update URL without reloading page
      const currentUrl = new URL(window.location.href);
      if (checkedTypes.length > 0) {
        currentUrl.searchParams.set('filter.p.m.custom.vive_vr_headsets', checkedTypes[0]);
      } else {
        currentUrl.searchParams.delete('filter.p.m.custom.vive_vr_headsets');
      }
      if (sortVal && sortVal !== 'best-selling') {
        currentUrl.searchParams.set('sort_by', sortVal);
      }
      window.history.replaceState({}, '', currentUrl.toString());
    }

    // Attach event listeners to checkboxes
    document.querySelectorAll('input[name="filter.p.m.custom.vive_vr_headsets"]').forEach(cb => {
      cb.addEventListener('change', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const val = e.target.value;
        const isChecked = e.target.checked;
        document.querySelectorAll(`input[name="filter.p.m.custom.vive_vr_headsets"][value="${val}"]`).forEach(other => {
          other.checked = isChecked;
        });
        applyFilters();
      });
    });

    // Attach event listeners to sort
    const sortSelects = document.querySelectorAll('#sidebar-SortBy, #drawer-SortBy');
    sortSelects.forEach(s => {
      s.addEventListener('change', (e) => {
        e.preventDefault();
        const val = e.target.value;
        sortSelects.forEach(other => { other.value = val; });
        applyFilters();
      });
    });

    // Intercept form submit and prevent page reload
    document.querySelectorAll('#FacetFiltersForm, #FacetFiltersFormMobile, .facets__form').forEach(form => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        e.stopPropagation();
        applyFilters();
        return false;
      });
    });

    // Prevent any facet link from reloading page
    document.querySelectorAll('.facets a, .facet-checkbox').forEach(el => {
      el.addEventListener('click', (e) => {
        if (e.target.tagName !== 'INPUT') {
          const input = el.querySelector('input[type="checkbox"]');
          if (input) {
            e.preventDefault();
            input.checked = !input.checked;
            input.dispatchEvent(new Event('change'));
          }
        }
      });
    });

    // Initial run
    applyFilters();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initViveFilters);
  } else {
    initViveFilters();
  }
})();
