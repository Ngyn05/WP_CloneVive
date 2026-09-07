/**
 * Collection Product Grid Initialization
 */
(function() {
  'use strict';

  function initCollectionGrid() {
    const grid = document.getElementById('main-collection-product-grid');
    if (!grid) return;

    const items = Array.from(grid.querySelectorAll('.product-item'));
    if (!items.length) return;

    // Ensure all products are visible
    items.forEach(el => {
      el.style.display = '';
    });

    // Update count text
    const countEls = document.querySelectorAll('#CollectionProductCount, .collection-product-count');
    countEls.forEach(c => {
      c.textContent = `Hiển thị ${items.length} sản phẩm`;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCollectionGrid);
  } else {
    initCollectionGrid();
  }
})();
