<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="shop-wrapper sf-woo" id="shopFilterWrapper"
     data-per-page="<?php echo esc_attr( $atts['per_page'] ); ?>"
     data-orderby="<?php echo esc_attr( $atts['orderby'] ); ?>">

  <!-- PAGE HEADER -->
  <div class="shop-header">
    <div class="shop-title-block">
      <h1 class="shop-title"><?php echo esc_html( $atts['title'] ); ?></h1>
      <p class="shop-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
    </div>
    <div class="shop-controls">
      <div class="view-toggle">
        <button class="view-btn active" data-view="grid" title="Grid View">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor"/></svg>
        </button>
        <button class="view-btn" data-view="list" title="List View">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="3" rx="1.5" fill="currentColor"/><rect x="1" y="7" width="14" height="3" rx="1.5" fill="currentColor"/><rect x="1" y="12" width="14" height="3" rx="1.5" fill="currentColor"/></svg>
        </button>
      </div>
      <div class="sort-dropdown" id="sfSortDropdown">
          <span class="sort-label"><?php esc_html_e( 'Sort by:', 'shop-filter' ); ?></span>
          <span class="sort-selected" id="sfSortSelected"><?php esc_html_e( 'Most Popular', 'shop-filter' ); ?></span>
          <ul class="sort-list" id="sfSortList">
            <li class="sort-item active" data-value="popular"><?php esc_html_e( 'Most Popular', 'shop-filter' ); ?></li>
            <li class="sort-item" data-value="default"><?php esc_html_e( 'Default Sorting', 'shop-filter' ); ?></li>
            <li class="sort-item" data-value="rating"><?php esc_html_e( 'Average Rating', 'shop-filter' ); ?></li>
            <li class="sort-item" data-value="recent"><?php esc_html_e( 'Recents', 'shop-filter' ); ?></li>
          </ul>
        </div>
      <div class="results-count" id="sfResultsCount"><?php esc_html_e( 'Loading...', 'shop-filter' ); ?></div>
    </div>
  </div>

  <div class="shop-body">

    <!-- DESKTOP SIDEBAR -->
    <aside class="filter-sidebar" id="sfFilterSidebar">
      <h2 class="filter-heading"><?php esc_html_e( 'Filter', 'shop-filter' ); ?></h2>

      <!-- Categories (dynamic) -->
      <div class="filter-section" id="sfCatSection">
        <div class="filter-section-header" onclick="sfToggleSection('sfCatSection')">
          <span><?php esc_html_e( 'Categories', 'shop-filter' ); ?></span>
          <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="filter-section-body" id="sfCatList">
          <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
        </div>
      </div>

      <!-- Price Range (dynamic min/max) -->
      <div class="filter-section" id="sfPriceSection">
        <div class="filter-section-header" onclick="sfToggleSection('sfPriceSection')">
          <span><?php esc_html_e( 'Price', 'shop-filter' ); ?></span>
          <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="filter-section-body">
          <div class="price-range-labels">
            <span id="sfPriceMin">-</span>
            <span id="sfPriceMax">-</span>
          </div>
          <div class="range-slider-wrap">
            <div class="range-track"><div class="range-fill" id="sfRangeFill"></div></div>
            <input type="range" class="range-input" id="sfRangeMax" min="0" max="1000" value="1000">
          </div>
        </div>
      </div>

      <!-- Brands (dynamic) -->
      <div class="filter-section" id="sfBrandSection">
        <div class="filter-section-header" onclick="sfToggleSection('sfBrandSection')">
          <span><?php esc_html_e( 'Service Provider', 'shop-filter' ); ?></span>
          <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="filter-section-body" id="sfBrandList">
          <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
        </div>
      </div>

      <!-- Duration (dynamic) -->
      <div class="filter-section" id="sfDurSection">
        <div class="filter-section-header" onclick="sfToggleSection('sfDurSection')">
          <span><?php esc_html_e( 'Duration', 'shop-filter' ); ?></span>
          <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="filter-section-body" id="sfDurList">
          <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
        </div>
      </div>

      <button class="apply-filters-btn" id="sfApplyBtn" onclick="sfApplyFilters()">
        <?php esc_html_e( 'Apply Filters', 'shop-filter' ); ?>
      </button>
    </aside>

    <!-- PRODUCTS AREA -->
    <main class="products-area">

      <!-- Mobile Bar -->
      <div class="mobile-bar">
        <button class="mobile-btn" onclick="sfOpenMobileFilter()">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          <?php esc_html_e( 'Filter', 'shop-filter' ); ?>
        </button>
        <button class="mobile-btn" onclick="sfOpenSortPanel()">
          <?php esc_html_e( 'Sort By', 'shop-filter' ); ?>
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="view-toggle mobile-view-toggle">
          <button class="view-btn active" data-view="grid">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor"/></svg>
          </button>
          <button class="view-btn" data-view="list">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="3" rx="1.5" fill="currentColor"/><rect x="1" y="7" width="14" height="3" rx="1.5" fill="currentColor"/><rect x="1" y="12" width="14" height="3" rx="1.5" fill="currentColor"/></svg>
          </button>
        </div>
      </div>

      <!-- Loader -->
      <div class="products-loader" id="sfProductsLoader">
        <div class="spinner"></div>
        <p><?php esc_html_e( 'Filtering products…', 'shop-filter' ); ?></p>
      </div>

      <!-- Products Grid -->
      <div class="products-grid" id="sfProductsGrid"></div>

      <!-- No Results -->
      <div class="sf-no-results" id="sfNoResults" style="display:none">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="22" stroke="#e5e7eb" stroke-width="2"/><path d="M16 24h16M24 16v16" stroke="#d1d5db" stroke-width="2" stroke-linecap="round"/></svg>
        <p><?php esc_html_e( 'No products found. Try adjusting your filters.', 'shop-filter' ); ?></p>
      </div>

      <!-- Load More -->
      <div class="load-more-wrap" id="sfLoadMoreWrap" style="display:none">
        <button class="load-more-btn" id="sfLoadMoreBtn" onclick="sfLoadMore()">
          <span class="load-more-text"><?php esc_html_e( 'Load more', 'shop-filter' ); ?></span>
          <div class="load-more-spinner"></div>
        </button>
      </div>

    </main>
  </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="sfMobileOverlay" onclick="sfCloseMobilePanels()"></div>

