<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function storefront_ui_catalog()
{
    return array(
        'meta.skip' => array('g' => 'Navigation', 'l' => 'Skip to content', 'd' => 'Skip to content'),
        'nav.support' => array('g' => 'Navigation', 'l' => 'Support nav label', 'd' => 'Support'),
        'nav.contact' => array('g' => 'Navigation', 'l' => 'Contact', 'd' => 'Contact'),
        'nav.help' => array('g' => 'Navigation', 'l' => 'Help', 'd' => 'Help'),
        'nav.back_products' => array('g' => 'Navigation', 'l' => 'Back to products', 'd' => 'Back to products'),
        'nav.open_menu' => array('g' => 'Navigation', 'l' => 'Open menu', 'd' => 'Open menu'),
        'nav.close_menu' => array('g' => 'Navigation', 'l' => 'Close menu', 'd' => 'Close menu'),
        'nav.search_label' => array('g' => 'Navigation', 'l' => 'Search label', 'd' => 'Search products'),
        'nav.search_placeholder' => array('g' => 'Navigation', 'l' => 'Search placeholder', 'd' => 'Search for products...'),
        'nav.search' => array('g' => 'Navigation', 'l' => 'Search button', 'd' => 'Search'),
        'nav.account' => array('g' => 'Navigation', 'l' => 'Account', 'd' => 'Account'),
        'nav.cart' => array('g' => 'Navigation', 'l' => 'Cart', 'd' => 'Cart'),
        'nav.all_categories' => array('g' => 'Navigation', 'l' => 'All categories', 'd' => 'All Categories'),
        'nav.home' => array('g' => 'Navigation', 'l' => 'Home', 'd' => 'Home'),
        'nav.shop' => array('g' => 'Navigation', 'l' => 'Shop', 'd' => 'Shop'),
        'nav.deals' => array('g' => 'Navigation', 'l' => 'Deals', 'd' => 'Deals'),
        'nav.main' => array('g' => 'Navigation', 'l' => 'Main nav label', 'd' => 'Main'),
        'nav.categories' => array('g' => 'Navigation', 'l' => 'Categories drawer', 'd' => 'Categories'),
        'nav.home_aria' => array('g' => 'Navigation', 'l' => 'Logo home aria', 'd' => '{store} home'),
        'nav.breadcrumb' => array('g' => 'Navigation', 'l' => 'Breadcrumb label', 'd' => 'Breadcrumb'),

        'home.featured_promos' => array('g' => 'Home', 'l' => 'Hero carousel label', 'd' => 'Featured promotions'),
        'home.prev_slide' => array('g' => 'Home', 'l' => 'Previous slide', 'd' => 'Previous slide'),
        'home.next_slide' => array('g' => 'Home', 'l' => 'Next slide', 'd' => 'Next slide'),
        'home.shop_by_category' => array('g' => 'Home', 'l' => 'Shop by category', 'd' => 'Shop by category'),
        'home.featured' => array('g' => 'Home', 'l' => 'Featured products', 'd' => 'Featured Products'),
        'home.handpicked' => array('g' => 'Home', 'l' => 'Handpicked for store', 'd' => 'Handpicked for {store}'),
        'home.view_all' => array('g' => 'Home', 'l' => 'View all products', 'd' => 'View All Products'),
        'home.no_products' => array('g' => 'Home', 'l' => 'No products yet', 'd' => 'No products available yet.'),
        'home.why_shop' => array('g' => 'Home', 'l' => 'Why shop with us', 'd' => 'Why shop with us'),
        'home.seasonal' => array('g' => 'Home', 'l' => 'Seasonal shops', 'd' => 'Seasonal shops'),
        'home.kicker_living' => array('g' => 'Home', 'l' => 'Default kicker', 'd' => 'Modern Living'),
        'home.kicker_season' => array('g' => 'Home', 'l' => 'New season kicker', 'd' => 'New Season'),
        'home.title_fresh' => array('g' => 'Home', 'l' => 'Fresh picks title', 'd' => "Fresh Picks\nFor You"),
        'home.text_fresh' => array('g' => 'Home', 'l' => 'Fresh picks text', 'd' => 'Explore the latest products curated for {store}.'),
        'home.kicker_value' => array('g' => 'Home', 'l' => 'Best value kicker', 'd' => 'Best Value'),
        'home.title_smart' => array('g' => 'Home', 'l' => 'Shop smart title', 'd' => "Shop Smart\nLive Better"),
        'home.text_smart' => array('g' => 'Home', 'l' => 'Shop smart text', 'd' => "Quality products at prices you'll love."),
        'home.disc_upto' => array('g' => 'Home', 'l' => 'Discount up to', 'd' => 'UP TO'),
        'home.disc_off' => array('g' => 'Home', 'l' => 'Discount off', 'd' => 'OFF'),
        'home.seasonal_alt' => array('g' => 'Home', 'l' => 'Seasonal promo alt', 'd' => 'Seasonal promo'),
        'home.festive_alt' => array('g' => 'Home', 'l' => 'Festive promo alt', 'd' => 'Festive promo'),
        'cta.shop_now' => array('g' => 'Home', 'l' => 'Shop now', 'd' => 'Shop Now'),
        'cta.browse_shop' => array('g' => 'Home', 'l' => 'Browse shop', 'd' => 'Browse Shop'),
        'cta.shop_deals' => array('g' => 'Home', 'l' => 'Shop deals', 'd' => 'Shop Deals'),
        'cta.browse_gifts' => array('g' => 'Home', 'l' => 'Browse gifts', 'd' => 'Browse Gifts'),
        'home.explore' => array('g' => 'Home', 'l' => 'Explore kicker', 'd' => 'Explore'),
        'home.top_picks' => array('g' => 'Home', 'l' => 'Top picks title', 'd' => 'Top Picks'),
        'home.top_picks_text' => array('g' => 'Home', 'l' => 'Top picks text', 'd' => 'Curated essentials & more'),
        'home.gifts_kicker' => array('g' => 'Home', 'l' => 'Gifts kicker', 'd' => 'Make It Special'),
        'home.gifts' => array('g' => 'Home', 'l' => 'Gift ideas title', 'd' => 'Gift Ideas'),
        'home.gifts_text' => array('g' => 'Home', 'l' => 'Gift ideas text', 'd' => 'Finds the whole family will love'),

        'trust.free_shipping' => array('g' => 'Trust', 'l' => 'Free shipping title', 'd' => 'Free Shipping'),
        'trust.free_shipping_text' => array('g' => 'Trust', 'l' => 'Free shipping text', 'd' => 'On qualifying orders'),
        'trust.secure' => array('g' => 'Trust', 'l' => 'Secure payment title', 'd' => 'Secure Payment'),
        'trust.secure_text' => array('g' => 'Trust', 'l' => 'Secure payment text', 'd' => '100% secure checkout'),
        'trust.returns' => array('g' => 'Trust', 'l' => 'Easy returns title', 'd' => 'Easy Returns'),
        'trust.returns_text' => array('g' => 'Trust', 'l' => 'Easy returns text', 'd' => 'Hassle-free policy'),
        'trust.support' => array('g' => 'Trust', 'l' => 'Support title', 'd' => '24/7 Support'),
        'trust.support_text' => array('g' => 'Trust', 'l' => 'Support text', 'd' => "We're here to help"),
        'trust.free_delivery' => array('g' => 'Trust', 'l' => 'Free delivery (PDP)', 'd' => 'Free Delivery'),
        'trust.delivery' => array('g' => 'Trust', 'l' => 'Delivery title', 'd' => 'Delivery'),
        'trust.flat_rate' => array('g' => 'Trust', 'l' => 'Flat rate text', 'd' => 'Flat rate {amount} per item'),
        'trust.free_from' => array('g' => 'Trust', 'l' => 'Free shipping from amount', 'd' => 'Free shipping from {amount}'),
        'trust.tracked' => array('g' => 'Trust', 'l' => 'Tracked shipping', 'd' => 'Tracked shipping available'),
        'trust.returns_simple' => array('g' => 'Trust', 'l' => 'Simple returns', 'd' => 'Simple returns process'),
        'trust.secure_checkout' => array('g' => 'Trust', 'l' => 'Secure checkout', 'd' => 'Secure Checkout'),
        'trust.protected' => array('g' => 'Trust', 'l' => 'Protected payments', 'd' => 'Protected payments'),
        'trust.customer_support' => array('g' => 'Trust', 'l' => 'Customer support', 'd' => 'Customer Support'),
        'trust.help' => array('g' => 'Trust', 'l' => 'We are here to help', 'd' => 'We are here to help'),

        'shop.category' => array('g' => 'Shop', 'l' => 'Category kicker', 'd' => 'Category'),
        'shop.everything' => array('g' => 'Shop', 'l' => 'Everything kicker', 'd' => 'Everything In One Place'),
        'shop.all_products' => array('g' => 'Shop', 'l' => 'All products', 'd' => 'All Products'),
        'shop.browse_range' => array('g' => 'Shop', 'l' => 'Browse range', 'd' => 'Browse the full {store} range.'),
        'shop.start_browsing' => array('g' => 'Shop', 'l' => 'Start browsing', 'd' => 'Start Browsing'),
        'shop.filters' => array('g' => 'Shop', 'l' => 'Filters label', 'd' => 'Product filters'),
        'shop.filter_by' => array('g' => 'Shop', 'l' => 'Filter by', 'd' => 'Filter By'),
        'shop.clear_all' => array('g' => 'Shop', 'l' => 'Clear all', 'd' => 'Clear All'),
        'shop.everything_option' => array('g' => 'Shop', 'l' => 'Everything option', 'd' => 'Everything'),
        'shop.price_range' => array('g' => 'Shop', 'l' => 'Price range', 'd' => 'Price Range'),
        'shop.min' => array('g' => 'Shop', 'l' => 'Min', 'd' => 'Min'),
        'shop.max' => array('g' => 'Shop', 'l' => 'Max', 'd' => 'Max'),
        'shop.availability' => array('g' => 'Shop', 'l' => 'Availability', 'd' => 'Availability'),
        'shop.in_stock' => array('g' => 'Shop', 'l' => 'In stock filter', 'd' => 'In Stock'),
        'shop.on_sale' => array('g' => 'Shop', 'l' => 'On sale filter', 'd' => 'On Sale'),
        'shop.search_sort' => array('g' => 'Shop', 'l' => 'Search / sort', 'd' => 'Search / Sort'),
        'shop.search_products' => array('g' => 'Shop', 'l' => 'Search products placeholder', 'd' => 'Search products...'),
        'shop.sort_newest' => array('g' => 'Shop', 'l' => 'Sort newest', 'd' => 'Newest'),
        'shop.sort_price_asc' => array('g' => 'Shop', 'l' => 'Price low to high', 'd' => 'Price: Low to High'),
        'shop.sort_price_desc' => array('g' => 'Shop', 'l' => 'Price high to low', 'd' => 'Price: High to Low'),
        'shop.sort_name' => array('g' => 'Shop', 'l' => 'Name A-Z', 'd' => 'Name A–Z'),
        'shop.apply' => array('g' => 'Shop', 'l' => 'Apply filters', 'd' => 'Apply Filters'),
        'shop.products' => array('g' => 'Shop', 'l' => 'Products region', 'd' => 'Products'),
        'shop.product_one' => array('g' => 'Shop', 'l' => 'One product count', 'd' => '{n} product'),
        'shop.product_many' => array('g' => 'Shop', 'l' => 'Many products count', 'd' => '{n} products'),
        'shop.item_one' => array('g' => 'Shop', 'l' => 'One item count', 'd' => '{n} item'),
        'shop.item_many' => array('g' => 'Shop', 'l' => 'Many items count', 'd' => '{n} items'),
        'shop.no_match' => array('g' => 'Shop', 'l' => 'No filter match', 'd' => 'No products match these filters.'),
        'shop.no_category' => array('g' => 'Shop', 'l' => 'No category products', 'd' => 'No products in this category yet.'),
        'shop.subcats' => array('g' => 'Shop', 'l' => 'Sub categories', 'd' => 'Sub categories'),
        'shop.shop_subcat' => array('g' => 'Shop', 'l' => 'Shop by sub-category', 'd' => 'Shop by sub-category'),
        'shop.browse_within' => array('g' => 'Shop', 'l' => 'Browse within category', 'd' => 'Browse within {name}'),
        'shop.category_products' => array('g' => 'Shop', 'l' => 'Category products heading', 'd' => '{name} products'),
        'shop.loading_more' => array('g' => 'Shop', 'l' => 'Loading more', 'd' => 'Loading more…'),
        'page.shop' => array('g' => 'Shop', 'l' => 'Shop page title', 'd' => 'Shop'),

        'product.add_cart' => array('g' => 'Product', 'l' => 'Add to cart', 'd' => 'Add to Cart'),
        'product.buy_now' => array('g' => 'Product', 'l' => 'Buy now', 'd' => 'Buy Now'),
        'product.in_stock' => array('g' => 'Product', 'l' => 'In stock', 'd' => 'In stock'),
        'product.out_of_stock' => array('g' => 'Product', 'l' => 'Out of stock', 'd' => 'Out of stock'),
        'product.in_stock_qty' => array('g' => 'Product', 'l' => 'In stock with qty', 'd' => 'In stock: {n}'),
        'product.sku' => array('g' => 'Product', 'l' => 'SKU label', 'd' => 'SKU'),
        'product.bestseller' => array('g' => 'Product', 'l' => 'Bestseller', 'd' => 'Bestseller'),
        'product.review_one' => array('g' => 'Product', 'l' => 'One review', 'd' => '{n} review'),
        'product.review_many' => array('g' => 'Product', 'l' => 'Many reviews', 'd' => '{n} reviews'),
        'product.qty' => array('g' => 'Product', 'l' => 'Quantity', 'd' => 'Quantity'),
        'product.qty_down' => array('g' => 'Product', 'l' => 'Decrease quantity', 'd' => 'Decrease quantity'),
        'product.qty_up' => array('g' => 'Product', 'l' => 'Increase quantity', 'd' => 'Increase quantity'),
        'product.add_wishlist' => array('g' => 'Product', 'l' => 'Add to wishlist', 'd' => 'Add to Wishlist'),
        'product.in_wishlist' => array('g' => 'Product', 'l' => 'In wishlist', 'd' => 'In Wishlist'),
        'product.share' => array('g' => 'Product', 'l' => 'Share product', 'd' => 'Share Product'),
        'product.tab_desc' => array('g' => 'Product', 'l' => 'Description tab', 'd' => 'Description'),
        'product.tab_specs' => array('g' => 'Product', 'l' => 'Specifications tab', 'd' => 'Specifications'),
        'product.tab_reviews' => array('g' => 'Product', 'l' => 'Reviews tab', 'd' => 'Reviews'),
        'product.tab_faqs' => array('g' => 'Product', 'l' => 'FAQs tab', 'd' => 'FAQs'),
        'product.desc_title' => array('g' => 'Product', 'l' => 'Product description', 'd' => 'Product Description'),
        'product.desc_empty' => array('g' => 'Product', 'l' => 'Empty description', 'd' => 'Details for this product will appear here.'),
        'product.video' => array('g' => 'Product', 'l' => 'Product video', 'd' => 'Product video'),
        'product.show_more' => array('g' => 'Product', 'l' => 'Show more', 'd' => 'Show more'),
        'product.show_less' => array('g' => 'Product', 'l' => 'Show less', 'd' => 'Show less'),
        'product.offer_save' => array('g' => 'Product', 'l' => 'Offer save amount', 'd' => 'You Save {amount}'),
        'product.offer_ends' => array('g' => 'Product', 'l' => 'Offer ends in', 'd' => 'Offer ends in:'),
        'product.offer_limited' => array('g' => 'Product', 'l' => 'Limited-time offer', 'd' => 'Limited-time offer'),
        'product.offer_free_shipping' => array('g' => 'Product', 'l' => 'Offer free shipping badge', 'd' => '🚚 FREE SHIPPING on offer'),
        'product.free_shipping' => array('g' => 'Product', 'l' => 'Product free shipping', 'd' => '🚚 FREE SHIPPING'),
        'product.offer_card_title' => array('g' => 'Product', 'l' => 'Offer card title', 'd' => 'Current offer'),
        'product.offer_card_qty' => array('g' => 'Product', 'l' => 'Offer card qty', 'd' => '{n} item'),
        'product.offer_card_qty_many' => array('g' => 'Product', 'l' => 'Offer card qty many', 'd' => '{n} items'),
        'product.offer_card_off' => array('g' => 'Product', 'l' => 'Offer card off percent', 'd' => '{n}% OFF'),
        'product.offer_card_off_short' => array('g' => 'Product', 'l' => 'Offer card off short', 'd' => 'OFF'),
        'product.offer_card_save' => array('g' => 'Product', 'l' => 'Offer card save', 'd' => 'Save {amount}'),
        'product.offer_card_save_short' => array('g' => 'Product', 'l' => 'Offer card save short', 'd' => 'SAVE'),
        'product.offer_card_bxgy' => array('g' => 'Product', 'l' => 'Offer card buy x get y', 'd' => 'Buy {buy} get {get}'),
        'product.offer_card_bxgy_short' => array('g' => 'Product', 'l' => 'Offer card bxgy short', 'd' => 'DEAL'),
        'product.offer_card_bundle' => array('g' => 'Product', 'l' => 'Offer card bundle', 'd' => 'Buy {qty} for {amount}'),
        'product.offer_card_bundle_short' => array('g' => 'Product', 'l' => 'Offer card bundle short', 'd' => 'PACK'),
        'product.offer_card_claim' => array('g' => 'Product', 'l' => 'Offer card claim CTA', 'd' => 'Add & checkout'),
        'product.offer_cards_title' => array('g' => 'Product', 'l' => 'Offer cards section title', 'd' => 'Choose an offer'),
        'product.offer_card_bogo' => array('g' => 'Product', 'l' => 'Offer card buy N get 1 free', 'd' => 'Buy {qty} get 1 free'),
        'product.offer_card_bogo_short' => array('g' => 'Product', 'l' => 'Offer card bogo short', 'd' => 'FREE'),
        'product.no_specs' => array('g' => 'Product', 'l' => 'No specifications', 'd' => 'No specifications have been added for this product yet.'),
        'product.reviews_title' => array('g' => 'Product', 'l' => 'Customer reviews', 'd' => 'Customer Reviews'),
        'product.reviews_based' => array('g' => 'Product', 'l' => 'Based on reviews', 'd' => 'Based on {n} review'),
        'product.reviews_based_many' => array('g' => 'Product', 'l' => 'Based on reviews (many)', 'd' => 'Based on {n} reviews'),
        'product.write_review' => array('g' => 'Product', 'l' => 'Write a review', 'd' => 'Write a Review'),
        'product.your_name' => array('g' => 'Product', 'l' => 'Your name', 'd' => 'Your name'),
        'product.rating' => array('g' => 'Product', 'l' => 'Rating', 'd' => 'Rating'),
        'product.star_one' => array('g' => 'Product', 'l' => 'One star', 'd' => '{n} star'),
        'product.star_many' => array('g' => 'Product', 'l' => 'Many stars', 'd' => '{n} stars'),
        'product.review_title' => array('g' => 'Product', 'l' => 'Review title', 'd' => 'Title'),
        'product.review_body' => array('g' => 'Product', 'l' => 'Review body', 'd' => 'Review'),
        'product.submit_review' => array('g' => 'Product', 'l' => 'Submit review', 'd' => 'Submit review'),
        'product.no_reviews' => array('g' => 'Product', 'l' => 'No reviews yet', 'd' => 'No reviews yet.'),
        'product.review_sample' => array('g' => 'Product', 'l' => 'Sample review badge', 'd' => 'Sample review'),
        'product.reviews_sample_note' => array('g' => 'Product', 'l' => 'Sample reviews note', 'd' => 'Sample reviews are demonstration content, not verified customer purchases.'),
        'product.faqs_title' => array('g' => 'Product', 'l' => 'FAQs heading', 'd' => 'Frequently Asked Questions'),
        'product.no_faqs' => array('g' => 'Product', 'l' => 'No FAQs', 'd' => 'No FAQs have been added for this product yet.'),
        'product.related' => array('g' => 'Product', 'l' => 'Related products', 'd' => 'Related Products'),
        'product.trending' => array('g' => 'Product', 'l' => 'Trending picks', 'd' => '🔥 Trending Picks'),
        'product.view_all' => array('g' => 'Product', 'l' => 'View all products', 'd' => 'View All →'),
        'product.prev' => array('g' => 'Product', 'l' => 'Previous products', 'd' => 'Previous products'),
        'product.next' => array('g' => 'Product', 'l' => 'Next products', 'd' => 'Next products'),
        'product.also_like' => array('g' => 'Product', 'l' => 'You may also like', 'd' => 'You may also like'),
        'product.details' => array('g' => 'Product', 'l' => 'Details heading', 'd' => 'Details'),
        'product.category' => array('g' => 'Product', 'l' => 'Category fact', 'd' => 'Category'),
        'product.subcategory' => array('g' => 'Product', 'l' => 'Sub category fact', 'd' => 'Sub category'),
        'product.made_by' => array('g' => 'Product', 'l' => 'Made by', 'd' => 'Made by'),
        'product.choose_delivery' => array('g' => 'Product', 'l' => 'Choose delivery', 'd' => 'Choose Delivery'),
        'product.choose_pack' => array('g' => 'Product', 'l' => 'Choose pack', 'd' => 'Choose Your Pack'),
        'product.options' => array('g' => 'Product', 'l' => 'Product options', 'd' => 'Product options'),
        'product.arrives' => array('g' => 'Product', 'l' => 'Arrives dates', 'd' => 'Arrives {dates}'),
        'product.watch_video' => array('g' => 'Product', 'l' => 'Watch video', 'd' => 'Watch Video'),
        'product.prev_image' => array('g' => 'Product', 'l' => 'Previous image', 'd' => 'Previous image'),
        'product.next_image' => array('g' => 'Product', 'l' => 'Next image', 'd' => 'Next image'),
        'product.zoom' => array('g' => 'Product', 'l' => 'Zoom image', 'd' => 'Zoom image'),
        'product.show_image' => array('g' => 'Product', 'l' => 'Show image n', 'd' => 'Show image {n}'),
        'product.delivery' => array('g' => 'Product', 'l' => 'Delivery heading', 'd' => 'Delivery'),
        'product.eta' => array('g' => 'Product', 'l' => 'Expected delivery', 'd' => 'Estimated delivery: {dates}'),
        'product.eta_working' => array('g' => 'Product', 'l' => 'Working days many', 'd' => 'Estimated delivery: {n} days'),
        'product.eta_working_one' => array('g' => 'Product', 'l' => 'Working days one', 'd' => 'Estimated delivery: {n} day'),
        'product.eta_working_range' => array('g' => 'Product', 'l' => 'Working days range', 'd' => 'Estimated delivery: {min}–{max} days'),
        'product.eta_vary' => array('g' => 'Product', 'l' => 'Delivery may vary', 'd' => 'Delivery times may vary depending on the shipping method and destination.'),
        'product.arrive_on' => array('g' => 'Product', 'l' => 'Arrive on date', 'd' => 'This product will arrive on {date}.'),
        'product.arrive_between' => array('g' => 'Product', 'l' => 'Arrive between dates', 'd' => 'This product will arrive between {from} and {to}.'),
        'cart.delivery_eta' => array('g' => 'Cart', 'l' => 'Cart delivery ETA', 'd' => 'Estimated delivery: {min}–{max} days'),
        'product.fallback_desc' => array('g' => 'Product', 'l' => 'Fallback description', 'd' => 'Selected for the ZENVello collection.'),
        'month.1' => array('g' => 'Product', 'l' => 'January short', 'd' => 'Jan'),
        'month.2' => array('g' => 'Product', 'l' => 'February short', 'd' => 'Feb'),
        'month.3' => array('g' => 'Product', 'l' => 'March short', 'd' => 'Mar'),
        'month.4' => array('g' => 'Product', 'l' => 'April short', 'd' => 'Apr'),
        'month.5' => array('g' => 'Product', 'l' => 'May short', 'd' => 'May'),
        'month.6' => array('g' => 'Product', 'l' => 'June short', 'd' => 'Jun'),
        'month.7' => array('g' => 'Product', 'l' => 'July short', 'd' => 'Jul'),
        'month.8' => array('g' => 'Product', 'l' => 'August short', 'd' => 'Aug'),
        'month.9' => array('g' => 'Product', 'l' => 'September short', 'd' => 'Sep'),
        'month.10' => array('g' => 'Product', 'l' => 'October short', 'd' => 'Oct'),
        'month.11' => array('g' => 'Product', 'l' => 'November short', 'd' => 'Nov'),
        'month.12' => array('g' => 'Product', 'l' => 'December short', 'd' => 'Dec'),

        'cart.title' => array('g' => 'Cart', 'l' => 'Cart heading', 'd' => 'Your cart'),
        'cart.ready' => array('g' => 'Cart', 'l' => 'Items ready', 'd' => '{n} item ready to checkout.'),
        'cart.ready_many' => array('g' => 'Cart', 'l' => 'Items ready many', 'd' => '{n} items ready to checkout.'),
        'cart.empty_lead' => array('g' => 'Cart', 'l' => 'Empty lead', 'd' => 'Add something you like — it will show up here.'),
        'cart.empty' => array('g' => 'Cart', 'l' => 'Empty heading', 'd' => 'Your cart is empty'),
        'cart.empty_text' => array('g' => 'Cart', 'l' => 'Empty text', 'd' => 'Browse the shop and add items when you are ready.'),
        'cart.continue' => array('g' => 'Cart', 'l' => 'Continue shopping', 'd' => 'Continue shopping'),
        'cart.each' => array('g' => 'Cart', 'l' => 'Price each', 'd' => '{amount} each'),
        'cart.remove' => array('g' => 'Cart', 'l' => 'Remove', 'd' => 'Remove'),
        'cart.update' => array('g' => 'Cart', 'l' => 'Update quantities', 'd' => 'Update quantities'),
        'cart.summary' => array('g' => 'Cart', 'l' => 'Order summary', 'd' => 'Order summary'),
        'cart.subtotal' => array('g' => 'Cart', 'l' => 'Subtotal', 'd' => 'Subtotal'),
        'cart.subtotal_before' => array('g' => 'Cart', 'l' => 'Subtotal before discount', 'd' => 'Subtotal'),
        'cart.discount' => array('g' => 'Cart', 'l' => 'Discount', 'd' => 'Discount'),
        'cart.original' => array('g' => 'Cart', 'l' => 'Original price', 'd' => 'Original: {amount}'),
        'cart.offer_off' => array('g' => 'Cart', 'l' => 'Offer discount', 'd' => 'Offer: −{amount}'),
        'cart.shipping' => array('g' => 'Cart', 'l' => 'Shipping', 'd' => 'Shipping'),
        'cart.shipping_qty' => array('g' => 'Cart', 'l' => 'Shipping with qty', 'd' => 'Shipping × {n}'),
        'cart.shipping_free' => array('g' => 'Cart', 'l' => 'Free shipping', 'd' => 'Free'),
        'cart.shipping_free_from' => array('g' => 'Cart', 'l' => 'Free shipping threshold', 'd' => 'Free from {amount}'),
        'cart.vat' => array('g' => 'Cart', 'l' => 'VAT label', 'd' => 'VAT ({percent}%)'),
        'cart.total' => array('g' => 'Cart', 'l' => 'Total', 'd' => 'Total'),
        'cart.checkout' => array('g' => 'Cart', 'l' => 'Checkout', 'd' => 'Checkout'),
        'cart.go_shop' => array('g' => 'Cart', 'l' => 'Go to shop', 'd' => 'Go to shop'),
        'cart.edit' => array('g' => 'Cart', 'l' => 'Edit cart', 'd' => 'Edit cart'),
        'cart.step' => array('g' => 'Cart', 'l' => 'Cart step', 'd' => 'Cart'),
        'page.cart' => array('g' => 'Cart', 'l' => 'Cart page title', 'd' => 'Cart'),

        'checkout.title' => array('g' => 'Checkout', 'l' => 'Checkout heading', 'd' => 'Checkout'),
        'checkout.lead' => array('g' => 'Checkout', 'l' => 'Checkout lead', 'd' => 'Shipping details for {store}.'),
        'checkout.shipping' => array('g' => 'Checkout', 'l' => 'Shipping details', 'd' => 'Shipping details'),
        'checkout.full_name' => array('g' => 'Checkout', 'l' => 'Full name', 'd' => 'Full name'),
        'checkout.first_name' => array('g' => 'Checkout', 'l' => 'First name', 'd' => 'First name'),
        'checkout.last_name' => array('g' => 'Checkout', 'l' => 'Last name', 'd' => 'Last name'),
        'checkout.email' => array('g' => 'Checkout', 'l' => 'Email', 'd' => 'Email'),
        'checkout.phone' => array('g' => 'Checkout', 'l' => 'Phone', 'd' => 'Phone'),
        'checkout.whatsapp' => array('g' => 'Checkout', 'l' => 'WhatsApp number', 'd' => 'WhatsApp number'),
        'checkout.whatsapp_hint' => array('g' => 'Checkout', 'l' => 'WhatsApp hint', 'd' => 'Required for order updates. Use country code without +.'),
        'checkout.address' => array('g' => 'Checkout', 'l' => 'Address', 'd' => 'Address'),
        'checkout.street' => array('g' => 'Checkout', 'l' => 'Street address', 'd' => 'Street address'),
        'checkout.apartment' => array('g' => 'Checkout', 'l' => 'Apartment or door code', 'd' => 'Apartment / door code (optional)'),
        'checkout.postcode' => array('g' => 'Checkout', 'l' => 'Postcode', 'd' => 'Postcode'),
        'checkout.city' => array('g' => 'Checkout', 'l' => 'City', 'd' => 'City'),
        'checkout.country' => array('g' => 'Checkout', 'l' => 'Country', 'd' => 'Country'),
        'checkout.billing_same' => array('g' => 'Checkout', 'l' => 'Billing same as shipping', 'd' => 'Billing address same as shipping'),
        'checkout.billing' => array('g' => 'Checkout', 'l' => 'Billing address', 'd' => 'Billing address'),
        'checkout.shipping_method' => array('g' => 'Checkout', 'l' => 'Shipping method', 'd' => 'Shipping method'),
        'checkout.shipping_standard' => array('g' => 'Checkout', 'l' => 'Standard shipping', 'd' => 'Standard delivery'),
        'checkout.terms' => array('g' => 'Checkout', 'l' => 'Agree to terms', 'd' => 'I agree to the Terms & Conditions'),
        'checkout.continue' => array('g' => 'Checkout', 'l' => 'Continue to payment', 'd' => 'Continue to payment'),
        'checkout.step_shipping' => array('g' => 'Checkout', 'l' => 'Shipping step', 'd' => 'Shipping'),
        'checkout.step_payment' => array('g' => 'Checkout', 'l' => 'Payment step', 'd' => 'Payment'),
        'checkout.progress' => array('g' => 'Checkout', 'l' => 'Checkout progress', 'd' => 'Checkout progress'),
        'page.checkout' => array('g' => 'Checkout', 'l' => 'Checkout page title', 'd' => 'Checkout'),

        'pay.title' => array('g' => 'Payment', 'l' => 'Payment heading', 'd' => 'Payment'),
        'pay.lead' => array('g' => 'Payment', 'l' => 'Payment lead', 'd' => 'Encrypted checkout — card details are sealed in your browser before they leave this page.'),
        'pay.card' => array('g' => 'Payment', 'l' => 'Pay by card', 'd' => 'Pay by Card'),
        'pay.paypal' => array('g' => 'Payment', 'l' => 'PayPal', 'd' => 'PayPal'),
        'pay.name_on_card' => array('g' => 'Payment', 'l' => 'Name on card', 'd' => 'Name on card'),
        'pay.name_placeholder' => array('g' => 'Payment', 'l' => 'Name on card placeholder', 'd' => 'Name as printed on card'),
        'pay.card_number' => array('g' => 'Payment', 'l' => 'Card number', 'd' => 'Card number'),
        'pay.encrypted' => array('g' => 'Payment', 'l' => 'Encrypted pill', 'd' => 'Encrypted'),
        'pay.expiry' => array('g' => 'Payment', 'l' => 'Expiry', 'd' => 'Expiry'),
        'pay.cvv' => array('g' => 'Payment', 'l' => 'CVV', 'd' => 'CVV'),
        'pay.note' => array('g' => 'Payment', 'l' => 'Card note', 'd' => 'Card details never touch our servers in plain text. They are RSA-encrypted in your browser, then charged through PayPal.'),
        'pay.pay_amount' => array('g' => 'Payment', 'l' => 'Pay amount', 'd' => 'Pay {amount}'),
        'pay.paypal_text' => array('g' => 'Payment', 'l' => 'PayPal text', 'd' => 'Continue with your PayPal account. You’ll confirm the payment on PayPal’s secure site, then return here.'),
        'pay.paypal_btn' => array('g' => 'Payment', 'l' => 'Continue with PayPal', 'd' => 'Continue with PayPal'),
        'pay.shipping_to' => array('g' => 'Payment', 'l' => 'Shipping to', 'd' => 'Shipping to'),
        'pay.edit_shipping' => array('g' => 'Payment', 'l' => 'Edit shipping', 'd' => 'Edit shipping'),
        'page.payment' => array('g' => 'Payment', 'l' => 'Payment page title', 'd' => 'Secure Payment'),
        'pay.method_card' => array('g' => 'Payment', 'l' => 'Paid via card', 'd' => 'Card'),
        'pay.method_paypal' => array('g' => 'Payment', 'l' => 'Paid via PayPal', 'd' => 'PayPal'),

        'thanks.title' => array('g' => 'Thanks', 'l' => 'Thank you', 'd' => 'Thank you'),
        'thanks.placed' => array('g' => 'Thanks', 'l' => 'Order placed', 'd' => 'Order {order} was placed successfully.'),
        'thanks.total_paid' => array('g' => 'Thanks', 'l' => 'Total paid', 'd' => 'Total paid'),
        'thanks.via' => array('g' => 'Thanks', 'l' => 'Paid via', 'd' => 'via {method}'),
        'thanks.email' => array('g' => 'Thanks', 'l' => 'Confirmation email', 'd' => 'A confirmation email is on its way. You can keep shopping in the meantime.'),
        'thanks.ok' => array('g' => 'Thanks', 'l' => 'Order success fallback', 'd' => 'Your order was placed successfully.'),
        'thanks.view_order' => array('g' => 'Thanks', 'l' => 'View order', 'd' => 'View order'),
        'page.thanks' => array('g' => 'Thanks', 'l' => 'Thank you page title', 'd' => 'Thank you'),

        'auth.login' => array('g' => 'Account', 'l' => 'Login heading', 'd' => 'Login'),
        'auth.login_lead' => array('g' => 'Account', 'l' => 'Login lead', 'd' => 'Sign in to your {store} account.'),
        'auth.password' => array('g' => 'Account', 'l' => 'Password', 'd' => 'Password'),
        'auth.login_btn' => array('g' => 'Account', 'l' => 'Login button', 'd' => 'Login'),
        'auth.new_here' => array('g' => 'Account', 'l' => 'New here', 'd' => 'New here?'),
        'auth.guest_checkout' => array('g' => 'Account', 'l' => 'Guest checkout', 'd' => 'Continue as guest'),
        'auth.guest_note' => array('g' => 'Account', 'l' => 'Guest checkout note', 'd' => 'No account yet? Continue as a guest. After a successful order we create your account and email the login details.'),
        'mail.account_subject' => array('g' => 'Mail', 'l' => 'New account subject', 'd' => 'Your {store} account'),
        'mail.account_intro' => array('g' => 'Mail', 'l' => 'New account intro', 'd' => 'Your order {order} is placed. We created an account so you can follow it.'),
        'mail.account_password' => array('g' => 'Mail', 'l' => 'New account password', 'd' => 'Password'),
        'mail.account_login' => array('g' => 'Mail', 'l' => 'New account login link', 'd' => 'Log in'),
        'auth.create_account' => array('g' => 'Account', 'l' => 'Create an account', 'd' => 'Create an account'),
        'auth.signup' => array('g' => 'Account', 'l' => 'Sign up heading', 'd' => 'Sign up'),
        'auth.signup_lead' => array('g' => 'Account', 'l' => 'Sign up lead', 'd' => 'Create your {store} account.'),
        'auth.create_btn' => array('g' => 'Account', 'l' => 'Create account button', 'd' => 'Create account'),
        'auth.have_account' => array('g' => 'Account', 'l' => 'Already have account', 'd' => 'Already have an account?'),
        'auth.account' => array('g' => 'Account', 'l' => 'My account', 'd' => 'My account'),
        'auth.welcome' => array('g' => 'Account', 'l' => 'Welcome back', 'd' => 'Welcome back, {name}.'),
        'auth.account_lead' => array('g' => 'Account', 'l' => 'Account lead', 'd' => 'Manage your profile and orders.'),
        'auth.edit_profile' => array('g' => 'Account', 'l' => 'Edit profile', 'd' => 'Edit profile'),
        'auth.orders' => array('g' => 'Account', 'l' => 'Orders', 'd' => 'Orders'),
        'auth.order_no' => array('g' => 'Account', 'l' => 'Order number', 'd' => 'Order'),
        'auth.product' => array('g' => 'Account', 'l' => 'Product', 'd' => 'Product'),
        'auth.qty' => array('g' => 'Account', 'l' => 'Qty', 'd' => 'Qty'),
        'auth.orders_all' => array('g' => 'Account', 'l' => 'All orders', 'd' => 'All'),
        'auth.no_orders' => array('g' => 'Account', 'l' => 'No orders yet', 'd' => 'No orders yet'),
        'auth.no_orders_text' => array('g' => 'Account', 'l' => 'No orders text', 'd' => 'When you place an order, it will show up here.'),
        'auth.no_status_orders' => array('g' => 'Account', 'l' => 'No products in status', 'd' => 'No products with this status yet.'),
        'auth.start_shopping' => array('g' => 'Account', 'l' => 'Start shopping', 'd' => 'Start shopping'),
        'auth.view' => array('g' => 'Account', 'l' => 'View', 'd' => 'View'),
        'auth.profile' => array('g' => 'Account', 'l' => 'Profile', 'd' => 'Profile'),
        'auth.new_password' => array('g' => 'Account', 'l' => 'New password', 'd' => 'New password'),
        'auth.password_keep' => array('g' => 'Account', 'l' => 'Keep password hint', 'd' => 'Leave blank to keep current password'),
        'auth.save_profile' => array('g' => 'Account', 'l' => 'Save profile', 'd' => 'Save profile'),
        'auth.logout' => array('g' => 'Account', 'l' => 'Logout', 'd' => 'Logout'),
        'auth.back' => array('g' => 'Account', 'l' => 'Back to account', 'd' => '← Back to account'),
        'page.login' => array('g' => 'Account', 'l' => 'Login page title', 'd' => 'Login'),
        'page.signup' => array('g' => 'Account', 'l' => 'Sign up page title', 'd' => 'Sign up'),
        'page.account' => array('g' => 'Account', 'l' => 'Account page title', 'd' => 'Account'),
        'page.order' => array('g' => 'Account', 'l' => 'Order page title', 'd' => 'Order {order}'),
        'order.heading' => array('g' => 'Account', 'l' => 'Order heading', 'd' => 'Order {order}'),
        'order.placed' => array('g' => 'Account', 'l' => 'Placed date', 'd' => 'Placed {date}'),
        'order.items' => array('g' => 'Account', 'l' => 'Items heading', 'd' => 'Items'),
        'order.qty_each' => array('g' => 'Account', 'l' => 'Qty each', 'd' => 'Qty {n} · {amount} each'),
        'order.status' => array('g' => 'Account', 'l' => 'Status heading', 'd' => 'Status'),
        'order.is_status' => array('g' => 'Account', 'l' => 'This order is status', 'd' => 'This order is {status}.'),
        'order.paid_via' => array('g' => 'Account', 'l' => 'Paid via', 'd' => 'Paid via {method}'),
        'order.shipping' => array('g' => 'Account', 'l' => 'Shipping heading', 'd' => 'Shipping'),
        'order.billing' => array('g' => 'Account', 'l' => 'Billing address', 'd' => 'Billing address'),
        'order.status_pending' => array('g' => 'Account', 'l' => 'Pending', 'd' => 'Pending'),
        'order.status_confirmed' => array('g' => 'Account', 'l' => 'Confirmed', 'd' => 'Confirmed'),
        'order.status_processing' => array('g' => 'Account', 'l' => 'Processing', 'd' => 'Processing'),
        'order.status_shipped' => array('g' => 'Account', 'l' => 'Shipped', 'd' => 'Shipped'),
        'order.status_dispatching' => array('g' => 'Account', 'l' => 'Dispatching', 'd' => 'Dispatching'),
        'order.status_delivered' => array('g' => 'Account', 'l' => 'Delivered', 'd' => 'Delivered'),
        'order.status_completed' => array('g' => 'Account', 'l' => 'Completed', 'd' => 'Completed'),
        'order.status_refund_requested' => array('g' => 'Account', 'l' => 'Refund requested', 'd' => 'Refund requested'),
        'order.status_refunded' => array('g' => 'Account', 'l' => 'Refunded', 'd' => 'Refunded'),
        'order.status_cancelled' => array('g' => 'Account', 'l' => 'Cancelled', 'd' => 'Cancelled'),
        'order.mail_customer_subject' => array('g' => 'Account', 'l' => 'Customer new order subject', 'd' => 'Your order {no} at {store}'),
        'order.mail_status_subject' => array('g' => 'Account', 'l' => 'Customer status subject', 'd' => 'Order {no} is {status}'),
        'order.mail_customer_intro' => array('g' => 'Account', 'l' => 'Customer new order intro', 'd' => 'Thank you for your order at {store}. We have received it and will start processing it shortly.'),
        'order.mail_status_intro' => array('g' => 'Account', 'l' => 'Customer status intro', 'd' => 'An update on your order {no} at {store}.'),
        'order.mail_item_subject' => array('g' => 'Account', 'l' => 'Customer item subject', 'd' => 'Order {no}: item update'),
        'order.mail_item_update' => array('g' => 'Account', 'l' => 'Customer item body', 'd' => 'In order {no}, {product} is now {status}.'),
        'order.mail_tracking' => array('g' => 'Account', 'l' => 'Tracking line', 'd' => 'Tracking: {company} {tracking}.'),
        'order.wa_new' => array('g' => 'Account', 'l' => 'Customer WhatsApp new order', 'd' => 'Your order {no} at {store} has been received. Status: {status}. Total: {total}. Items: {items}'),
        'order.wa_status' => array('g' => 'Account', 'l' => 'Customer WhatsApp status', 'd' => 'Your order {no} at {store} is now {status}. Total: {total}. Items: {items}'),
        'order.wa_item' => array('g' => 'Account', 'l' => 'Customer WhatsApp item', 'd' => 'In order {no} at {store}, {product} is now {status}. Qty: {qty}.'),
        'order.wa_tracking' => array('g' => 'Account', 'l' => 'Customer WhatsApp tracking', 'd' => 'Tracking: {company} {tracking}.'),
        'order.tracking' => array('g' => 'Account', 'l' => 'Tracking', 'd' => 'Tracking'),
        'order.track_processing' => array('g' => 'Account', 'l' => 'Track processing', 'd' => 'Processing'),
        'order.track_dispatch' => array('g' => 'Account', 'l' => 'Track dispatched', 'd' => 'Dispatched'),
        'order.track_complete' => array('g' => 'Account', 'l' => 'Track completed', 'd' => 'Completed'),

        'contact.title' => array('g' => 'Contact', 'l' => 'Contact heading', 'd' => 'Contact Us'),
        'contact.lead' => array('g' => 'Contact', 'l' => 'Contact lead', 'd' => 'Questions about an order, a product or a return? Send us a message and we will get back to you.'),
        'contact.full_name' => array('g' => 'Contact', 'l' => 'Full name', 'd' => 'Full name'),
        'contact.email' => array('g' => 'Contact', 'l' => 'Email address', 'd' => 'Email address'),
        'contact.order_no' => array('g' => 'Contact', 'l' => 'Order number', 'd' => 'Order number'),
        'contact.optional' => array('g' => 'Contact', 'l' => 'Optional hint', 'd' => '(optional)'),
        'contact.order_placeholder' => array('g' => 'Contact', 'l' => 'Order placeholder', 'd' => 'e.g. 1048372'),
        'contact.topic' => array('g' => 'Contact', 'l' => 'What is it about', 'd' => 'What is it about?'),
        'contact.topic_order' => array('g' => 'Contact', 'l' => 'Topic existing order', 'd' => 'An existing order'),
        'contact.topic_delivery' => array('g' => 'Contact', 'l' => 'Topic delivery', 'd' => 'Delivery & shipping'),
        'contact.topic_returns' => array('g' => 'Contact', 'l' => 'Topic returns', 'd' => 'Returns & refunds'),
        'contact.topic_product' => array('g' => 'Contact', 'l' => 'Topic product', 'd' => 'Product question'),
        'contact.topic_other' => array('g' => 'Contact', 'l' => 'Topic other', 'd' => 'Something else'),
        'contact.message' => array('g' => 'Contact', 'l' => 'Message', 'd' => 'Message'),
        'contact.send' => array('g' => 'Contact', 'l' => 'Send message', 'd' => 'Send Message'),
        'contact.note' => array('g' => 'Contact', 'l' => 'Form note', 'd' => 'We only use these details to reply to your enquiry.'),
        'contact.sent' => array('g' => 'Contact', 'l' => 'Message sent', 'd' => 'Thank you. We have received your message.'),
        'contact.invalid' => array('g' => 'Contact', 'l' => 'Validation error', 'd' => 'Please fill in your name, a valid email, and a message.'),
        'contact.no_email' => array('g' => 'Contact', 'l' => 'Missing store email', 'd' => 'This store has not configured a contact email yet.'),
        'contact.send_failed' => array('g' => 'Contact', 'l' => 'Send failed', 'd' => 'We could not send your message just now. Please try again, or use the email shown on this page if one is listed.'),
        'contact.privacy' => array('g' => 'Contact', 'l' => 'Privacy link', 'd' => 'Privacy Policy'),
        'contact.terms' => array('g' => 'Contact', 'l' => 'Terms link', 'd' => 'Terms of Service'),
        'contact.deletion' => array('g' => 'Contact', 'l' => 'Data deletion link', 'd' => 'Data deletion'),
        'contact.other_ways' => array('g' => 'Contact', 'l' => 'Other ways', 'd' => 'Other ways to reach us'),
        'contact.email_label' => array('g' => 'Contact', 'l' => 'Email label', 'd' => 'Email'),
        'contact.phone_label' => array('g' => 'Contact', 'l' => 'Phone label', 'd' => 'Phone'),
        'contact.address_label' => array('g' => 'Contact', 'l' => 'Address label', 'd' => 'Address'),
        'contact.meta' => array('g' => 'Contact', 'l' => 'Contact meta description', 'd' => 'Contact the {store} team using the form on this page. Verified email, phone, or address is shown when the store has configured it.'),
        'page.contact' => array('g' => 'Contact', 'l' => 'Contact page title', 'd' => 'Contact'),

        'footer.shop' => array('g' => 'Footer', 'l' => 'Shop column', 'd' => 'Shop'),
        'footer.all_products' => array('g' => 'Footer', 'l' => 'All products', 'd' => 'All Products'),
        'footer.service' => array('g' => 'Footer', 'l' => 'Customer service', 'd' => 'Customer Service'),
        'footer.contact' => array('g' => 'Footer', 'l' => 'Contact us', 'd' => 'Contact Us'),
        'footer.help' => array('g' => 'Footer', 'l' => 'Help center', 'd' => 'Help Center'),
        'footer.about' => array('g' => 'Footer', 'l' => 'About column', 'd' => 'About'),
        'footer.about_us' => array('g' => 'Footer', 'l' => 'About us', 'd' => 'About Us'),
        'footer.stay' => array('g' => 'Footer', 'l' => 'Stay in touch', 'd' => 'Stay in touch'),
        'footer.rights' => array('g' => 'Footer', 'l' => 'All rights reserved', 'd' => 'All rights reserved.'),
        'footer.tagline' => array('g' => 'Footer', 'l' => 'Footer tagline', 'd' => 'Shop smart · Live better'),
        'footer.about_default' => array('g' => 'Footer', 'l' => 'Default about text', 'd' => 'Your one-stop shop for quality products at the best prices. Shop smart, live better.'),

        'msg.out_of_stock' => array('g' => 'Messages', 'l' => 'Product out of stock', 'd' => 'This product is out of stock.'),
        'msg.no_more_stock' => array('g' => 'Messages', 'l' => 'No more stock', 'd' => 'No more stock available for this product.'),
        'msg.added_cart' => array('g' => 'Messages', 'l' => 'Added to cart', 'd' => 'Product added to cart.'),
        'msg.added_wish' => array('g' => 'Messages', 'l' => 'Added to wishlist', 'd' => 'Added to wishlist.'),
        'msg.removed_wish' => array('g' => 'Messages', 'l' => 'Removed from wishlist', 'd' => 'Removed from wishlist.'),
        'msg.login_review' => array('g' => 'Messages', 'l' => 'Login to review', 'd' => 'Please login to write a review.'),
        'msg.review_required' => array('g' => 'Messages', 'l' => 'Review required', 'd' => 'Please add your name and review.'),
        'msg.review_thanks' => array('g' => 'Messages', 'l' => 'Review thanks', 'd' => 'Thanks for your review. It will appear after approval.'),
        'msg.cart_updated' => array('g' => 'Messages', 'l' => 'Cart updated', 'd' => 'Cart updated.'),
        'msg.login_checkout' => array('g' => 'Messages', 'l' => 'Login to checkout', 'd' => 'Please login to checkout.'),
        'msg.cart_empty' => array('g' => 'Messages', 'l' => 'Cart is empty', 'd' => 'Your cart is empty.'),
        'msg.checkout_required' => array('g' => 'Messages', 'l' => 'Checkout fields required', 'd' => 'Name, email, WhatsApp number and address are required.'),
        'msg.complete_shipping' => array('g' => 'Messages', 'l' => 'Complete shipping first', 'd' => 'Please complete shipping details first.'),
        'msg.pay_expired' => array('g' => 'Messages', 'l' => 'Payment expired', 'd' => 'Payment session expired. Please checkout again.'),
        'msg.pay_card_read' => array('g' => 'Messages', 'l' => 'Card read failed', 'd' => 'Could not read card details securely. Please try again.'),
        'msg.pay_card_failed' => array('g' => 'Messages', 'l' => 'Card payment failed', 'd' => 'Card payment failed. You can try again.'),
        'msg.pay_card_incomplete' => array('g' => 'Messages', 'l' => 'Card not completed', 'd' => 'Card payment was not completed. You can try again.'),
        'msg.pay_declined' => array('g' => 'Messages', 'l' => 'Card declined', 'd' => 'Your card was declined. No payment was taken. Please try another card or PayPal.'),
        'msg.pay_insufficient_funds' => array('g' => 'Messages', 'l' => 'Insufficient funds', 'd' => 'The card was declined (insufficient funds). No payment was taken. Please try another card.'),
        'msg.pay_pending' => array('g' => 'Messages', 'l' => 'Payment pending', 'd' => 'Your payment is pending. The order is not marked as paid yet.'),
        'msg.pay_paypal_start' => array('g' => 'Messages', 'l' => 'PayPal start failed', 'd' => 'Unable to start PayPal checkout.'),
        'msg.pay_paypal_link' => array('g' => 'Messages', 'l' => 'PayPal link missing', 'd' => 'PayPal approval link missing.'),
        'msg.pay_expired_short' => array('g' => 'Messages', 'l' => 'Payment expired short', 'd' => 'Payment session expired.'),
        'msg.pay_paypal_ref' => array('g' => 'Messages', 'l' => 'PayPal reference missing', 'd' => 'Missing PayPal order reference.'),
        'msg.pay_paypal_incomplete' => array('g' => 'Messages', 'l' => 'PayPal not completed', 'd' => 'PayPal payment was not completed.'),
        'msg.pay_paypal_cancel' => array('g' => 'Messages', 'l' => 'PayPal cancelled', 'd' => 'PayPal payment was cancelled. You can try again.'),
        'msg.login_invalid' => array('g' => 'Messages', 'l' => 'Invalid login', 'd' => 'Invalid email or password.'),
        'msg.signup_required' => array('g' => 'Messages', 'l' => 'Signup required', 'd' => 'Name, email and password are required.'),
        'msg.email_taken' => array('g' => 'Messages', 'l' => 'Email taken', 'd' => 'This email is already registered for this store.'),
        'msg.profile_updated' => array('g' => 'Messages', 'l' => 'Profile updated', 'd' => 'Profile updated.'),
        'msg.order_missing' => array('g' => 'Messages', 'l' => 'Order not found', 'd' => 'Order not found.'),
    );
}

