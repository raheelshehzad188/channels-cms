<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function profit_money($amount, $currency)
{
    return htmlspecialchars((string) $currency) . ' ' . number_format((float) $amount, 2);
}

function profit_currency_codes($rates = array())
{
    $codes = array('PKR', 'SEK', 'GBP', 'EUR', 'USD', 'AUD', 'AED', 'RON', 'PLN', 'BGN');
    foreach ((array) $rates as $code => $unused) {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            $codes[] = $code;
        }
    }
    $codes = array_values(array_unique($codes));
    sort($codes);
    return $codes;
}

function profit_money_plain($amount, $currency)
{
    return number_format((float) $amount, 2) . ' ' . htmlspecialchars((string) $currency);
}

function profit_pct($n)
{
    return number_format((float) $n, 1) . '%';
}

function profit_round2($n)
{
    return round((float) $n, 2);
}

function profit_new_extra_for_target($currentExtra, $currentSelling, $requiredSelling)
{
    $currentExtra = profit_round2($currentExtra);
    $currentSelling = (float) $currentSelling;
    $requiredSelling = (float) $requiredSelling;
    if ($requiredSelling <= 0) {
        return $currentExtra;
    }
    $delta = profit_round2($requiredSelling - $currentSelling);
    if ($delta <= 0) {
        return $currentExtra;
    }
    return profit_round2($currentExtra + $delta + 0.15);
}


function profit_convert_amount($amount, $fromCurrency, $toCurrency, $rates)
{
    $fromCurrency = strtoupper(trim((string) $fromCurrency));
    $toCurrency = strtoupper(trim((string) $toCurrency));
    if ($fromCurrency === '' || $toCurrency === '' || $fromCurrency === $toCurrency) {
        return (float) $amount;
    }
    $from = isset($rates[$fromCurrency]) ? (float) $rates[$fromCurrency] : 0;
    $to = isset($rates[$toCurrency]) ? (float) $rates[$toCurrency] : 0;
    if ($from <= 0 || $to <= 0) {
        return (float) $amount;
    }
    return ((float) $amount * $from) / $to;
}

function profit_default_settings()
{
    return array(
        'target_profit' => 100,
        'daily_ad_budget' => 6000,
        'expected_orders_per_day' => 5,
        'payment_fee_percent' => 2.9,
        'payment_fixed_fee' => 1,
        'other_cost' => 10,
        'return_rate' => 5,
        'default_currency' => 'SEK',
        'ad_budget_currency' => 'PKR',
        'manual_cpa' => null,
        'actual_ad_spend' => null,
        'actual_orders' => null,
    );
}

function profit_budget_currency($settings)
{
    $code = isset($settings['ad_budget_currency']) ? strtoupper(trim((string) $settings['ad_budget_currency'])) : '';
    return $code !== '' ? $code : 'PKR';
}

function profit_to_pkr($amount, $currency, $rates)
{
    $currency = strtoupper(trim((string) $currency));
    if ($currency === '' || $currency === 'PKR') {
        return (float) $amount;
    }
    $rate = isset($rates[$currency]) ? (float) $rates[$currency] : 0;
    if ($rate <= 0) {
        return null;
    }
    return (float) $amount * $rate;
}

function profit_blank($value)
{
    return $value === null || $value === '' || (is_string($value) && trim($value) === '');
}

function profit_convert_via_pkr($amount, $fromCurrency, $toCurrency, $rates)
{
    $fromCurrency = strtoupper(trim((string) $fromCurrency));
    $toCurrency = strtoupper(trim((string) $toCurrency));
    $amount = (float) $amount;
    if ($fromCurrency === '') {
        $fromCurrency = 'PKR';
    }
    if ($toCurrency === '') {
        $toCurrency = $fromCurrency;
    }
    if ($fromCurrency === $toCurrency) {
        return $amount;
    }
    $fromRate = ($fromCurrency === 'PKR') ? 1.0 : (isset($rates[$fromCurrency]) ? (float) $rates[$fromCurrency] : 0.0);
    $toRate = ($toCurrency === 'PKR') ? 1.0 : (isset($rates[$toCurrency]) ? (float) $rates[$toCurrency] : 0.0);
    if ($fromRate <= 0 || $toRate <= 0) {
        return $amount;
    }
    $pkr = $fromCurrency === 'PKR' ? $amount : ($amount * $fromRate);
    return $toCurrency === 'PKR' ? $pkr : ($pkr / $toRate);
}

function profit_from_budget($amount, $settings, $toCurrency, $rates)
{
    return profit_convert_via_pkr($amount, profit_budget_currency($settings), $toCurrency, $rates);
}

function profit_calculated_cpa_budget($settings)
{
    $orders = isset($settings['expected_orders_per_day']) ? (float) $settings['expected_orders_per_day'] : 0;
    if ($orders <= 0) {
        return 0.0;
    }
    return (float) $settings['daily_ad_budget'] / $orders;
}

