<?php

defined('BASEPATH') OR exit('No direct script access allowed');



/*

| -------------------------------------------------------------------------

| URI ROUTING

| -------------------------------------------------------------------------

| This file lets you re-map URI requests to specific controller functions.

|

| Typically there is a one-to-one relationship between a URL string

| and its corresponding controller class/method. The segments in a

| URL normally follow this pattern: 

|

|	example.com/class/method/id/

|

| In some instances, however, you may want to remap this relationship

| so that a different class/function is called than the one

| corresponding to the URL.

|

| Please see the user guide for complete details:

|

|	https://codeigniter.com/user_guide/general/routing.html

|

| -------------------------------------------------------------------------

| RESERVED ROUTES

| -------------------------------------------------------------------------

|

| There are three reserved routes:

|

|	$route['default_controller'] = 'welcome';

|

| This route indicates which controller class should be loaded if the

| URI contains no data. In the above example, the "welcome" class

| would be loaded.

|

|	$route['404_override'] = 'errors/page_missing';

|

| This route will tell the Router which controller/method to use if those

| provided in the URL cannot be matched to a valid route.

|

|	$route['translate_uri_dashes'] = FALSE;

|

| This is not exactly a route, but allows you to automatically route

| controller and method names that contain dashes. '-' isn't a valid

| class or method name character, so it requires translation.

| When you set this option to TRUE, it will replace ALL dashes in the

| controller and method URI segments.

|

| Examples:	my-controller/index	-> my_controller/index

|		my-controller/my-method	-> my_controller/my_method

*/

$host = isset($_SERVER['HTTP_HOST']) ? strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : '';
if ($host !== '' && !in_array($host, array('localhost', '127.0.0.1'), true)) {
	$route['default_controller'] = 'shop';
} else {
	$route['default_controller'] = 'login';
}

$route['course'] = 'index/course';

$route['404_override'] = '';

$route['translate_uri_dashes'] = FALSE;

// Store tenant panel (Phase 2)
$route['store'] = 'store/dashboard/index';
$route['store/login'] = 'store/auth/login';
$route['store/logout'] = 'store/auth/logout';
$route['store/forgot-password'] = 'store/auth/forgot_password';
$route['store/reset-password/(:any)'] = 'store/auth/reset_password/$1';
$route['store/dashboard'] = 'store/dashboard/index';
$route['store/products'] = 'store/products/index';
$route['store/my-products'] = 'store/products/mine';
$route['store/products/add/(:num)'] = 'store/products/add/$1';
$route['store/products/form'] = 'store/products/form';
$route['store/products/form/(:num)'] = 'store/products/form/$1';
$route['store/products/save'] = 'store/products/save';
$route['store/products/save/(:num)'] = 'store/products/save/$1';
$route['store/products/delete/(:num)'] = 'store/products/delete/$1';
$route['store/products/delete-image/(:num)'] = 'store/products/delete_image/$1';
$route['store/categories'] = 'store/categories/index';
$route['store/categories/edit/(:num)'] = 'store/categories/edit/$1';
$route['store/homepage-hero'] = 'store/homepage_hero/index';
$route['store/homepage-hero/form'] = 'store/homepage_hero/form';
$route['store/homepage-hero/form/(:num)'] = 'store/homepage_hero/form/$1';
$route['store/homepage-hero/delete/(:num)'] = 'store/homepage_hero/delete/$1';
$route['store/theme-settings'] = 'store/theme_settings/index';
$route['store/theme-settings/save'] = 'store/theme_settings/save';
$route['store/theme-settings/slider'] = 'store/theme_settings/slider';
$route['store/theme-settings/slide'] = 'store/theme_settings/slide';
$route['store/theme-settings/slide/(:num)'] = 'store/theme_settings/slide/$1';
$route['store/theme-settings/slide-delete/(:num)'] = 'store/theme_settings/slide_delete/$1';
$route['store/orders'] = 'store/orders/index';
$route['store/orders/view/(:num)'] = 'store/orders/view/$1';
$route['store/orders/status/(:num)'] = 'store/orders/status/$1';
$route['store/customers'] = 'store/customers/index';
$route['store/customers/view/(:num)'] = 'store/customers/view/$1';
$route['store/profile'] = 'store/profile/index';
$route['store/settings'] = 'store/settings/index';
$route['store/staff'] = 'store/staff/index';
$route['store/staff/create'] = 'store/staff/create';
$route['store/staff/create/(:num)'] = 'store/staff/create/$1';
$route['store/staff/delete/(:num)'] = 'store/staff/delete/$1';
$route['store/custom-css'] = 'store/appearance/css';
$route['store/themes'] = 'store/themes/index';
$route['store/themes/activate/(:num)'] = 'store/themes/activate/$1';
$route['store/themes/deactivate/(:num)'] = 'store/themes/deactivate/$1';
$route['store/themes/preview/(:num)'] = 'store/themes/preview/$1';
$route['store/apps'] = 'store/apps/index';
$route['store/apps/toggle/(:num)/(:any)'] = 'store/apps/toggle/$1/$2';
$route['store/apps/configure/(:num)'] = 'store/apps/configure/$1';