function storefront_ui_locale_strings($locale)
{
    $locale = strtolower(trim((string) $locale));
    if ($locale !== 'sv') {
        return array();
    }
    return array(
        'meta.skip' => 'Hoppa till innehåll',
        'nav.support' => 'Support',
        'nav.contact' => 'Kontakt',
        'nav.help' => 'Hjälp',
        'nav.back_products' => 'Tillbaka till produkter',
        'nav.open_menu' => 'Öppna meny',
        'nav.close_menu' => 'Stäng meny',
        'nav.search_label' => 'Sök produkter',
        'nav.search_placeholder' => 'Sök efter produkter...',
        'nav.search' => 'Sök',
        'nav.account' => 'Konto',
        'nav.cart' => 'Kundvagn',
        'nav.all_categories' => 'Alla kategorier',
        'nav.home' => 'Hem',
        'nav.shop' => 'Butik',
        'nav.deals' => 'Erbjudanden',
        'nav.main' => 'Huvudmeny',
        'nav.categories' => 'Kategorier',
        'nav.home_aria' => '{store} startsida',
        'nav.breadcrumb' => 'Sökväg',
        'home.featured_promos' => 'Utvalda kampanjer',
        'home.prev_slide' => 'Föregående bild',
        'home.next_slide' => 'Nästa bild',
        'home.shop_by_category' => 'Handla efter kategori',
        'home.featured' => 'Utvalda produkter',
        'home.handpicked' => 'Handplockat för {store}',
        'home.view_all' => 'Visa alla produkter',
        'home.no_products' => 'Inga produkter finns ännu.',
        'home.why_shop' => 'Därför ska du handla hos oss',
        'home.seasonal' => 'Säsongens butiker',
        'home.kicker_living' => 'Modernt hem',
        'home.kicker_season' => 'Ny säsong',
        'home.title_fresh' => "Nya favoriter\nFör dig",
        'home.text_fresh' => 'Upptäck de senaste produkterna utvalda för {store}.',
        'home.kicker_value' => 'Bästa värde',
        'home.title_smart' => "Handla smart\nLev bättre",
        'home.text_smart' => 'Kvalitetsprodukter till priser du gillar.',
        'home.disc_upto' => 'UPP TILL',
        'home.disc_off' => 'RABATT',
        'home.seasonal_alt' => 'Säsongskampanj',
        'home.festive_alt' => 'Festkampanj',
        'cta.shop_now' => 'Handla nu',
        'cta.browse_shop' => 'Utforska butiken',
        'cta.shop_deals' => 'Se erbjudanden',
        'cta.browse_gifts' => 'Se presenter',
        'home.explore' => 'Utforska',
        'home.top_picks' => 'Toppval',
        'home.top_picks_text' => 'Utvalda favoriter och mer',
        'home.gifts_kicker' => 'Gör det extra',
        'home.gifts' => 'Presenttips',
        'home.gifts_text' => 'Fynd som hela familjen gillar',
        'trust.free_shipping' => 'Fri frakt',
        'trust.free_shipping_text' => 'På kvalificerade ordrar',
        'trust.secure' => 'Säker betalning',
        'trust.secure_text' => '100 % säker kassa',
        'trust.returns' => 'Enkla returer',
        'trust.returns_text' => 'Enkel returpolicy',
        'trust.support' => 'Support dygnet runt',
        'trust.support_text' => 'Vi finns här för att hjälpa',
        'trust.free_delivery' => 'Fri leverans',
        'trust.delivery' => 'Leverans',
        'trust.flat_rate' => 'Fast pris {amount} per vara',
        'trust.free_from' => 'Fri frakt från {amount}',
        'trust.tracked' => 'Spårbar frakt finns',
        'trust.returns_simple' => 'Enkel returprocess',
        'trust.secure_checkout' => 'Säker kassa',
        'trust.protected' => 'Skyddade betalningar',
        'trust.customer_support' => 'Kundsupport',
        'trust.help' => 'Vi finns här för att hjälpa',
        'shop.category' => 'Kategori',
        'shop.everything' => 'Allt på ett ställe',
        'shop.all_products' => 'Alla produkter',
        'shop.browse_range' => 'Utforska hela {store}-sortimentet.',
        'shop.start_browsing' => 'Börja handla',
        'shop.filters' => 'Produktfilter',
        'shop.filter_by' => 'Filtrera',
        'shop.clear_all' => 'Rensa allt',
        'shop.everything_option' => 'Allt',
        'shop.price_range' => 'Prisintervall',
        'shop.min' => 'Min',
        'shop.max' => 'Max',
        'shop.availability' => 'Tillgänglighet',
        'shop.in_stock' => 'I lager',
        'shop.on_sale' => 'På rea',
        'shop.search_sort' => 'Sök / Sortera',
        'shop.search_products' => 'Sök produkter...',
        'shop.sort_newest' => 'Nyast',
        'shop.sort_price_asc' => 'Pris: Lågt till högt',
        'shop.sort_price_desc' => 'Pris: Högt till lågt',
        'shop.sort_name' => 'Namn A–Ö',
        'shop.apply' => 'Använd filter',
        'shop.products' => 'Produkter',
        'shop.product_one' => '{n} produkt',
        'shop.product_many' => '{n} produkter',
        'shop.item_one' => '{n} artikel',
        'shop.item_many' => '{n} artiklar',
        'shop.no_match' => 'Inga produkter matchar filtren.',
        'shop.no_category' => 'Inga produkter i den här kategorin ännu.',
        'shop.subcats' => 'Underkategorier',
        'shop.shop_subcat' => 'Handla efter underkategori',
        'shop.browse_within' => 'Bläddra i {name}',
        'shop.category_products' => '{name} produkter',
        'shop.loading_more' => 'Laddar mer…',
        'page.shop' => 'Butik',
        'product.add_cart' => 'Lägg i kundvagn',
        'product.buy_now' => 'Köp nu',
        'product.in_stock' => 'I lager',
        'product.out_of_stock' => 'Slut i lager',
        'product.in_stock_qty' => 'I lager: {n}',
        'product.sku' => 'Artikelnummer',
        'product.bestseller' => 'Bästsäljare',
        'product.review_one' => '{n} recension',
        'product.review_many' => '{n} recensioner',
        'product.qty' => 'Antal',
        'product.qty_down' => 'Minska antal',
        'product.qty_up' => 'Öka antal',
        'product.add_wishlist' => 'Lägg till i önskelista',
        'product.in_wishlist' => 'I önskelistan',
        'product.share' => 'Dela produkt',
        'product.tab_desc' => 'Beskrivning',
        'product.tab_specs' => 'Specifikationer',
        'product.tab_reviews' => 'Recensioner',
        'product.tab_faqs' => 'Vanliga frågor',
        'product.desc_title' => 'Produktbeskrivning',
        'product.desc_empty' => 'Information om den här produkten visas här.',
        'product.video' => 'Produktvideo',
        'product.show_more' => 'Visa mer',
        'product.show_less' => 'Visa mindre',
        'product.offer_save' => 'Du sparar {amount}',
        'product.offer_ends' => 'Erbjudandet slutar om:',
        'product.offer_limited' => 'Tidsbegränsat erbjudande',
        'product.offer_free_shipping' => '🚚 FRI FRAKT vid erbjudande',
        'product.free_shipping' => '🚚 FRI FRAKT',
        'product.offer_card_title' => 'Aktuellt erbjudande',
        'product.offer_card_qty' => '{n} produkt',
        'product.offer_card_qty_many' => '{n} produkter',
        'product.offer_card_off' => '{n}% rabatt',
        'product.offer_card_off_short' => 'RABATT',
        'product.offer_card_save' => 'Spara {amount}',
        'product.offer_card_save_short' => 'SPARA',
        'product.offer_card_bxgy' => 'Köp {buy} få {get}',
        'product.offer_card_bxgy_short' => 'ERBJUDANDE',
        'product.offer_card_bundle' => 'Köp {qty} för {amount}',
        'product.offer_card_bundle_short' => 'PAKET',
        'product.offer_card_claim' => 'Lägg i kundvagn & kassa',
        'product.offer_cards_title' => 'Välj erbjudande',
        'product.offer_card_bogo' => 'Köp {qty} få 1 gratis',
        'product.offer_card_bogo_short' => 'GRATIS',
        'product.no_specs' => 'Inga specifikationer har lagts till för den här produkten ännu.',
        'product.reviews_title' => 'Kundrecensioner',
        'product.reviews_based' => 'Baserat på {n} recension',
        'product.reviews_based_many' => 'Baserat på {n} recensioner',
        'product.write_review' => 'Skriv en recension',
        'product.your_name' => 'Ditt namn',
        'product.rating' => 'Betyg',
        'product.star_one' => '{n} stjärna',
        'product.star_many' => '{n} stjärnor',
        'product.review_title' => 'Rubrik',
        'product.review_body' => 'Recension',
        'product.submit_review' => 'Skicka recension',
        'product.no_reviews' => 'Inga recensioner ännu.',
        'product.review_sample' => 'Exempelrecension',
        'product.reviews_sample_note' => 'Exempelrecensioner är demonstrationsinnehåll, inte verifierade kundköp.',
        'product.faqs_title' => 'Vanliga frågor',
        'product.no_faqs' => 'Inga vanliga frågor har lagts till för den här produkten ännu.',
        'product.related' => 'Liknande produkter',
        'product.trending' => '🔥 Trendande val',
        'product.view_all' => 'Visa alla →',
        'product.prev' => 'Föregående produkter',
        'product.next' => 'Nästa produkter',
        'product.also_like' => 'Du kanske också gillar',
        'product.details' => 'Detaljer',
        'product.category' => 'Kategori',
        'product.subcategory' => 'Underkategori',
        'product.made_by' => 'Tillverkad av',
        'product.choose_delivery' => 'Välj leverans',
        'product.choose_pack' => 'Välj paket',
        'product.options' => 'Produktval',
        'product.arrives' => 'Anländer {dates}',
        'product.watch_video' => 'Titta på video',
        'product.prev_image' => 'Föregående bild',
        'product.next_image' => 'Nästa bild',
        'product.zoom' => 'Zooma bild',
        'product.show_image' => 'Visa bild {n}',
        'product.delivery' => 'Leverans',
        'product.eta' => 'Beräknad leverans: {dates}',
        'product.eta_working' => 'Beräknad leveranstid: {n} dagar',
        'product.eta_working_one' => 'Beräknad leveranstid: {n} dag',
        'product.eta_working_range' => 'Beräknad leveranstid: {min}–{max} dagar',
        'product.eta_vary' => 'Leveranstiden kan variera beroende på fraktmetod och destination.',
        'product.arrive_on' => 'Den här produkten anländer {date}.',
        'product.arrive_between' => 'Den här produkten anländer mellan {from} och {to}.',
        'product.fallback_desc' => 'Utvald för ZENVello-kollektionen.',
        'cart.delivery_eta' => 'Beräknad leveranstid: {min}–{max} dagar',
        'month.1' => 'jan',
        'month.2' => 'feb',
        'month.3' => 'mar',
        'month.4' => 'apr',
        'month.5' => 'maj',
        'month.6' => 'jun',
        'month.7' => 'jul',
        'month.8' => 'aug',
        'month.9' => 'sep',
        'month.10' => 'okt',
        'month.11' => 'nov',
        'month.12' => 'dec',
        'cart.title' => 'Din kundvagn',
        'cart.ready' => '{n} artikel redo för kassan.',
        'cart.ready_many' => '{n} artiklar redo för kassan.',
        'cart.empty_lead' => 'Lägg till något du gillar — det visas här.',
        'cart.empty' => 'Din kundvagn är tom',
        'cart.empty_text' => 'Utforska butiken och lägg till varor när du är redo.',
        'cart.continue' => 'Fortsätt handla',
        'cart.each' => '{amount} styck',
        'cart.remove' => 'Ta bort',
        'cart.update' => 'Uppdatera antal',
        'cart.summary' => 'Ordersammanfattning',
        'cart.subtotal' => 'Delsumma',
        'cart.subtotal_before' => 'Delsumma',
        'cart.discount' => 'Rabatt',
        'cart.original' => 'Ordinarie: {amount}',
        'cart.offer_off' => 'Erbjudande: −{amount}',
        'cart.shipping' => 'Frakt',
        'cart.shipping_qty' => 'Frakt × {n}',
        'cart.shipping_free' => 'Gratis',
        'cart.shipping_free_from' => 'Fri frakt från {amount}',
        'cart.vat' => 'Moms ({percent}%)',
        'cart.total' => 'Totalt',
        'cart.checkout' => 'Till kassan',
        'cart.go_shop' => 'Gå till butiken',
        'cart.edit' => 'Ändra kundvagn',
        'cart.step' => 'Kundvagn',
        'page.cart' => 'Kundvagn',
        'checkout.title' => 'Kassa',
        'checkout.lead' => 'Leveransuppgifter för {store}.',
        'checkout.shipping' => 'Leveransuppgifter',
        'checkout.full_name' => 'Fullständigt namn',
        'checkout.first_name' => 'Förnamn',
        'checkout.last_name' => 'Efternamn',
        'checkout.email' => 'E-post',
        'checkout.phone' => 'Telefon',
        'checkout.whatsapp' => 'WhatsApp-nummer',
        'checkout.whatsapp_hint' => 'Krävs för orderuppdateringar. Använd landskod utan +.',
        'checkout.address' => 'Adress',
        'checkout.street' => 'Gatuadress',
        'checkout.apartment' => 'Lägenhet / portkod (valfritt)',
        'checkout.postcode' => 'Postnummer',
        'checkout.city' => 'Ort',
        'checkout.country' => 'Land',
        'checkout.billing_same' => 'Fakturaadress samma som leverans',
        'checkout.billing' => 'Fakturaadress',
        'checkout.shipping_method' => 'Fraktsätt',
        'checkout.shipping_standard' => 'Standardleverans',
        'checkout.terms' => 'Jag godkänner villkoren',
        'checkout.continue' => 'Fortsätt till betalning',
        'checkout.step_shipping' => 'Leverans',
        'checkout.step_payment' => 'Betalning',
        'checkout.progress' => 'Kassans steg',
        'page.checkout' => 'Kassa',
        'pay.title' => 'Betalning',
        'pay.lead' => 'Krypterad kassa — kortuppgifter skyddas i din webbläsare innan de lämnar sidan.',
        'pay.card' => 'Betala med kort',
        'pay.paypal' => 'PayPal',
        'pay.name_on_card' => 'Namn på kortet',
        'pay.name_placeholder' => 'Namn som det står på kortet',
        'pay.card_number' => 'Kortnummer',
        'pay.encrypted' => 'Krypterat',
        'pay.expiry' => 'Utgångsdatum',
        'pay.cvv' => 'CVV',
        'pay.note' => 'Kortuppgifter lagras aldrig i klartext hos oss. De RSA-krypteras i webbläsaren och debiteras via PayPal.',
        'pay.pay_amount' => 'Betala {amount}',
        'pay.paypal_text' => 'Fortsätt med ditt PayPal-konto. Du bekräftar betalningen på PayPals säkra sida och kommer sedan tillbaka hit.',
        'pay.paypal_btn' => 'Fortsätt med PayPal',
        'pay.shipping_to' => 'Levereras till',
        'pay.edit_shipping' => 'Ändra leverans',
        'page.payment' => 'Säker betalning',
        'pay.method_card' => 'Kort',
        'pay.method_paypal' => 'PayPal',
        'thanks.title' => 'Tack',
        'thanks.placed' => 'Order {order} har lagts.',
        'thanks.total_paid' => 'Betalt totalt',
        'thanks.via' => 'via {method}',
        'thanks.email' => 'Ett bekräftelsemejl är på väg. Du kan fortsätta handla under tiden.',
        'thanks.ok' => 'Din order har lagts.',
        'thanks.view_order' => 'Visa order',
        'page.thanks' => 'Tack',
        'auth.login' => 'Logga in',
        'auth.login_lead' => 'Logga in på ditt {store}-konto.',
        'auth.password' => 'Lösenord',
        'auth.login_btn' => 'Logga in',
        'auth.new_here' => 'Ny här?',
        'auth.guest_checkout' => 'Fortsätt som gäst',
        'auth.guest_note' => 'Inget konto än? Fortsätt som gäst. Efter en lyckad order skapar vi ett konto och mejlar inloggningen.',
        'mail.account_subject' => 'Ditt konto hos {store}',
        'mail.account_intro' => 'Din order {order} är lagd. Vi har skapat ett konto så att du kan följa den.',
        'mail.account_password' => 'Lösenord',
        'mail.account_login' => 'Logga in',
        'auth.create_account' => 'Skapa ett konto',
        'auth.signup' => 'Registrera dig',
        'auth.signup_lead' => 'Skapa ditt {store}-konto.',
        'auth.create_btn' => 'Skapa konto',
        'auth.have_account' => 'Har du redan ett konto?',
        'auth.account' => 'Mitt konto',
        'auth.welcome' => 'Välkommen tillbaka, {name}.',
        'auth.account_lead' => 'Hantera din profil och dina ordrar.',
        'auth.edit_profile' => 'Redigera profil',
        'auth.orders' => 'Ordrar',
        'auth.orders_all' => 'Alla',
        'auth.order_no' => 'Order',
        'auth.product' => 'Produkt',
        'auth.qty' => 'Antal',
        'auth.no_orders' => 'Inga ordrar ännu',
        'auth.no_orders_text' => 'När du lägger en order visas den här.',
        'auth.no_status_orders' => 'Inga produkter med den här statusen ännu.',
        'auth.start_shopping' => 'Börja handla',
        'auth.view' => 'Visa',
        'auth.profile' => 'Profil',
        'auth.new_password' => 'Nytt lösenord',
        'auth.password_keep' => 'Lämna tomt för att behålla nuvarande lösenord',
        'auth.save_profile' => 'Spara profil',
        'auth.logout' => 'Logga ut',
        'auth.back' => '← Tillbaka till kontot',
        'page.login' => 'Logga in',
        'page.signup' => 'Registrera',
        'page.account' => 'Konto',
        'page.order' => 'Order {order}',
        'order.heading' => 'Order {order}',
        'order.placed' => 'Lagd {date}',
        'order.items' => 'Artiklar',
        'order.qty_each' => 'Antal {n} · {amount} styck',
        'order.status' => 'Status',
        'order.is_status' => 'Den här ordern är {status}.',
        'order.paid_via' => 'Betald via {method}',
        'order.shipping' => 'Leverans',
        'order.billing' => 'Fakturaadress',
        'order.status_pending' => 'Väntande',
        'order.status_confirmed' => 'Bekräftad',
        'order.status_processing' => 'Behandlas',
        'order.status_shipped' => 'Skickad',
        'order.status_dispatching' => 'Packas',
        'order.status_delivered' => 'Levererad',
        'order.status_completed' => 'Slutförd',
        'order.status_refund_requested' => 'Återbetalning begärd',
        'order.status_refunded' => 'Återbetald',
        'order.status_cancelled' => 'Avbruten',
        'order.mail_customer_subject' => 'Din order {no} hos {store}',
        'order.mail_status_subject' => 'Order {no} är {status}',
        'order.mail_customer_intro' => 'Tack för din order hos {store}. Vi har tagit emot den och börjar behandla den inom kort.',
        'order.mail_status_intro' => 'En uppdatering om din order {no} hos {store}.',
        'order.mail_item_subject' => 'Order {no}: artikeluppdatering',
        'order.mail_item_update' => 'I order {no} är {product} nu {status}.',
        'order.mail_tracking' => 'Spårning: {company} {tracking}.',
        'order.wa_new' => 'Din order {no} hos {store} har tagits emot. Status: {status}. Totalt: {total}. Artiklar: {items}',
        'order.wa_status' => 'Din order {no} hos {store} är nu {status}. Totalt: {total}. Artiklar: {items}',
        'order.wa_item' => 'I order {no} hos {store} är {product} nu {status}. Antal: {qty}.',
        'order.wa_tracking' => 'Spårning: {company} {tracking}.',
        'order.tracking' => 'Spårning',
        'order.track_processing' => 'Behandlas',
        'order.track_dispatch' => 'Skickad',
        'order.track_complete' => 'Slutförd',
        'contact.title' => 'Kontakta oss',
        'contact.lead' => 'Frågor om en order, en produkt eller en retur? Skicka ett meddelande så återkommer vi.',
        'contact.full_name' => 'Fullständigt namn',
        'contact.email' => 'E-postadress',
        'contact.order_no' => 'Ordernummer',
        'contact.optional' => '(valfritt)',
        'contact.order_placeholder' => 't.ex. 1048372',
        'contact.topic' => 'Vad gäller det?',
        'contact.topic_order' => 'En befintlig order',
        'contact.topic_delivery' => 'Leverans & frakt',
        'contact.topic_returns' => 'Returer & återbetalning',
        'contact.topic_product' => 'Produktfråga',
        'contact.topic_other' => 'Något annat',
        'contact.message' => 'Meddelande',
        'contact.send' => 'Skicka meddelande',
        'contact.note' => 'Vi använder bara dessa uppgifter för att svara på din förfrågan.',
        'contact.sent' => 'Tack. Vi har tagit emot ditt meddelande.',
        'contact.invalid' => 'Fyll i namn, en giltig e-postadress och ett meddelande.',
        'contact.no_email' => 'Butiken har inte angett någon kontakt-e-post ännu.',
        'contact.send_failed' => 'Meddelandet kunde inte skickas just nu. Försök igen, eller använd e-postadressen på sidan om den visas.',
        'contact.privacy' => 'Integritetspolicy',
        'contact.terms' => 'Användarvillkor',
        'contact.deletion' => 'Radering av data',
        'contact.other_ways' => 'Andra sätt att nå oss',
        'contact.email_label' => 'E-post',
        'contact.phone_label' => 'Telefon',
        'contact.address_label' => 'Adress',
        'contact.meta' => 'Kontakta {store} via formuläret på den här sidan. E-post, telefon eller adress visas om butiken har angett dem.',
        'page.contact' => 'Kontakt',
        'footer.shop' => 'Butik',
        'footer.all_products' => 'Alla produkter',
        'footer.service' => 'Kundservice',
        'footer.contact' => 'Kontakta oss',
        'footer.help' => 'Hjälpcenter',
        'footer.about' => 'Om oss',
        'footer.about_us' => 'Om oss',
        'footer.stay' => 'Håll kontakten',
        'footer.rights' => 'Alla rättigheter förbehållna.',
        'footer.tagline' => 'Handla smart · Lev bättre',
        'footer.about_default' => 'Din butik för kvalitetsprodukter till bra priser. Handla smart, lev bättre.',
        'msg.out_of_stock' => 'Den här produkten är slut i lager.',
        'msg.no_more_stock' => 'Det finns inte mer i lager av den här produkten.',
        'msg.added_cart' => 'Produkten har lagts i kundvagnen.',
        'msg.added_wish' => 'Tillagd i önskelistan.',
        'msg.removed_wish' => 'Borttagen från önskelistan.',
        'msg.login_review' => 'Logga in för att skriva en recension.',
        'msg.review_required' => 'Ange ditt namn och din recension.',
        'msg.review_thanks' => 'Tack för din recension. Den visas efter godkännande.',
        'msg.cart_updated' => 'Kundvagnen har uppdaterats.',
        'msg.login_checkout' => 'Logga in för att gå till kassan.',
        'msg.cart_empty' => 'Din kundvagn är tom.',
        'msg.checkout_required' => 'Namn, e-post, WhatsApp-nummer och adress krävs.',
        'msg.complete_shipping' => 'Fyll i leveransuppgifterna först.',
        'msg.pay_expired' => 'Betalningssessionen har gått ut. Gå till kassan igen.',
        'msg.pay_card_read' => 'Kortuppgifterna kunde inte läsas säkert. Försök igen.',
        'msg.pay_card_failed' => 'Kortbetalningen misslyckades. Du kan försöka igen.',
        'msg.pay_card_incomplete' => 'Kortbetalningen slutfördes inte. Du kan försöka igen.',
        'msg.pay_declined' => 'Kortet avvisades. Ingen betalning togs. Försök med ett annat kort eller PayPal.',
        'msg.pay_insufficient_funds' => 'Kortet avvisades (otillräckligt saldo). Ingen betalning togs. Försök med ett annat kort.',
        'msg.pay_pending' => 'Betalningen väntar. Ordern är inte markerad som betald ännu.',
        'msg.pay_paypal_start' => 'Kunde inte starta PayPal-kassan.',
        'msg.pay_paypal_link' => 'PayPal-länken saknas.',
        'msg.pay_expired_short' => 'Betalningssessionen har gått ut.',
        'msg.pay_paypal_ref' => 'PayPal-orderreferens saknas.',
        'msg.pay_paypal_incomplete' => 'PayPal-betalningen slutfördes inte.',
        'msg.pay_paypal_cancel' => 'PayPal-betalningen avbröts. Du kan försöka igen.',
        'msg.login_invalid' => 'Fel e-post eller lösenord.',
        'msg.signup_required' => 'Namn, e-post och lösenord krävs.',
        'msg.email_taken' => 'Den här e-postadressen är redan registrerad i den här butiken.',
        'msg.profile_updated' => 'Profilen har uppdaterats.',
        'msg.order_missing' => 'Ordern hittades inte.',
    );
}

