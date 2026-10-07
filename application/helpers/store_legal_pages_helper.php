<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function store_legal_pages_seed($storeId)
{
    $CI =& get_instance();
    $CI->load->model('Store_page_model');
    $CI->Store_page_model->ensure_tables();
    $store = $CI->db->where('id', (int) $storeId)->get('stores')->row();
    if (!$store) {
        return 0;
    }
    $settings = array();
    if ($CI->db->table_exists('store_settings')) {
        $rows = $CI->db->where('store_id', (int) $storeId)->get('store_settings')->result_array();
        foreach ($rows as $row) {
            $pair = function_exists('store_setting_row_pair') ? store_setting_row_pair($row) : null;
            if ($pair && !empty($pair['key'])) {
                $settings[$pair['key']] = isset($pair['value']) ? $pair['value'] : '';
            }
        }
    }
    $ctx = store_legal_pages_context($store, $settings);
    $n = 0;
    foreach (store_legal_pages_definitions($ctx) as $page) {
        $saved = $CI->Store_page_model->upsert_by_slug((int) $storeId, $page);
        if ($saved) {
            $n++;
        }
    }
    return $n;
}

function store_legal_pages_context($store, $settings)
{
    $info = function_exists('storefront_contact_info')
        ? storefront_contact_info($store, $settings)
        : array('name' => $store->name, 'email' => '', 'phone' => '', 'address' => '', 'domain' => $store->domain);
    $domain = $info['domain'] !== '' ? $info['domain'] : trim((string) $store->domain);
    $base = $domain !== '' ? ('https://' . $domain) : '';
    $name = $info['name'] !== '' ? $info['name'] : trim((string) $store->name);
    return array(
        'name' => $name,
        'email' => $info['email'],
        'phone' => $info['phone'],
        'address' => $info['address'],
        'domain' => $domain,
        'contact_url' => $base !== '' ? ($base . '/contact') : '/contact',
        'privacy_url' => $base !== '' ? ($base . '/privacy-policy') : '/privacy-policy',
        'terms_url' => $base !== '' ? ($base . '/terms-of-service') : '/terms-of-service',
        'deletion_url' => $base !== '' ? ($base . '/data-deletion') : '/data-deletion',
        'year' => date('Y'),
        'updated' => date('j F Y'),
    );
}

function store_legal_pages_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function store_legal_pages_contact_html($ctx)
{
    $parts = array();
    if ($ctx['email'] !== '') {
        $email = store_legal_pages_e($ctx['email']);
        $parts[] = 'email: <a href="mailto:' . $email . '">' . $email . '</a>';
    }
    $parts[] = 'the public contact form at <a href="' . store_legal_pages_e($ctx['contact_url']) . '">' . store_legal_pages_e($ctx['contact_url']) . '</a>';
    if ($ctx['phone'] !== '') {
        $parts[] = 'phone: ' . store_legal_pages_e($ctx['phone']);
    }
    if (count($parts) === 1) {
        return $parts[0];
    }
    $last = array_pop($parts);
    return implode(', ', $parts) . ', or ' . $last;
}

function store_legal_pages_operator_html($ctx)
{
    $name = store_legal_pages_e($ctx['name']);
    $html = '<p>This website (' . store_legal_pages_e($ctx['domain']) . ') is operated by <strong>' . $name . '</strong>.</p>';
    if ($ctx['address'] !== '') {
        $html .= '<p>Correspondence address: ' . store_legal_pages_e($ctx['address']) . '</p>';
    } else {
        $html .= '<p><em>[To be completed by store admin: registered office / postal address]</em></p>';
    }
    $html .= '<p><em>[To be completed by store admin: legal entity name and company registration number, if different from the store name above]</em></p>';
    return $html;
}