$route['admin/countries'] = 'admin/countries/index';
$route['admin/countries/form'] = 'admin/countries/form';
$route['admin/countries/form/(:num)'] = 'admin/countries/form/$1';
$route['admin/countries/save'] = 'admin/countries/save';
$route['admin/countries/save/(:num)'] = 'admin/countries/save/$1';
$route['admin/countries/delete/(:num)'] = 'admin/countries/delete/$1';

$route['admin/categories'] = 'admin/categories/index';
$route['admin/categories/form'] = 'admin/categories/form';
$route['admin/categories/form/(:num)'] = 'admin/categories/form/$1';
$route['admin/categories/save'] = 'admin/categories/save';
$route['admin/categories/save/(:num)'] = 'admin/categories/save/$1';
$route['admin/categories/delete/(:num)'] = 'admin/categories/delete/$1';
$route['admin/categories/parents'] = 'admin/categories/parents';
$route['admin/categories/import_preview'] = 'admin/categories/import_preview';
$route['admin/categories/import_save'] = 'admin/categories/import_save';

$route['admin/suppliers'] = 'admin/suppliers/index';
$route['admin/suppliers/form'] = 'admin/suppliers/form';
$route['admin/suppliers/form/(:num)'] = 'admin/suppliers/form/$1';
$route['admin/suppliers/save'] = 'admin/suppliers/save';
$route['admin/suppliers/save/(:num)'] = 'admin/suppliers/save/$1';
$route['admin/suppliers/delete/(:num)'] = 'admin/suppliers/delete/$1';

$route['admin/users'] = 'admin/users/index';
$route['admin/users/form'] = 'admin/users/form';
$route['admin/users/form/(:num)'] = 'admin/users/form/$1';
$route['admin/users/save'] = 'admin/users/save';
$route['admin/users/save/(:num)'] = 'admin/users/save/$1';
$route['admin/users/delete/(:num)'] = 'admin/users/delete/$1';

$route['admin/products'] = 'admin/products/index';
$route['admin/products/import'] = 'admin/products/import';
$route['admin/products/import_preview'] = 'admin/products/import_preview';
$route['admin/products/import_save'] = 'admin/products/import_save';
$route['admin/products/csv_template'] = 'admin/products/csv_template';
$route['admin/products/csv_preview'] = 'admin/products/csv_preview';
$route['admin/products/csv_import_row'] = 'admin/products/csv_import_row';
$route['admin/products/unknown'] = 'admin/products/unknown';
$route['admin/products/unknown_delete/(:num)'] = 'admin/products/unknown_delete/$1';
$route['admin/products/fetch_details/(:num)'] = 'admin/products/fetch_details/$1';
$route['admin/products/form'] = 'admin/products/form';
$route['admin/products/form/(:num)'] = 'admin/products/form/$1';
$route['admin/products/view/(:num)'] = 'admin/products/view/$1';
$route['admin/products/preview/(:num)/(:any)'] = 'admin/products/preview/$1/$2';
$route['admin/products/save'] = 'admin/products/save';
$route['admin/products/save/(:num)'] = 'admin/products/save/$1';
$route['admin/products/delete/(:num)'] = 'admin/products/delete/$1';
$route['admin/products/delete_image/(:num)'] = 'admin/products/delete_image/$1';