<!-- Mobile Filter Panel -->
<div class="mobile-panel" id="sfMobileFilterPanel">
  <div class="mobile-panel-header">
    <h2><?php esc_html_e( 'Filter', 'shop-filter' ); ?></h2>
    <button class="panel-close-btn" onclick="sfCloseMobilePanels()">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </div>
  <div class="mobile-panel-body">

    <div class="filter-section" id="sfMCatSection">
      <div class="filter-section-header" onclick="sfToggleSection('sfMCatSection')">
        <span><?php esc_html_e( 'Categories', 'shop-filter' ); ?></span>
        <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="filter-section-body" id="sfMCatList">
        <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
      </div>
    </div>

    <div class="filter-section" id="sfMPriceSection">
      <div class="filter-section-header" onclick="sfToggleSection('sfMPriceSection')">
        <span><?php esc_html_e( 'Price', 'shop-filter' ); ?></span>
        <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="filter-section-body">
        <div class="price-range-labels"><span id="sfMPriceMin">-</span><span id="sfMPriceMax">-</span></div>
        <div class="range-slider-wrap">
          <div class="range-track"><div class="range-fill" id="sfMRangeFill"></div></div>
          <input type="range" class="range-input" id="sfMRangeMax" min="0" max="1000" value="1000">
        </div>
      </div>
    </div>

    <div class="filter-section collapsed" id="sfMBrandSection">
      <div class="filter-section-header" onclick="sfToggleSection('sfMBrandSection')">
        <span><?php esc_html_e( 'Service Provider', 'shop-filter' ); ?></span>
        <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="filter-section-body" id="sfMBrandList">
        <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
      </div>
    </div>

    <div class="filter-section collapsed" id="sfMDurSection">
      <div class="filter-section-header" onclick="sfToggleSection('sfMDurSection')">
        <span><?php esc_html_e( 'Duration', 'shop-filter' ); ?></span>
        <svg class="chevron" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="filter-section-body" id="sfMDurList">
        <div class="sf-loading-options"><div class="sf-dot-loader"></div></div>
      </div>
    </div>

  </div>
  <div class="mobile-panel-footer">
    <button class="apply-filters-btn" onclick="sfApplyFilters(); sfCloseMobilePanels()">
      <?php esc_html_e( 'Apply Filters', 'shop-filter' ); ?>
    </button>
  </div>
</div>

<!-- Mobile Sort Panel -->
<div class="mobile-panel" id="sfMobileSortPanel">
  <div class="mobile-panel-header">
    <h2><?php esc_html_e( 'Sort By', 'shop-filter' ); ?></h2>
    <button class="panel-close-btn" onclick="sfCloseMobilePanels()">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </div>
  <div class="mobile-panel-body">
    <div class="filter-section-header-static"><?php esc_html_e( 'Sort Options', 'shop-filter' ); ?></div>
    <label class="filter-checkbox checked sort-radio"><input type="radio" name="sfSortMobile" value="popular" checked> <span class="checkmark"></span> <?php esc_html_e( 'Most Popular', 'shop-filter' ); ?></label>
    <label class="filter-checkbox sort-radio"><input type="radio" name="sfSortMobile" value="default"> <span class="checkmark"></span> <?php esc_html_e( 'Default Sorting', 'shop-filter' ); ?></label>
    <label class="filter-checkbox sort-radio"><input type="radio" name="sfSortMobile" value="rating"> <span class="checkmark"></span> <?php esc_html_e( 'Average Rating', 'shop-filter' ); ?></label>
    <label class="filter-checkbox sort-radio"><input type="radio" name="sfSortMobile" value="recent"> <span class="checkmark"></span> <?php esc_html_e( 'Recents', 'shop-filter' ); ?></label>
  </div>
  <div class="mobile-panel-footer">
    <button class="apply-filters-btn" onclick="sfApplyFilters(); sfCloseMobilePanels()">
      <?php esc_html_e( 'Apply Filters', 'shop-filter' ); ?>
    </button>
  </div>
</div>