function store_legal_pages_definitions($ctx)
{
    $name = store_legal_pages_e($ctx['name']);
    $contactHow = store_legal_pages_contact_html($ctx);
    $operator = store_legal_pages_operator_html($ctx);

    $privacy = ''
        . '<p>This Privacy Policy describes how ' . $name . ' (“we”, “us”) may collect, use, and share personal information when you visit ' . store_legal_pages_e($ctx['domain']) . ', create an account, place an order, or otherwise contact us. It is written in plain language and does not create rights beyond those that apply under law.</p>'
        . '<p>Last updated: ' . store_legal_pages_e($ctx['updated']) . '.</p>'
        . $operator
        . '<h2>Personal information we may collect</h2>'
        . '<p>The information we hold depends on how you use the store. We do not collect every category below from every visitor.</p>'
        . '<h3>Account information</h3>'
        . '<p>If you create a customer account, we may store your name, email address, password (stored in a protected form), and other details you choose to save in your profile, such as a phone number or delivery address.</p>'
        . '<h3>Contact information</h3>'
        . '<p>If you write to us (including through the contact form), we may store your name, email address, order number if you provide one, the subject of your message, and the message itself.</p>'
        . '<h3>Order and transaction information</h3>'
        . '<p>When you place an order we may process the products ordered, quantities, prices, shipping method, delivery details, payment status, and related order notes. Payment card details are processed by the payment provider shown at checkout; we do not intend to store full payment card numbers on this website.</p>'
        . '<h3>Website usage information</h3>'
        . '<p>Like most websites, our servers and analytics tools may record technical data such as IP address, browser type, device type, referring page, pages viewed, and approximate timestamps. This helps us operate, secure, and improve the store.</p>'
        . '<h3>Cookies</h3>'
        . '<p>We use cookies and similar technologies that are needed to run the store (for example, keeping you signed in and remembering your cart). We may also use cookies or pixels for analytics and, where enabled by the store, for advertising measurement. You can control cookies in your browser settings. Blocking some cookies may limit store features.</p>'
        . '<h3>Meta / Facebook and social login</h3>'
        . '<p>' . $name . ' may use Meta (Facebook/Instagram) technologies such as the Meta Pixel or Conversions API when advertising or event measurement is enabled. Those tools may receive limited event data (for example, that a page was viewed or a purchase was completed), subject to Meta’s terms and your device/browser settings.</p>'
        . '<p>This storefront does not currently offer Facebook Login as a customer sign-in method. If that changes, or if you otherwise connect a Meta account to a ' . $name . ' experience, we may receive the profile data Meta shares with us for that purpose (typically a name, email, and a Meta user identifier). We will only use that data to operate the requested feature.</p>'
        . '<h2>How we use information</h2>'
        . '<p>We use personal information to:</p>'
        . '<ul>'
        . '<li>provide the website, accounts, checkout, order fulfilment, and customer support;</li>'
        . '<li>process payments through the methods offered at checkout;</li>'
        . '<li>send order-related messages (such as confirmations or shipping updates);</li>'
        . '<li>measure store performance and, where enabled, advertising performance including Meta events;</li>'
        . '<li>protect the store against fraud, abuse, and technical issues;</li>'
        . '<li>comply with legal obligations that apply to us, such as accounting records.</li>'
        . '</ul>'
        . '<p>We do not sell your personal information as a product. We do not use the information you give us for purposes that are incompatible with operating this store, except where the law requires or allows it.</p>'
        . '<h2>How information is stored and protected</h2>'
        . '<p>Information is stored on systems used to run this website and related services. We use reasonable technical and organisational measures (such as access controls and encrypted connections where the site is served over HTTPS) to protect personal data. No method of transmission or storage is completely secure, and we cannot guarantee absolute security.</p>'
        . '<h2>Third-party services</h2>'
        . '<p>We use service providers to operate the store. Depending on how you shop, this may include hosting, email delivery, payment processors (for example PayPal where offered at checkout), shipping/fulfilment partners, analytics, and advertising platforms such as Meta. Those providers process data on our behalf or as independent controllers according to their own terms.</p>'
        . '<h2>Data sharing</h2>'
        . '<p>We may share personal information with:</p>'
        . '<ul>'
        . '<li>service providers who help us run the store;</li>'
        . '<li>payment, shipping, or fulfilment partners needed to complete your order;</li>'
        . '<li>professional advisers or authorities where we are legally required or entitled to do so;</li>'
        . '<li>a buyer of the business, if the store is transferred, under appropriate safeguards.</li>'
        . '</ul>'
        . '<p>We do not share customer data with unrelated third parties for their independent marketing unless you have asked us to, or the law requires it.</p>'
        . '<h2>Your rights</h2>'
        . '<p>Depending on where you live, you may have rights to access, correct, delete, or restrict the use of your personal data, to object to certain processing, and to lodge a complaint with a supervisory authority. For users in the EEA/UK this may include rights under the GDPR. To exercise a request, contact us using the details below. We may need to verify your identity before acting.</p>'
        . '<p>A dedicated explanation of account and data deletion is available at <a href="' . store_legal_pages_e($ctx['deletion_url']) . '">' . store_legal_pages_e($ctx['deletion_url']) . '</a>.</p>'
        . '<h2>Data retention</h2>'
        . '<p>We keep personal information only for as long as needed for the purposes described above. Order and invoice records may be kept for longer where bookkeeping, tax, dispute, or other legal rules require it. Account data is kept while the account remains open, and for a limited period afterwards if needed to complete outstanding orders or legal obligations.</p>'
        . '<h2>How to contact us about privacy</h2>'
        . '<p>For privacy questions or requests, contact ' . $name . ' via ' . $contactHow . '.</p>'
        . '<p>Please include “Privacy request” in the subject and enough detail for us to find your account or order (for example the email address you used).</p>'
        . '<p>This policy may be updated from time to time. The “Last updated” date at the top will change when we publish a revision. Continued use of the website after an update means you should read the current version.</p>';

    $deletion = ''
        . '<p>This page explains how you can ask ' . $name . ' to delete your customer account and associated personal data. It is also the public User Data Deletion URL for Meta / Facebook App Review.</p>'
        . '<p>Last updated: ' . store_legal_pages_e($ctx['updated']) . '.</p>'
        . '<h2>What data can be deleted</h2>'
        . '<p>On request, we can delete or anonymise personal data that we do not need to keep, including:</p>'
        . '<ul>'
        . '<li>your customer account login and profile details;</li>'
        . '<li>saved addresses and phone numbers on the account;</li>'
        . '<li>marketing or support messages that are not required as business records;</li>'
        . '<li>identifiers linked to a Meta/Facebook user, if you used a Meta-related feature with this store.</li>'
        . '</ul>'
        . '<p>Order history that is required for accounting, tax, fraud prevention, or dispute handling may be retained in a limited form even after the account is closed. We will tell you if part of a request cannot be completed for that reason.</p>'
        . '<h2>How to submit a deletion request</h2>'
        . '<p>There is no self-service “delete my account” button in the storefront today. Send a request using ' . $contactHow . '.</p>'
        . '<p>Please include:</p>'
        . '<ol>'
        . '<li>the subject line “Data deletion request”;</li>'
        . '<li>the email address on your ' . $name . ' account;</li>'
        . '<li>any order numbers you want us to locate;</li>'
        . '<li>if the request relates to Meta/Facebook, your Facebook/Meta user ID or the email used with that app, if you have it.</li>'
        . '</ol>'
        . '<p>If you cannot access the email inbox on the account, describe another way we can reasonably verify that you are the account holder.</p>'
        . '<h2>What happens after a request</h2>'
        . '<ol>'
        . '<li>We confirm that we have received the request.</li>'
        . '<li>We verify that the request comes from the account holder (or an authorised person).</li>'
        . '<li>We delete or anonymise personal data that is not subject to a retention exception.</li>'
        . '<li>We close the customer account so it can no longer be used to sign in.</li>'
        . '<li>We send a short confirmation when the request has been processed, or explain what we could not delete and why.</li>'
        . '</ol>'
        . '<h2>Retention exceptions</h2>'
        . '<p>We may retain specific records where the law requires or allows it, including invoices and order records needed for bookkeeping and tax, information needed to handle a payment chargeback or legal claim, and data needed to detect or prevent abuse. Those records are kept only for as long as that purpose requires.</p>'
        . '<h2>Timeframe</h2>'
        . '<p>We aim to handle deletion requests without undue delay. Where data-protection law applies (for example the GDPR for people in the EEA), we will respond within the period that law requires — typically one month, which may be extended in complex cases if the law allows. This is not a guarantee of a shorter internal deadline.</p>'
        . '<h2>Meta / Facebook users</h2>'
        . '<p>If you used a Meta product in connection with this store and want related data removed, submit the request as described above. We will delete the personal data we hold that is linked to that use, except where a retention exception applies. Removing data from Meta’s own systems (for example your Facebook account) must be done in Meta’s settings.</p>'
        . '<h2>Contact</h2>'
        . '<p>Questions about this process: ' . $contactHow . '.</p>'
        . '<p>Related policies: <a href="' . store_legal_pages_e($ctx['privacy_url']) . '">Privacy Policy</a> and <a href="' . store_legal_pages_e($ctx['terms_url']) . '">Terms of Service</a>.</p>';

    $terms = ''
        . '<p>These Terms of Service (“Terms”) apply to your use of the ' . $name . ' website at ' . store_legal_pages_e($ctx['domain']) . '. By using the website or placing an order you agree to these Terms. If you do not agree, please do not use the store.</p>'
        . '<p>Last updated: ' . store_legal_pages_e($ctx['updated']) . '.</p>'
        . $operator
        . '<h2>Website usage</h2>'
        . '<p>You may browse and purchase from the store for lawful personal or ordinary business use. You must not misuse the website (including attempting to gain unauthorised access, disrupting service, scraping in a way that harms the store, or introducing malware).</p>'
        . '<h2>User accounts</h2>'
        . '<p>Some features require a customer account. You are responsible for keeping your login details confidential and for activity on your account. Tell us promptly if you believe the account has been used without permission. We may refuse, suspend, or close an account if we reasonably believe these Terms have been broken or that the account is being used for fraud or abuse.</p>'
        . '<h2>Orders</h2>'
        . '<p>An order is an offer to buy the products shown in your cart. We may accept or decline an order (for example if an item is unavailable, a price is obviously wrong, or we cannot complete payment or delivery). A contract is formed when we confirm the order. We may cancel an accepted order if we cannot fulfil it; if that happens we will refund amounts you have paid for the cancelled items, using the original payment method where practical.</p>'
        . '<h2>Payments</h2>'
        . '<p>You must pay using a method offered at checkout. Payment is processed by the provider of that method. The amount charged is the total shown at checkout, including any shipping and taxes displayed there. If a payment fails or is later reversed, we may pause fulfilment until the order is paid.</p>'
        . '<h2>Product information</h2>'
        . '<p>We aim to describe products accurately using the information available to us, including titles, images, and specifications. Photographs are illustrative. Colours and details may vary. If a description is materially wrong, contact us and we will look into a correction, replacement, or refund as appropriate.</p>'
        . '<h2>Pricing</h2>'
        . '<p>Prices are shown in the currency used by this store. The price that applies is the price displayed at checkout when you complete the order. We may change prices on the website at any time before you place an order. Promotional prices apply only while they are shown and only on the terms stated with the offer.</p>'
        . '<h2>Shipping</h2>'
        . '<p>Available shipping options, costs, and any estimated delivery windows are shown during checkout or on the product page when that information is configured. Estimates are not guaranteed delivery dates. Risk in the goods passes according to the shipping method and applicable law. If a parcel is delayed or missing, contact us with your order number so we can check tracking with the carrier.</p>'
        . '<h2>Returns and refunds</h2>'
        . '<p>Returns and refunds are handled according to the information shown at checkout, any returns instructions we send you, and consumer law that applies to your purchase. We do not state a fixed “no-questions” return period on this page beyond what is offered at checkout or required by law.</p>'
        . '<p>If you believe you have a legal right to withdraw from a distance contract, or that an item is faulty, contact us before returning the goods unless we have told you otherwise. Refunds, when due, are made to the original payment method where practical.</p>'
        . '<p><em>[To be completed by store admin: any additional written returns policy, restocking rules, or non-returnable item categories]</em></p>'
        . '<h2>Intellectual property</h2>'
        . '<p>The store name, branding, website design, and content we publish are owned by us or used under licence. You may not copy, scrape, or reuse that material for a competing store or public distribution without permission, except for ordinary viewing and personal use or where the law allows.</p>'
        . '<h2>Prohibited use</h2>'
        . '<p>You must not use the website to commit fraud, money laundering, or other crimes; to harass staff or other customers; to upload unlawful content; or to circumvent security, pricing, or payment controls.</p>'
        . '<h2>Account termination</h2>'
        . '<p>You may stop using the store and ask us to close your account as described on the <a href="' . store_legal_pages_e($ctx['deletion_url']) . '">data deletion page</a>. We may suspend or terminate access if you seriously or repeatedly break these Terms, if required by law, or if we stop operating this website. Outstanding orders already paid may still be fulfilled or refunded as appropriate.</p>'
        . '<h2>Third-party services</h2>'
        . '<p>Checkout, payments, shipping, analytics, and advertising may involve third-party services. Their terms and privacy notices apply to their processing. We are not responsible for websites we do not control that you choose to visit from our pages.</p>'
        . '<h2>Limitation of liability</h2>'
        . '<p>Nothing in these Terms limits liability that cannot be limited under applicable law, including liability for death or personal injury caused by negligence, or for fraud. Subject to that, we are not liable for losses that were not reasonably foreseeable, for business losses if you use the store as a consumer, or for delays or failures caused by events outside our reasonable control (including carrier disruption or supplier shortage). Our liability for any order is limited to the amount you paid for that order, except where the law says otherwise.</p>'
        . '<h2>Changes to these Terms</h2>'
        . '<p>We may update these Terms from time to time. The current version will be published at <a href="' . store_legal_pages_e($ctx['terms_url']) . '">' . store_legal_pages_e($ctx['terms_url']) . '</a> with a new “Last updated” date. Changes apply to new visits and new orders after publication. They do not change a contract already formed for an earlier order, except where the law requires.</p>'
        . '<h2>Contact information</h2>'
        . '<p>Questions about these Terms: ' . $contactHow . '.</p>'
        . '<p>See also our <a href="' . store_legal_pages_e($ctx['privacy_url']) . '">Privacy Policy</a>.</p>';

    return array(
        array(
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'detail' => $privacy,
            'meta_description' => 'How ' . $ctx['name'] . ' collects, uses, stores, and shares personal information, including cookies, orders, and Meta/Facebook-related data.',
            'status' => 1,
            'show_in_nav' => 0,
            'show_in_footer' => 1,
            'sort_order' => 20,
        ),
        array(
            'title' => 'Data Deletion',
            'slug' => 'data-deletion',
            'detail' => $deletion,
            'meta_description' => 'How to request deletion of your ' . $ctx['name'] . ' account and personal data, including Meta/Facebook-related data.',
            'status' => 1,
            'show_in_nav' => 0,
            'show_in_footer' => 1,
            'sort_order' => 30,
        ),
        array(
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'detail' => $terms,
            'meta_description' => 'Terms of Service for shopping at ' . $ctx['name'] . ', covering accounts, orders, payments, shipping, returns, and liability.',
            'status' => 1,
            'show_in_nav' => 0,
            'show_in_footer' => 1,
            'sort_order' => 21,
        ),
    );
}