function storefront_ui_marketing_defaults($locale)
{
    if (strtolower((string) $locale) !== 'sv') {
        return array();
    }
    return array(
        'promo_text' => 'Fri frakt på ordrar över 500 kr',
        'footer_about' => 'Handla smart, lev bättre med ZENVello Sweden.',
        'footer_text' => 'ZENVello Sweden. Alla rättigheter förbehållna.',
        'hero_title' => "Gör ditt hem\ntill ditt",
        'hero_subtitle' => 'Upptäck smarta, snygga och prisvärda produkter för en bättre vardag.',
        'banner_1_kicker' => 'Utforska',
        'banner_1_title' => 'Toppval',
        'banner_1_text' => 'Utvalda favoriter och mer',
        'banner_1_btn_text' => 'Handla nu',
        'banner_2_kicker' => 'Gör det extra',
        'banner_2_title' => 'Presenttips',
        'banner_2_text' => 'Fynd som hela familjen gillar',
        'banner_2_btn_text' => 'Se presenter',
    );
}

function storefront_ui_context()
{
    $settings = array();
    $store = null;
    if (function_exists('get_instance')) {
        $CI =& get_instance();
        if ($CI && isset($CI->tenant) && is_object($CI->tenant)) {
            if (method_exists($CI->tenant, 'get_settings')) {
                $settings = $CI->tenant->get_settings();
            }
            if (method_exists($CI->tenant, 'get_store')) {
                $store = $CI->tenant->get_store();
            }
        }
    }
    return array($store, is_array($settings) ? $settings : array());
}