function profit_resolve_cpa($product, $settings, $rates)
{
    $native = '';
    if (is_array($product) && !profit_blank(isset($product['currency']) ? $product['currency'] : null)) {
        $native = strtoupper(trim((string) $product['currency']));
    }
    if ($native === '') {
        $native = strtoupper(trim((string) (isset($settings['default_currency']) ? $settings['default_currency'] : 'SEK')));
    }
    $budgetCurrency = profit_budget_currency($settings);
    $toNative = function ($amount) use ($budgetCurrency, $native, $rates) {
        return profit_convert_via_pkr($amount, $budgetCurrency, $native, $rates);
    };
    $pack = function ($cpa, $mode, $source, $budgetAmount = null) use ($budgetCurrency) {
        return array(
            'cpa' => (float) $cpa,
            'mode' => $mode,
            'source' => $source,
            'cpa_budget' => $budgetAmount,
            'cpa_budget_currency' => $budgetAmount === null ? null : $budgetCurrency,
        );
    };

    $productSpend = is_array($product) && array_key_exists('actual_ad_spend', $product) ? $product['actual_ad_spend'] : null;
    $productOrders = is_array($product) && array_key_exists('actual_orders', $product) ? $product['actual_orders'] : null;
    if (!profit_blank($productSpend) && !profit_blank($productOrders) && (float) $productOrders > 0) {
        return $pack((float) $productSpend / (float) $productOrders, 'actual', 'product');
    }
    $productActualCpa = is_array($product) && array_key_exists('actual_cpa', $product) ? $product['actual_cpa'] : null;
    if (!profit_blank($productActualCpa)) {
        return $pack((float) $productActualCpa, 'actual', 'product');
    }

    $globalSpend = isset($settings['actual_ad_spend']) ? $settings['actual_ad_spend'] : null;
    $globalOrders = isset($settings['actual_orders']) ? $settings['actual_orders'] : null;
    if (!profit_blank($globalSpend) && !profit_blank($globalOrders) && (float) $globalOrders > 0) {
        $budgetCpa = (float) $globalSpend / (float) $globalOrders;
        return $pack($toNative($budgetCpa), 'actual', 'global', $budgetCpa);
    }

    $productManual = is_array($product) && array_key_exists('manual_cpa', $product) ? $product['manual_cpa'] : null;
    if (!profit_blank($productManual)) {
        return $pack((float) $productManual, 'manual', 'product');
    }

    $globalManual = isset($settings['manual_cpa']) ? $settings['manual_cpa'] : null;
    if (!profit_blank($globalManual)) {
        return $pack($toNative((float) $globalManual), 'manual', 'global', (float) $globalManual);
    }

    $budgetCpa = profit_calculated_cpa_budget($settings);
    return $pack($toNative($budgetCpa), 'calculated', 'shared_budget', $budgetCpa);
}

