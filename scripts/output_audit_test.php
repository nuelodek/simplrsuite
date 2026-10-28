<?php
declare(strict_types=1);
// Static output-escaping audit for every routable page and the shared app/ code.
// Flags each echo / print / short echo that outputs a variable, an interpolated
// string or a function result that is not known to return escaped or sanitised
// HTML. Reviewed exceptions live in REVIEWED below with the reason. Exit 1 on any
// unreviewed finding. Static only: it never runs the pages.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
chdir($root);

// Helpers that return escaped text or sanitised HTML (checked by reading each one).
const SAFE_CALLS = [
    'e','htmlspecialchars','csrf_field','icon','brand','rich_text_html','record_cell_html','progress_html',
    'number_format','count','intval','floatval','json_encode','rawurlencode','urlencode','http_build_query',
    'attachment_size','accounting_currency_amount','date','gmdate','implode_safe','show_flash','stage_class',
    'nav_html','money','money_html','badge','time_ago','notification_icon','e_attr','min','max','round','abs',
    'attachment_accept','attachment_types_label','attachment_limit','auth_start','auth_end','page_start','page_end',
    'number','strlen','mb_strlen','ceil','floor','http_response_code',
];
// Reviewed 28 Oct 2026: file => [operand prefix => why it cannot carry user HTML].
$reviewed = [
    'app/bootstrap.php' => ['$home' => 'fixed path chosen by role in a match()', '$label' => 'fixed text chosen by role in a match()'],
    'app/github-panel.php' => ['$id' => 'record id validated with FILTER_VALIDATE_INT by users/admin/workspace.php'],
    'app/record-detail.php' => ['$id' => 'record id validated with FILTER_VALIDATE_INT by users/admin/workspace.php'],
    'app/talent-profile.php' => ['$max' => 'integer length limits from a literal list'],
    'app/view.php' => ['$unread' => 'integer count', '$initial' => 'escaped with e() where it is assigned'],
    'users/admin/accounting.php' => ['$money(' => 'closure returns e(accounting_currency_amount())', '$journalLine(' => 'closure escapes account names with e(); other parts are fixed', 'strtolower($label)' => 'labels from accounting_categories()'],
    'users/admin/billing.php' => ['$currency' => 'allowlisted to NGN or USD on line 7', '$months' => 'integers from billing_catalog()', '$tool[' => 'integer amounts from billing_catalog()'],
    'users/admin/calendar.php' => ['$chunk' => 'iCalendar text, escaped for ICS, served as text/calendar', '$line' => 'iCalendar text, escaped for ICS', '$dayNumber' => 'loop integer'],
    'users/admin/github.php' => ['$index' => 'array index', '$extra' => 'for-loop counter 0..1'],
    'users/admin/newdashboard.php' => ["implode(' and '" => 'fixed links joined with integer counts', '$overdue' => 'integer count', '$index' => 'loop integer', '$colour' => 'fixed colour list', '$status' => 'fixed status list', '$taskStatus[' => 'integer counts', '$label' => 'fixed setup checklist labels'],
    'users/admin/notifications.php' => ['$unread' => 'integer count', '$value' => 'fixed email-mode options'],
    'users/admin/queries.php' => ['$value' => 'fixed filter options', '$label' => 'fixed filter labels', '$filter' => 'allowlisted to open, closed or all'],
    'users/admin/query.php' => ['$minutes' => 'fixed response-window list'],
    'users/admin/settings.php' => ['$minutes' => 'fixed response-window list', '$months' => 'fixed warning-expiry list'],
    'users/admin/workspace.php' => ["\$field['max']" => 'integer limit from module config'],
    'users/employees/workspace.php' => ['$status' => 'fixed status list', 'ucfirst($status)' => 'fixed status list'],
];

$targets = array_unique(array_merge(array_values(require 'app/routes.php'), glob('app/*.php'), glob('app/landing/*.php')));
sort($targets);
$findings = [];
foreach ($targets as $file) {
    if (!is_file($file)) continue;
    $source = file_get_contents($file);
    $tokens = token_get_all($source);
    $count = count($tokens);
    // Variables this file only ever assigns from an integer cast, validator or count.
    preg_match_all('/(\$[A-Za-z_]\w*)\s*=\s*(\(int\)|\(float\)|filter_var\([^;]*FILTER_VALIDATE_INT|max\(|min\(|count\(|intdiv\(|ceil\(|floor\(|round\(|array_sum\(|(?:int)?val\()/', $source, $numeric);
    preg_match_all('/(\$[A-Za-z_]\w*)\s*=(?!=)/', $source, $assigned);
    $numericVars = array_diff(array_unique($numeric[1]), array_diff($assigned[1], $numeric[1]));
    $GLOBALS['numeric_vars'] = array_flip($numericVars);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!is_array($token) || !in_array($token[0], [T_ECHO, T_PRINT, T_OPEN_TAG_WITH_ECHO], true)) continue;
        $line = $token[2];
        // Collect the expression up to the semicolon or the closing tag.
        $expr = [];
        for ($j = $i + 1; $j < $count; $j++) {
            $t = $tokens[$j];
            if ($t === ';' || (is_array($t) && $t[0] === T_CLOSE_TAG)) break;
            $expr[] = $t;
        }
        $i = $j;
        foreach (unsafe_operands($expr) as $operand) $findings[] = [$file, $line, $operand];
    }
}