function storefront_native_locale($store = null, $settings = null)
{
    if ($store === null || $settings === null) {
        list($ctxStore, $ctxSettings) = storefront_ui_context();
        if ($store === null) {
            $store = $ctxStore;
        }
        if ($settings === null) {
            $settings = $ctxSettings;
        }
    }
    $forced = '';
    if (is_array($settings) && isset($settings['ui.locale'])) {
        $forced = strtolower(trim((string) $settings['ui.locale']));
    }
    if (in_array($forced, array('sv', 'en'), true)) {
        return $forced;
    }
    if ($store && !empty($store->language)) {
        $lang = strtolower(trim((string) $store->language));
        if (isset(storefront_language_labels()[$lang])) {
            return $lang;
        }
    }
    $code = '';
    if ($store && !empty($store->country_code)) {
        $code = strtoupper((string) $store->country_code);
    }
    if ($code === 'SE') {
        return 'sv';
    }
    if ($store && !empty($store->domain) && preg_match('/\.se$/i', $store->domain)) {
        return 'sv';
    }
    return 'en';
}

function storefront_language_labels()
{
    return array(
        'sv' => 'Svenska',
        'en' => 'English',
        'de' => 'Deutsch',
        'fr' => 'Français',
        'es' => 'Español',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'nb' => 'Norsk',
        'da' => 'Dansk',
        'fi' => 'Suomi',
        'pl' => 'Polski',
        'pt' => 'Português',
    );
}