$route['admin/themes'] = 'admin/themes/index';
$route['admin/themes/settings/(:num)'] = 'admin/themes/settings/$1';
$route['admin/themes/save_field/(:num)'] = 'admin/themes/save_field/$1';
$route['admin/themes/save_field/(:num)/(:num)'] = 'admin/themes/save_field/$1/$2';
$route['admin/themes/delete_field/(:num)/(:num)'] = 'admin/themes/delete_field/$1/$2';

$route['admin/pricing'] = 'admin/pricing/index';
$route['admin/pricing/save'] = 'admin/pricing/save';

$route['admin/smtp'] = 'admin/smtp/index';
$route['admin/smtp/save'] = 'admin/smtp/save';
$route['admin/smtp/test'] = 'admin/smtp/test';

$route['admin/paypal'] = 'admin/paypal_settings/index';
$route['admin/paypal/save'] = 'admin/paypal_settings/save';

$route['admin/orders'] = 'admin/orders/index';
$route['admin/orders/view/(:num)'] = 'admin/orders/view/$1';
$route['admin/orders/complete/(:num)'] = 'admin/orders/complete/$1';
$route['admin/orders/status/(:num)'] = 'admin/orders/status/$1';

$route['admin/flush-data'] = 'admin/flush_data/index';
$route['admin/flush-data/submit'] = 'admin/flush_data/submit';

$route['admin/stores'] = 'admin/stores/index';
$route['admin/stores/form'] = 'admin/stores/form';
$route['admin/stores/form/(:num)'] = 'admin/stores/form/$1';
$route['admin/stores/save'] = 'admin/stores/save';
$route['admin/stores/save/(:num)'] = 'admin/stores/save/$1';
$route['admin/stores/theme/(:num)'] = 'admin/stores/theme/$1';
$route['admin/stores/save_theme/(:num)'] = 'admin/stores/save_theme/$1';
$route['admin/stores/settings/(:num)'] = 'admin/stores/settings/$1';
$route['admin/stores/save_settings/(:num)'] = 'admin/stores/save_settings/$1';
$route['admin/stores/delete/(:num)'] = 'admin/stores/delete/$1';

$route['shop'] = 'shop/catalog';
$route['category/(:any)/more'] = 'shop/category_more/$1';
$route['category/(:any)'] = 'shop/category/$1';
$route['product/(:any)'] = 'shop/detail/$1';
$route['contact'] = 'shop/page/contact';
$route['cart'] = 'shop/cart';
$route['cart/add/(:num)'] = 'shop/add_to_cart/$1';
$route['cart/update'] = 'shop/update_cart';
$route['cart/remove/(:num)'] = 'shop/remove_from_cart/$1';
$route['checkout'] = 'shop/checkout';
$route['payment'] = 'shop/payment';
$route['payment/card'] = 'shop/payment_card';
$route['payment/paypal'] = 'shop/payment_paypal';
$route['payment/return'] = 'shop/payment_return';
$route['payment/cancel'] = 'shop/payment_cancel';
$route['account'] = 'shop/customer_profile';
$route['account/login'] = 'shop/customer_login';
$route['account/signup'] = 'shop/customer_signup';
$route['account/logout'] = 'shop/customer_logout';
$route['page/(:any)'] = 'shop/page/$1';