function profit_calculate($product, $settings, $rates = array())
{
    $currency = isset($product['currency']) ? $product['currency'] : $settings['default_currency'];
    $toNative = function ($amount) use ($settings, $currency, $rates) {
        return profit_convert_amount($amount, $settings['default_currency'], $currency, $rates);
    };

    $feePct = ((isset($product['payment_fee_percent']) && $product['payment_fee_percent'] !== null && $product['payment_fee_percent'] !== '')
        ? (float) $product['payment_fee_percent']
        : (float) $settings['payment_fee_percent']) / 100;
    $fixedFee = (isset($product['payment_fixed_fee']) && $product['payment_fixed_fee'] !== null && $product['payment_fixed_fee'] !== '')
        ? (float) $product['payment_fixed_fee']
        : $toNative((float) $settings['payment_fixed_fee']);
    $other = (isset($product['other_cost']) && $product['other_cost'] !== null && $product['other_cost'] !== '')
        ? (float) $product['other_cost']
        : $toNative((float) $settings['other_cost']);
    $returnRate = ((isset($product['return_rate']) && $product['return_rate'] !== null && $product['return_rate'] !== '')
        ? (float) $product['return_rate']
        : (float) $settings['return_rate']) / 100;

    $sellingPrice = (float) $product['selling_price'];
    $productCost = (float) $product['product_cost'];
    $shippingCost = (float) $product['shipping_cost'];
    $cpaInfo = profit_resolve_cpa($product, $settings, $rates);
    $cpa = (float) $cpaInfo['cpa'];
    $targetProfit = $toNative((float) $settings['target_profit']);

    $paymentFee = $sellingPrice * $feePct + $fixedFee;
    $returnAllowance = $sellingPrice * $returnRate;
    $landedCost = $productCost + $shippingCost;
    $totalCostBeforeAds = $productCost + $shippingCost + $paymentFee + $other + $returnAllowance;
    $netProfit = $sellingPrice - $productCost - $shippingCost - $paymentFee - $other - $returnAllowance - $cpa;
    $profitMargin = $sellingPrice > 0 ? ($netProfit / $sellingPrice) * 100 : 0;
    $breakEvenCpa = $sellingPrice - $productCost - $shippingCost - $paymentFee - $other - $returnAllowance;
    $targetCpa = $breakEvenCpa - $targetProfit;
    $profitGap = $targetProfit - $netProfit;
    $denominator = 1 - $feePct - $returnRate;
    $requiredSellingPrice = $denominator > 0
        ? ($targetProfit + $productCost + $shippingCost + $other + $fixedFee + $cpa) / $denominator
        : null;
    $maximumProductCost = $sellingPrice - $shippingCost - $paymentFee - $other - $returnAllowance - $cpa - $targetProfit;
    $maximumShippingCost = $sellingPrice - $productCost - $paymentFee - $other - $returnAllowance - $cpa - $targetProfit;

    if ($netProfit <= 0) {
        $status = 'loss';
    } elseif ($netProfit >= $targetProfit) {
        $status = 'above_target';
    } else {
        $status = 'below_target';
    }

    $rate = isset($rates[$currency]) ? (float) $rates[$currency] : null;
    $localProfit = profit_to_pkr($netProfit, $currency, $rates);

    return array(
        'selling_price' => $sellingPrice,
        'product_cost' => $productCost,
        'shipping_cost' => $shippingCost,
        'payment_fee' => $paymentFee,
        'return_allowance' => $returnAllowance,
        'landed_cost' => $landedCost,
        'total_cost_before_ads' => $totalCostBeforeAds,
        'net_profit' => $netProfit,
        'profit_margin' => $profitMargin,
        'break_even_cpa' => $breakEvenCpa,
        'target_cpa' => $targetCpa,
        'profit_gap' => $profitGap,
        'required_selling_price' => $requiredSellingPrice,
        'maximum_product_cost' => $maximumProductCost,
        'maximum_shipping_cost' => $maximumShippingCost,
        'maximum_cpa' => $targetCpa,
        'local_profit' => $localProfit,
        'rate_to_pkr' => $rate,
        'status' => $status,
        'cpa' => $cpa,
        'cpa_mode' => $cpaInfo['mode'],
        'cpa_source' => $cpaInfo['source'],
        'cpa_budget' => isset($cpaInfo['cpa_budget']) ? $cpaInfo['cpa_budget'] : null,
        'cpa_budget_currency' => isset($cpaInfo['cpa_budget_currency']) ? $cpaInfo['cpa_budget_currency'] : null,
        'target_profit' => $targetProfit,
    );
}

function profit_status_label($status)
{
    if ($status === 'above_target') {
        return 'Profitable / Above Target';
    }
    if ($status === 'below_target') {
        return 'Below Target';
    }
    return 'Loss';
}

function profit_status_class($status)
{
    if ($status === 'above_target') {
        return 'label-primary';
    }
    if ($status === 'below_target') {
        return 'label-warning';
    }
    return 'label-danger';
}

function profit_status_key($status)
{
    $status = strtolower(trim((string) $status));
    if ($status === 'profitable' || $status === 'above' || $status === 'above_target') {
        return 'above_target';
    }
    if ($status === 'below_target' || $status === 'below') {
        return 'below_target';
    }
    if ($status === 'loss') {
        return 'loss';
    }
    return '';
}

function profit_round_metrics($metrics)
{
    foreach ($metrics as $key => $value) {
        if (is_numeric($value)) {
            $metrics[$key] = profit_round2($value);
        }
    }
    if (isset($metrics['net_profit'], $metrics['target_profit'])) {
        if ($metrics['net_profit'] <= 0) {
            $metrics['status'] = 'loss';
        } elseif ($metrics['net_profit'] >= $metrics['target_profit']) {
            $metrics['status'] = 'above_target';
        } else {
            $metrics['status'] = 'below_target';
        }
    }
    return $metrics;
}

function profit_analyzer_id($product)
{
    if (is_array($product)) {
        $code = isset($product['analyzer_code']) ? $product['analyzer_code'] : '';
        $sku = isset($product['sku']) ? $product['sku'] : '';
        $id = isset($product['id']) ? $product['id'] : 0;
    } else {
        $code = isset($product->analyzer_code) ? $product->analyzer_code : '';
        $sku = isset($product->sku) ? $product->sku : '';
        $id = isset($product->id) ? $product->id : 0;
    }
    if (trim((string) $code) !== '') {
        return trim((string) $code);
    }
    if (trim((string) $sku) !== '') {
        return trim((string) $sku);
    }
    return 'P' . (int) $id;
}