function storefront_visitor_locale()
{
    $allowed = array_keys(storefront_language_labels());
    $get = isset($_GET['lang']) ? strtolower(trim((string) $_GET['lang'])) : '';
    if (in_array($get, $allowed, true)) {
        return $get;
    }
    if (!empty($_COOKIE['ec_lang'])) {
        $cookie = strtolower(trim((string) $_COOKIE['ec_lang']));
        if (in_array($cookie, $allowed, true)) {
            return $cookie;
        }
    }
    return '';
}

function storefront_ui_locale($store = null, $settings = null)
{
    $native = storefront_native_locale($store, $settings);
    $visitor = storefront_visitor_locale();
    if ($visitor === 'en') {
        return 'en';
    }
    if ($visitor !== '' && $visitor === $native) {
        return $native;
    }
    return $native;
}

function storefront_html_lang()
{
    $locale = storefront_ui_locale();
    return $locale !== '' ? $locale : 'en';
}

function storefront_lang_url($code)
{
    return storefront_url('lang/' . $code);
}

function storefront_language_switcher_html()
{
    $native = storefront_native_locale();
    if ($native === 'en') {
        return '';
    }
    $current = storefront_ui_locale();
    $labels = storefront_language_labels();
    $nativeLabel = isset($labels[$native]) ? $labels[$native] : strtoupper($native);
    $items = array(
        array('code' => $native, 'label' => $nativeLabel, 'short' => strtoupper($native)),
        array('code' => 'en', 'label' => 'English', 'short' => 'EN'),
    );
    $html = '<nav class="lang-switch" aria-label="Language">';
    foreach ($items as $i => $item) {
        if ($i > 0) {
            $html .= '<span class="lang-switch__sep" aria-hidden="true">|</span>';
        }
        $active = $current === $item['code'] ? ' is-active' : '';
        $html .= '<a class="lang-switch__link' . $active . '" href="' . htmlspecialchars(storefront_lang_url($item['code']), ENT_QUOTES, 'UTF-8') . '"'
            . ($active ? ' aria-current="true"' : '') . '>'
            . htmlspecialchars($item['short'], ENT_QUOTES, 'UTF-8')
            . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

function store_ui($key, $replace = array())
{
    $catalog = storefront_ui_catalog();
    $english = isset($catalog[$key]) ? $catalog[$key]['d'] : $key;
    list($store, $settings) = storefront_ui_context();
    $saved = '';
    if (isset($settings['ui.' . $key])) {
        $saved = trim((string) $settings['ui.' . $key]);
    }
    $locale = storefront_ui_locale($store, $settings);
    $native = storefront_native_locale($store, $settings);
    $localized = storefront_ui_locale_strings($locale);
    if ($saved !== '' && $locale === $native) {
        $value = $saved;
    } elseif (isset($localized[$key])) {
        $value = $localized[$key];
    } else {
        $value = $english;
    }
    if ($store && strpos($value, '{store}') !== false) {
        $replace['{store}'] = isset($replace['{store}']) ? $replace['{store}'] : $store->name;
    }
    if (!empty($replace)) {
        $value = strtr($value, $replace);
    }
    return $value;
}

function e_ui($key, $replace = array())
{
    return htmlspecialchars(store_ui($key, $replace), ENT_QUOTES, 'UTF-8');
}

function storefront_ui_count($oneKey, $manyKey, $n)
{
    $n = (int) $n;
    return store_ui($n === 1 ? $oneKey : $manyKey, array('{n}' => $n));
}

function storefront_ui_js_map()
{
    $keys = array(
        'product.in_stock',
        'product.out_of_stock',
        'product.add_wishlist',
        'product.in_wishlist',
        'product.show_more',
        'product.show_less',
        'shop.loading_more',
    );
    $out = array();
    foreach ($keys as $key) {
        $out[$key] = store_ui($key);
    }
    return $out;
}

function storefront_date_label($dt, $withYear = false)
{
    if (!($dt instanceof DateTimeInterface)) {
        try {
            $dt = new DateTime((string) $dt);
        } catch (Exception $e) {
            return '';
        }
    }
    $month = store_ui('month.' . (int) $dt->format('n'));
    $label = ((int) $dt->format('j')) . ' ' . $month;
    if ($withYear) {
        $label .= ' ' . $dt->format('Y');
    }
    return $label;
}

function storefront_ui_groups()
{
    $groups = array();
    foreach (storefront_ui_catalog() as $key => $meta) {
        $group = isset($meta['g']) ? $meta['g'] : 'Other';
        if (!isset($groups[$group])) {
            $groups[$group] = array();
        }
        $groups[$group][$key] = $meta;
    }
    return $groups;
}

function storefront_ui_seed_store($storeId, $locale = 'sv')
{
    $CI =& get_instance();
    $CI->load->model('Store_settings_model');
    $locale = strtolower((string) $locale) === 'sv' ? 'sv' : 'en';
    $pairs = array('ui.locale' => $locale);
    $catalog = storefront_ui_catalog();
    $localized = storefront_ui_locale_strings($locale);
    foreach ($catalog as $key => $meta) {
        $pairs['ui.' . $key] = isset($localized[$key]) ? $localized[$key] : $meta['d'];
    }
    if ($locale === 'sv') {
        $pairs = array_merge($pairs, storefront_ui_marketing_defaults('sv'));
    }
    $CI->Store_settings_model->setMany((int) $storeId, $pairs);
    if ($CI->db->field_exists('language', 'stores')) {
        $CI->db->where('id', (int) $storeId)->update('stores', array('language' => $locale));
    }
    return $pairs;
}