// Splits on top-level concatenation, commas and ternary branches; returns operands that may be unescaped.
function unsafe_operands(array $expr): array {
    $parts = []; $current = []; $depth = 0; $ternary = false;
    foreach ($expr as $t) {
        $text = is_array($t) ? $t[1] : $t;
        if (in_array($text, ['(', '[', '{'], true) || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) $depth++;
        if (in_array($text, [')', ']', '}'], true)) $depth--;
        if ($depth === 0 && in_array($text, ['.', ','], true)) { $parts[] = $current; $current = []; continue; }
        if ($depth === 0 && $text === '?') { $ternary = true; $current = []; continue; }   // drop the condition
        if ($depth === 0 && $text === ':' && $ternary) { $parts[] = $current; $current = []; continue; }
        if ($depth === 0 && is_array($t) && $t[0] === T_COALESCE) { $parts[] = $current; $current = []; continue; }
        $current[] = $t;
    }
    $parts[] = $current;
    $unsafe = [];
    foreach ($parts as $part) {
        $part = array_values(array_filter($part, fn($t) => !(is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))));
        if (!$part) continue;
        $first = $part[0];
        $source = trim(implode('', array_map(fn($t) => is_array($t) ? $t[1] : $t, $part)));
        if (is_array($first) && in_array($first[0], [T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_DNUMBER, T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true) && count($part) === 1) continue;
        if (is_array($first) && in_array($first[0], [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true)) continue;
        // Numeric variables and arithmetic on them ($page, $page + 1).
        $numericOnly = true;
        foreach ($part as $t) {
            if (is_array($t) && $t[0] === T_VARIABLE && isset($GLOBALS['numeric_vars'][$t[1]])) continue;
            if (is_array($t) && in_array($t[0], [T_LNUMBER, T_DNUMBER], true)) continue;
            if (!is_array($t) && in_array($t, ['+', '-', '*', '/', '%', '(', ')'], true)) continue;
            $numericOnly = false; break;
        }
        if ($numericOnly) continue;
        if (is_array($first) && $first[0] === T_STRING && in_array(strtolower($first[1]), SAFE_CALLS, true) && ($part[1] ?? null) === '(') continue;
        if (is_array($first) && in_array($first[0], [T_STRING], true) && in_array(strtoupper($first[1]), ['PHP_EOL','TRUE','FALSE','NULL'], true)) continue;
        // A parenthesised expression is as safe as its own operands: (cond ? 'a' : e($b)).
        if ($first === '(' && end($part) === ')') {
            $inner = array_slice($part, 1, -1);
            $unsafe = array_merge($unsafe, unsafe_operands($inner));
            continue;
        }
        // nl2br() only adds <br> tags; safe when its argument is escaped.
        if (is_array($first) && $first[0] === T_STRING && strtolower($first[1]) === 'nl2br' && is_array($part[2] ?? null) && strtolower($part[2][1]) === 'e') continue;
        if ($first === '"') { // interpolated string: unsafe only if it contains a variable
            if (!array_filter($part, fn($t) => is_array($t) && in_array($t[0], [T_VARIABLE, T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) continue;
        }
        if (is_array($first) && $first[0] === T_START_HEREDOC) {
            if (!array_filter($part, fn($t) => is_array($t) && in_array($t[0], [T_VARIABLE, T_CURLY_OPEN], true))) continue;
        }
        $unsafe[] = mb_strimwidth(preg_replace('/\s+/', ' ', $source), 0, 90, '…');
    }
    return $unsafe;
}

$isReviewed = function (array $finding) use ($reviewed): bool {
    foreach ($reviewed[$finding[0]] ?? [] as $prefix => $reason) if (str_starts_with($finding[2], $prefix)) return true;
    return false;
};
$open = array_values(array_filter($findings, fn($f) => !$isReviewed($f)));
foreach ($open as [$file, $line, $operand]) echo str_pad("$file:$line", 46), $operand, PHP_EOL;
echo json_encode(['files' => count($targets), 'findings' => count($findings), 'reviewed' => count($findings) - count($open), 'open' => count($open)]) . PHP_EOL;
exit($open ? 1 : 0);
