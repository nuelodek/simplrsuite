<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/modules.php';
require_once __DIR__ . '/../app/settings.php';

$db = database();
$user = workspace_user($db);
$companyId = (int)$user['company_id'];
$role = (string)($_SESSION['auth_role'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$body = $_POST;
if ($method === 'POST' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $decoded = json_decode(file_get_contents('php://input'), true);
    if (!is_array($decoded)) fail('Submit a valid POS request.', 400);
    $body = $decoded;
}
$action = $method === 'GET' ? (is_string($_GET['mode'] ?? null) ? $_GET['mode'] : 'bootstrap') : (is_string($body['action'] ?? null) ? $body['action'] : '');
$writeActions = ['create_register','open_shift','close_shift','create_sale','complete_sale','cancel_sale','refund_sale'];
require_module_access($db, $user, 'pos', in_array($action, $writeActions, true));

function pos_actor(): array {
    $role = (string)($_SESSION['auth_role'] ?? '');
    return ['role'=>$role,'id'=>$role === 'admin' ? (int)($_SESSION['user_id'] ?? 0) : (int)($_SESSION['staff_id'] ?? 0)];
}
function pos_money_cents(mixed $value, string $label, bool $allowZero = true): int {
    if (!is_string($value) && !is_int($value) && !is_float($value)) fail('Enter a valid ' . $label . '.', 422);
    $text = trim((string)$value);
    if (!preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D', $text)) fail('Enter a valid ' . $label . '.', 422);
    [$whole,$fraction] = array_pad(explode('.', $text, 2), 2, '');
    $cents = ((int)$whole * 100) + (int)str_pad($fraction, 2, '0');
    if ((!$allowZero && $cents < 1) || $cents > 999999999999) fail('Enter a valid ' . $label . '.', 422);
    return $cents;
}
function pos_amount(int $cents): string {
    return intdiv($cents,100) . '.' . str_pad((string)($cents % 100),2,'0',STR_PAD_LEFT);
}
function pos_db_cents(mixed $value): int {
    if (!is_string($value) && !is_int($value) && !is_float($value)) throw new UnexpectedValueException('Invalid stored POS amount.');
    $text = trim((string)$value);
    if (!preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D', $text)) throw new UnexpectedValueException('Invalid stored POS amount.');
    [$whole,$fraction] = array_pad(explode('.', $text, 2), 2, '');
    return (int)$whole * 100 + (int)str_pad($fraction, 2, '0');
}
function pos_reservations_expire(mysqli $db, int $companyId): void {
    db_run($db,"UPDATE pos_sales SET status = 'expired', expires_at = NULL WHERE company_id = ? AND status = 'reserved' AND expires_at <= UTC_TIMESTAMP()",[$companyId]);
}
function pos_require_write(mysqli $db, array $user, bool $lock = true): void {
    require_module_access($db,$user,'pos',true,$lock);
}
function pos_lock_actor(mysqli $db, array $user): void {
    $role = (string)($_SESSION['auth_role'] ?? '');
    $table = match ($role) { 'admin'=>'users','employee'=>'employees','manager'=>'managers',default=>'' };
    if ($table === '' || !db_one($db,"SELECT id FROM `$table` WHERE id = ? AND company_id = ? FOR UPDATE",[(int)$user['id'],(int)$user['company_id']])) {
        fail('Your workspace account could not be verified.',403);
    }
}
function pos_require_operator(array $user, array $shift): void {
    $actor = pos_actor();
    if (($actor['role'] !== $shift['opened_by_role'] || $actor['id'] !== (int)$shift['opened_by_id']) && $actor['role'] !== 'admin') {
        fail('Only the cashier who opened this register can manage its shift and sales.',403);
    }
}
function pos_sale(mysqli $db, int $companyId, int $saleId, bool $lock = false): ?array {
    return db_one($db,'SELECT * FROM pos_sales WHERE id = ? AND company_id = ?' . ($lock ? ' FOR UPDATE' : ''),[$saleId,$companyId]);
}
function pos_shift(mysqli $db, int $companyId, int $shiftId, bool $lock = false): ?array {
    return db_one($db,'SELECT * FROM pos_shifts WHERE id = ? AND company_id = ?' . ($lock ? ' FOR UPDATE' : ''),[$shiftId,$companyId]);
}
function pos_inventory_rows(mysqli $db, int $companyId, array $ids, bool $lock = false): array {
    if (!$ids) return [];
    $marks = implode(',',array_fill(0,count($ids),'?'));
    return db_rows($db,"SELECT id, name, quantity, unit_price FROM inventory_items WHERE company_id = ? AND id IN ($marks) ORDER BY id" . ($lock ? ' FOR UPDATE' : ''),[$companyId,...$ids]);
}
function pos_reserved_quantities(mysqli $db, int $companyId, array $ids): array {
    if (!$ids) return [];
    $marks = implode(',',array_fill(0,count($ids),'?'));
    $rows = db_rows($db,"SELECT si.inventory_item_id,si.quantity FROM pos_sale_items si JOIN pos_sales s ON s.id = si.sale_id AND s.company_id = si.company_id
        WHERE si.company_id = ? AND si.inventory_item_id IN ($marks) AND s.status = 'reserved' AND s.expires_at > UTC_TIMESTAMP()
        ORDER BY si.inventory_item_id,s.id FOR UPDATE",[$companyId,...$ids]);
    $reserved = [];
    foreach ($rows as $row) {
        $id = (int)$row['inventory_item_id'];
        $reserved[$id] = ($reserved[$id] ?? 0) + (int)$row['quantity'];
    }
    return $reserved;
}
function pos_bootstrap(mysqli $db, array $user, int $companyId, string $currency, string $timezone): never {
    pos_reservations_expire($db,$companyId);
    $actor = pos_actor();
    $products = db_rows($db,"SELECT i.id, i.name, i.description, i.category, i.quantity, i.unit_price,
        GREATEST(i.quantity - COALESCE(r.reserved,0),0) AS available
        FROM inventory_items i
        LEFT JOIN (
            SELECT si.inventory_item_id, SUM(si.quantity) AS reserved
            FROM pos_sale_items si JOIN pos_sales s ON s.id = si.sale_id AND s.company_id = si.company_id
            WHERE s.company_id = ? AND s.status = 'reserved' AND s.expires_at > UTC_TIMESTAMP()
            GROUP BY si.inventory_item_id
        ) r ON r.inventory_item_id = i.id
        WHERE i.company_id = ? ORDER BY i.name LIMIT 500",[$companyId,$companyId]);
    $registers = db_rows($db,'SELECT r.id, r.name, r.is_active, s.id AS open_shift_id, s.opening_cash, s.opened_at, s.opened_by_role, s.opened_by_id
        FROM pos_registers r LEFT JOIN pos_shifts s ON s.register_id = r.id AND s.company_id = r.company_id AND s.status = \'open\'
        WHERE r.company_id = ? AND r.is_active = 1 ORDER BY r.name',[$companyId]);
    $myShift = null;
    foreach ($registers as $register) {
        if ((int)($register['open_shift_id'] ?? 0) > 0 && $register['opened_by_role'] === $actor['role'] && (int)$register['opened_by_id'] === $actor['id']) {
            $myShift = ['id'=>(int)$register['open_shift_id'],'register_id'=>(int)$register['id'],'register_name'=>$register['name'],'opening_cash'=>$register['opening_cash'],'opened_at'=>$register['opened_at']];
            break;
        }
    }
    $customers = db_rows($db,"SELECT id, CONCAT(first_name,' ',last_name) AS name, customer_email, business_name
        FROM customers WHERE company_id = ? ORDER BY first_name, last_name LIMIT 500",[$companyId]);
    $sales = db_rows($db,'SELECT s.id, s.reference, s.status, s.currency, s.total, s.refunded_amount, s.payment_method, s.created_at, s.completed_at, s.expires_at,
        c.first_name, c.last_name, CONCAT(c.first_name,\' \',c.last_name) AS customer_name
        FROM pos_sales s LEFT JOIN customers c ON c.id = s.customer_id AND c.company_id = s.company_id
        WHERE s.company_id = ? ORDER BY s.id DESC LIMIT 40',[$companyId]);
    try { $zone = new DateTimeZone($timezone); } catch (Throwable $error) { throw new UnexpectedValueException('The workspace timezone is invalid.',0,$error); }
    $localStart = new DateTimeImmutable('today',$zone);
    $utcStart = $localStart->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $utcEnd = $localStart->modify('+1 day')->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $report = db_one($db,"SELECT
        COUNT(CASE WHEN status IN ('completed','partial_refund','refunded') THEN 1 END) AS sale_count,
        COALESCE(SUM(CASE WHEN status IN ('completed','partial_refund','refunded') THEN total ELSE 0 END),0) AS gross,
        (SELECT COALESCE(SUM(amount),0) FROM pos_refunds WHERE company_id = ? AND created_at >= ? AND created_at < ?) AS refunds,
        COALESCE(SUM(CASE WHEN status IN ('completed','partial_refund','refunded') AND payment_method = 'cash' THEN total ELSE 0 END),0) AS cash,
        COALESCE(SUM(CASE WHEN status IN ('completed','partial_refund','refunded') AND payment_method = 'card' THEN total ELSE 0 END),0) AS card,
        COALESCE(SUM(CASE WHEN status IN ('completed','partial_refund','refunded') AND payment_method = 'other' THEN total ELSE 0 END),0) AS other
        FROM pos_sales WHERE company_id = ? AND created_at >= ? AND created_at < ?",[$companyId,$utcStart,$utcEnd,$companyId,$utcStart,$utcEnd]) ?: [];
    json_response(['ok'=>true,'currency'=>$currency,'can_edit'=>module_access($db,$user,'pos') === 'edit','products'=>$products,'registers'=>$registers,'my_shift'=>$myShift,'customers'=>$customers,'sales'=>$sales,'report'=>$report]);
}

if ($method === 'GET') {
    if ($action === 'bootstrap') {
        $settings = workspace_settings($db,$companyId);
        pos_bootstrap($db,$user,$companyId,(string)($settings['currency'] ?? ''),(string)($settings['timezone'] ?? config('APP_TIMEZONE','Africa/Lagos')));
    }
    if ($action === 'sale') {
        $saleId = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$saleId) fail('Choose a sale to view.',422);
        $sale = db_one($db,'SELECT s.*, CONCAT(c.first_name,\' \',c.last_name) AS customer_name FROM pos_sales s LEFT JOIN customers c ON c.id = s.customer_id AND c.company_id = s.company_id WHERE s.id = ? AND s.company_id = ?',[$saleId,$companyId]);
        if (!$sale) fail('This sale could not be found in your workspace.',404);
        $items = db_rows($db,'SELECT item_name, quantity, unit_price, line_total FROM pos_sale_items WHERE sale_id = ? AND company_id = ? ORDER BY id',[$saleId,$companyId]);
        $refunds = db_rows($db,'SELECT amount, refund_method, restocked, note, created_at FROM pos_refunds WHERE sale_id = ? AND company_id = ? ORDER BY id DESC',[$saleId,$companyId]);
        json_response(['ok'=>true,'sale'=>$sale,'items'=>$items,'refunds'=>$refunds]);
    }
    fail('Choose a valid POS view.',404);
}

require_post();
pos_require_write($db,$user,false);
$db->begin_transaction();
try {
    pos_require_write($db,$user,true);
    $actor = pos_actor();
    pos_reservations_expire($db,$companyId);

    if ($action === 'create_register') {
        if ($actor['role'] !== 'admin') fail('Only the workspace owner can add a register.',403);
        $name = trim(is_string($body['name'] ?? null) ? $body['name'] : '');
        if ($name === '' || strlen($name) > 100) fail('Enter a register name up to 100 characters.',422);
        db_run($db,'INSERT INTO pos_registers (company_id,name,created_by) VALUES (?,?,?)',[$companyId,$name,$actor['id']]);
        $id = (int)$db->insert_id;
        audit_event($db,$companyId,'pos.register_created','pos_registers',(string)$id,['name'=>$name]);
        $db->commit();
        json_response(['ok'=>true,'id'=>$id],201);
    }

    if ($action === 'open_shift') {
        $registerId = filter_var($body['register_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$registerId) fail('Choose a register to open.',422);
        $openingCash = pos_money_cents($body['opening_cash'] ?? '0','opening cash');
        pos_lock_actor($db,$user);
        if (db_one($db,"SELECT id FROM pos_shifts WHERE company_id = ? AND opened_by_role = ? AND opened_by_id = ? AND status = 'open' LIMIT 1 FOR UPDATE",[$companyId,$actor['role'],$actor['id']])) fail('Close your existing register shift before opening another.',409);
        $register = db_one($db,'SELECT id,name FROM pos_registers WHERE id = ? AND company_id = ? AND is_active = 1 FOR UPDATE',[$registerId,$companyId]);
        if (!$register) fail('This register is unavailable in your workspace.',404);
        if (db_one($db,"SELECT id FROM pos_shifts WHERE company_id = ? AND register_id = ? AND status = 'open' FOR UPDATE",[$companyId,$registerId])) fail('This register already has an open shift.',409);
        db_run($db,"INSERT INTO pos_shifts (company_id,register_id,opened_by_role,opened_by_id,status,opening_cash) VALUES (?,?,?,?,'open',?)",[$companyId,$registerId,$actor['role'],$actor['id'],pos_amount($openingCash)]);
        $id = (int)$db->insert_id;
        audit_event($db,$companyId,'pos.shift_opened','pos_shifts',(string)$id,['register_id'=>(int)$registerId,'opening_cash'=>pos_amount($openingCash)]);
        $db->commit();
        json_response(['ok'=>true,'id'=>$id],201);
    }

    if ($action === 'close_shift') {
        $shiftId = filter_var($body['shift_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$shiftId) fail('Choose an open shift to close.',422);
        $shift = pos_shift($db,$companyId,(int)$shiftId,true);
        if (!$shift || $shift['status'] !== 'open') fail('This register shift is no longer open.',409);
        pos_require_operator($user,$shift);
        if (db_one($db,"SELECT id FROM pos_sales WHERE company_id = ? AND register_shift_id = ? AND status = 'reserved' LIMIT 1 FOR UPDATE",[$companyId,$shiftId])) fail('Complete or cancel reserved sales before closing this shift.',409);
        $cashSales = pos_db_cents(db_one($db,"SELECT COALESCE(SUM(total),0) AS amount FROM pos_sales WHERE company_id = ? AND register_shift_id = ? AND status IN ('completed','partial_refund','refunded') AND payment_method = 'cash'",[$companyId,$shiftId])['amount'] ?? '0');
        $cashRefunds = pos_db_cents(db_one($db,"SELECT COALESCE(SUM(amount),0) AS amount FROM pos_refunds WHERE company_id = ? AND register_shift_id = ? AND refund_method = 'cash'",[$companyId,$shiftId])['amount'] ?? '0');
        $expected = pos_db_cents($shift['opening_cash']) + $cashSales - $cashRefunds;
        $counted = pos_money_cents($body['closing_cash'] ?? null,'closing cash');
        db_run($db,"UPDATE pos_shifts SET status = 'closed',closed_by_role = ?,closed_by_id = ?,closing_cash = ?,expected_cash = ?,closed_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND status = 'open'",[$actor['role'],$actor['id'],pos_amount($counted),pos_amount($expected),$shiftId,$companyId]);
        audit_event($db,$companyId,'pos.shift_closed','pos_shifts',(string)$shiftId,['closing_cash'=>pos_amount($counted),'expected_cash'=>pos_amount($expected),'variance'=>pos_amount($counted-$expected)]);
        $db->commit();
        json_response(['ok'=>true,'expected_cash'=>pos_amount($expected),'variance'=>pos_amount($counted-$expected)]);
    }

    if ($action === 'create_sale') {
        $settings = workspace_settings($db,$companyId);
        $currency = strtoupper((string)($settings['currency'] ?? ''));
        if (!isset(supported_currencies()[$currency])) fail('Set a supported workspace currency in Settings before opening the POS.',409);
        $shiftId = filter_var($body['shift_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$shiftId) fail('Open a register shift before starting a sale.',409);
        $shift = pos_shift($db,$companyId,(int)$shiftId,true);
        if (!$shift || $shift['status'] !== 'open') fail('This register shift is no longer open.',409);
        pos_require_operator($user,$shift);
        $itemsInput = $body['items'] ?? null;
        if (!is_array($itemsInput) || count($itemsInput) < 1 || count($itemsInput) > 50) fail('Add between 1 and 50 product lines to the sale.',422);
        $quantities = [];
        foreach ($itemsInput as $item) {
            if (!is_array($item)) fail('Choose valid products and quantities.',422);
            $itemId = filter_var($item['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            $quantity = filter_var($item['quantity'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]);
            if (!$itemId || !$quantity) fail('Choose valid products and quantities.',422);
            $quantities[$itemId] = ($quantities[$itemId] ?? 0) + $quantity;
            if ($quantities[$itemId] > 10000) fail('A product quantity cannot exceed 10,000.',422);
        }
        ksort($quantities,SORT_NUMERIC);
        $inventory = pos_inventory_rows($db,$companyId,array_map('intval',array_keys($quantities)),true);
        if (count($inventory) !== count($quantities)) fail('One or more products are no longer in this workspace inventory.',404);
        $reservedQuantities = pos_reserved_quantities($db,$companyId,array_map('intval',array_keys($quantities)));
        $customerId = null;
        if (!empty($body['customer_id'])) {
            $customerId = filter_var($body['customer_id'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            if (!$customerId || !db_one($db,'SELECT id FROM customers WHERE id = ? AND company_id = ?',[$customerId,$companyId])) fail('Choose a customer from your workspace.',422);
        }
        $discount = $body['discount_percent'] ?? '0';
        if ((!is_string($discount) && !is_int($discount) && !is_float($discount)) || !preg_match('/^(?:0|[1-9][0-9]?)(?:\.[0-9]{1,2})?$/D',(string)$discount) || (float)$discount > 100) fail('Enter a discount between 0 and 100 percent.',422);
        $discountParts = array_pad(explode('.',(string)$discount,2),2,'');
        $discountBasisPoints = (int)$discountParts[0] * 100 + (int)str_pad($discountParts[1],2,'0');
        $lines = []; $subtotal = 0;
        foreach ($inventory as $product) {
            $itemId = (int)$product['id'];
            $reserved = $reservedQuantities[$itemId] ?? 0;
            $quantity = $quantities[$itemId];
            if ((int)$product['quantity'] - $reserved < $quantity) fail('Not enough available stock for ' . $product['name'] . '.',409);
            $unitCents = pos_db_cents($product['unit_price']);
            $lineCents = $unitCents * $quantity;
            $subtotal += $lineCents;
            $lines[] = ['id'=>$itemId,'name'=>$product['name'],'quantity'=>$quantity,'unit'=>pos_amount($unitCents),'line'=>pos_amount($lineCents)];
        }
        if ($subtotal > 999999999999) fail('This sale total is too large.',422);
        $discountCents = intdiv($subtotal * $discountBasisPoints + 5000,10000);
        $totalCents = $subtotal - $discountCents;
        $reference = 'S-' . gmdate('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        db_run($db,"INSERT INTO pos_sales (company_id,register_shift_id,reference,operator_role,operator_id,customer_id,currency,status,subtotal,discount_percent,discount_amount,total,expires_at) VALUES (?,?,?,?,?,?,?,'reserved',?,?,?,?,UTC_TIMESTAMP() + INTERVAL 20 MINUTE)",[$companyId,$shiftId,$reference,$actor['role'],$actor['id'],$customerId,$currency,pos_amount($subtotal),number_format($discountBasisPoints/100,2,'.',''),pos_amount($discountCents),pos_amount($totalCents)]);
        $saleId = (int)$db->insert_id;
        foreach ($lines as $line) db_run($db,'INSERT INTO pos_sale_items (sale_id,company_id,inventory_item_id,item_name,quantity,unit_price,line_total) VALUES (?,?,?,?,?,?,?)',[$saleId,$companyId,$line['id'],$line['name'],$line['quantity'],$line['unit'],$line['line']]);
        audit_event($db,$companyId,'pos.sale_reserved','pos_sales',(string)$saleId,['reference'=>$reference,'total'=>pos_amount($totalCents),'currency'=>$currency]);
        $db->commit();
        json_response(['ok'=>true,'id'=>$saleId,'reference'=>$reference,'expires_in'=>1200],201);
    }

    if ($action === 'complete_sale') {
        $saleId = filter_var($body['sale_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$saleId) fail('Choose a sale to complete.',422);
        $sale = pos_sale($db,$companyId,(int)$saleId,true);
        if (!$sale || $sale['status'] !== 'reserved' || !$sale['expires_at'] || strtotime($sale['expires_at'] . ' UTC') <= time()) fail('This reservation has expired or is no longer available. Refresh the POS and start a new sale.',409);
        $shift = pos_shift($db,$companyId,(int)$sale['register_shift_id'],true);
        if (!$shift || $shift['status'] !== 'open') fail('Open the register shift before completing this sale.',409);
        pos_require_operator($user,$shift);
        $items = db_rows($db,'SELECT inventory_item_id,quantity FROM pos_sale_items WHERE sale_id = ? AND company_id = ? ORDER BY inventory_item_id FOR UPDATE',[$saleId,$companyId]);
        $ids = array_map(fn($item)=>(int)$item['inventory_item_id'],$items);
        $inventory = pos_inventory_rows($db,$companyId,$ids,true);
        if (count($items) !== count($inventory)) fail('The reserved inventory could not be verified. No stock was changed.',409);
        foreach ($items as $item) {
            $product = null;
            foreach ($inventory as $candidate) if ((int)$candidate['id'] === (int)$item['inventory_item_id']) { $product = $candidate; break; }
            if (!$product || (int)$product['quantity'] < (int)$item['quantity']) fail('Inventory changed below the reserved quantity. No stock was changed.',409);
        }
        $paymentMethod = is_string($body['payment_method'] ?? null) ? $body['payment_method'] : '';
        if (!in_array($paymentMethod,['cash','card','other'],true)) fail('Choose cash, card, or other as the recorded payment method.',422);
        $total = pos_db_cents($sale['total']);
        $cashReceived = $total; $change = 0;
        if ($paymentMethod === 'cash') {
            $cashReceived = pos_money_cents($body['cash_received'] ?? null,'cash received');
            if ($cashReceived < $total) fail('Cash received must cover the sale total.',422);
            $change = $cashReceived - $total;
        }
        foreach ($items as $item) {
            $updated = db_run($db,'UPDATE inventory_items SET quantity = quantity - ? WHERE id = ? AND company_id = ? AND quantity >= ?',[(int)$item['quantity'],(int)$item['inventory_item_id'],$companyId,(int)$item['quantity']]);
            if ($updated->affected_rows !== 1) fail('Inventory changed during checkout. No stock was changed.',409);
        }
        db_run($db,"UPDATE pos_sales SET status = 'completed',payment_method = ?,cash_received = ?,change_due = ?,expires_at = NULL,completed_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND status = 'reserved'",[$paymentMethod,$paymentMethod === 'cash' ? pos_amount($cashReceived) : null,$paymentMethod === 'cash' ? pos_amount($change) : null,$saleId,$companyId]);
        audit_event($db,$companyId,'pos.sale_completed','pos_sales',(string)$saleId,['reference'=>$sale['reference'],'payment_method'=>$paymentMethod,'total'=>$sale['total']]);
        $db->commit();
        json_response(['ok'=>true,'reference'=>$sale['reference'],'change_due'=>pos_amount($change)]);
    }

    if ($action === 'cancel_sale') {
        $saleId = filter_var($body['sale_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$saleId) fail('Choose a sale to cancel.',422);
        $sale = pos_sale($db,$companyId,(int)$saleId,true);
        if (!$sale || $sale['status'] !== 'reserved') fail('Only an active stock reservation can be cancelled.',409);
        $shift = db_one($db,"SELECT * FROM pos_shifts WHERE company_id = ? AND opened_by_role = ? AND opened_by_id = ? AND status = 'open' LIMIT 1 FOR UPDATE",[$companyId,$actor['role'],$actor['id']]);
        if (!$shift) fail('Open your register shift before recording a refund.',409);
        pos_require_operator($user,$shift);
        db_run($db,"UPDATE pos_sales SET status = 'cancelled',expires_at = NULL,cancelled_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND status = 'reserved'",[$saleId,$companyId]);
        audit_event($db,$companyId,'pos.sale_cancelled','pos_sales',(string)$saleId,['reference'=>$sale['reference']]);
        $db->commit();
        json_response(['ok'=>true]);
    }

    if ($action === 'refund_sale') {
        $saleId = filter_var($body['sale_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$saleId) fail('Choose a completed sale to refund.',422);
        $sale = pos_sale($db,$companyId,(int)$saleId,true);
        if (!$sale || !in_array($sale['status'],['completed','partial_refund'],true)) fail('Only completed sales with a remaining refundable balance can be refunded.',409);
        $shift = pos_shift($db,$companyId,(int)$sale['register_shift_id'],true);
        if (!$shift) fail('This sale has no valid register shift.',409);
        pos_require_operator($user,$shift);
        $amount = pos_money_cents($body['amount'] ?? null,'refund amount',false);
        $remaining = pos_db_cents($sale['total']) - pos_db_cents($sale['refunded_amount']);
        if ($amount > $remaining) fail('The refund cannot exceed the remaining sale balance.',422);
        $method = is_string($body['refund_method'] ?? null) ? $body['refund_method'] : '';
        if (!in_array($method,['cash','card','other'],true)) fail('Choose cash, card, or other as the recorded refund method.',422);
        $restock = ($body['restock'] ?? false) === true || ($body['restock'] ?? '') === '1';
        if ($restock && ($amount !== $remaining || pos_db_cents($sale['refunded_amount']) !== 0)) fail('Restock is available only when refunding the full sale before any earlier refund.',422);
        if ($restock && !empty($sale['restocked_at'])) fail('This sale’s items have already been restocked.',409);
        $note = trim(is_string($body['note'] ?? null) ? $body['note'] : '');
        if (strlen($note) > 255) fail('Refund notes must be 255 characters or fewer.',422);
        if ($restock) {
            $items = db_rows($db,'SELECT inventory_item_id,quantity FROM pos_sale_items WHERE sale_id = ? AND company_id = ? ORDER BY inventory_item_id FOR UPDATE',[$saleId,$companyId]);
            $ids = array_map(fn($item)=>(int)$item['inventory_item_id'],$items);
            $inventory = pos_inventory_rows($db,$companyId,$ids,true);
            if (count($items) !== count($inventory)) fail('The original inventory items could not be verified. No refund was recorded.',409);
            foreach ($items as $item) db_run($db,'UPDATE inventory_items SET quantity = quantity + ? WHERE id = ? AND company_id = ?',[(int)$item['quantity'],(int)$item['inventory_item_id'],$companyId]);
        }
        $newRefunded = pos_db_cents($sale['refunded_amount']) + $amount;
        $newStatus = $newRefunded >= pos_db_cents($sale['total']) ? 'refunded' : 'partial_refund';
        db_run($db,'INSERT INTO pos_refunds (company_id,sale_id,register_shift_id,operator_role,operator_id,amount,refund_method,restocked,note) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,$saleId,$shift['id'],$actor['role'],$actor['id'],pos_amount($amount),$method,$restock ? 1 : 0,$note]);
        db_run($db,'UPDATE pos_sales SET refunded_amount = ?,status = ?,restocked_at = IF(?,UTC_TIMESTAMP(),restocked_at) WHERE id = ? AND company_id = ?', [pos_amount($newRefunded),$newStatus,$restock ? 1 : 0,$saleId,$companyId]);
        audit_event($db,$companyId,'pos.sale_refunded','pos_sales',(string)$saleId,['reference'=>$sale['reference'],'amount'=>pos_amount($amount),'method'=>$method,'restocked'=>$restock]);
        $db->commit();
        json_response(['ok'=>true,'status'=>$newStatus,'refunded_amount'=>pos_amount($newRefunded)]);
    }

    fail('Choose a valid POS action.',422);
} catch (mysqli_sql_exception $error) {
    $db->rollback();
    if ($error->getCode() === 1062) fail($action === 'create_register' ? 'A register with that name already exists.' : 'This POS record conflicts with an existing record. Review the latest sale and try again.',409);
    throw $error;
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
