<?php
declare(strict_types=1);
// Existing monthly NGN toolkit prices, moved from subpro.php to the server.
// Amounts are integer kobo. Billing has no automatic renewals or currency conversion.
function billing_catalog(): array {
    return [
        'payroll'=>['name'=>'Finance & inventory','amount'=>300000,'modules'=>['expenses','ledger','inventory','invoices']],
        'recruiting'=>['name'=>'Recruiting','amount'=>250000,'modules'=>['jobs','applications']],
        'crm'=>['name'=>'Sales & CRM','amount'=>400000,'modules'=>['leads','customers','contacts','accounts','deals','pos']],
        'cybersecurity'=>['name'=>'Security','amount'=>400000,'modules'=>['incidents']],
        'project'=>['name'=>'Projects & tasks','amount'=>350000,'modules'=>['projects','tasks','events']],
        'strategy'=>['name'=>'Business strategy','amount'=>350000,'modules'=>['objectives']],
        'risk'=>['name'=>'Compliance','amount'=>300000,'modules'=>['compliance']],
        'product'=>['name'=>'Product development','amount'=>400000,'modules'=>['products']],
        'office'=>['name'=>'Office assets','amount'=>350000,'modules'=>['assets']],
    ];
}
function billing_usd_rate(): string {
    // Original simplr24 subpro.php fallback. This is an explicit fixed price
    // conversion, not a claim to have fetched a current market exchange rate.
    $rate = config('BILLING_USD_RATE','0.00063');
    if (!preg_match('/^(?:0\.[0-9]{1,8}|1(?:\.0{1,8})?)$/D',$rate) || (float)$rate <= 0) throw new InvalidArgumentException('Dollar pricing is temporarily unavailable.');
    return $rate;
}
function billing_usd_minor(int $ngnMinor): int {
    $parts = explode('.',billing_usd_rate());
    $numerator = (int)$parts[0] * 100000000 + (int)str_pad($parts[1] ?? '',8,'0');
    return intdiv($ngnMinor * $numerator + 50000000,100000000);
}
function billing_quote(array $selection, string $currency = 'NGN'): array {
    if (!in_array($currency,['NGN','USD'],true)) throw new InvalidArgumentException('Choose NGN or USD.');
    $items = []; $amount = 0; $catalog = billing_catalog();
    ksort($selection);
    foreach ($selection as $key=>$months) {
        if (!isset($catalog[$key]) || !is_scalar($months) || !preg_match('/^(?:[0-9]|1[0-2])$/D',(string)$months)) throw new InvalidArgumentException('Choose between 1 and 12 months for each toolkit.');
        if ((int)$months === 0) continue;
        $tool = $catalog[$key]; $line = $tool['amount'] * (int)$months; $amount += $line;
        $items[] = ['key'=>$key,'name'=>$tool['name'],'months'=>(int)$months,'unit_amount'=>$tool['amount'],'amount'=>$line];
    }
    if (!$items) throw new InvalidArgumentException('Choose at least one toolkit.');
    if ($currency === 'USD') {
        foreach ($items as &$item) { $item['pricing_currency'] = 'NGN'; $item['usd_rate'] = billing_usd_rate(); } unset($item);
        $amount = billing_usd_minor($amount);
        if ($amount < 1) throw new InvalidArgumentException('The dollar total must be at least one cent.');
    }
    return ['items'=>$items,'amount'=>$amount,'currency'=>$currency];
}
