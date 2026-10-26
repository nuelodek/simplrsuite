<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function icon(string $name, string $class = ''): string {
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'folder' => '<path d="M3 7V5a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'task' => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="m8 12 3 3 5-6"/>',
        'users' => '<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v3"/>',
        'chart' => '<path d="M4 3v17h17M8 15v-4m5 4V7m5 8V4"/>',
        'wallet' => '<rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 8V5a2 2 0 0 1 2-2h12v2m4 6h-6v5h6"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V3h8v4M3 12a24 24 0 0 0 18 0M12 12v3"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6Zm-4 9 3 3 5-6"/>',
        'box' => '<path d="m12 3 9 5v9l-9 5-9-5V8Zm0 10v9M3 8l9 5 9-5M8 5l9 5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18M8 15h2m4 0h2"/>',
        'plug' => '<path d="M8 3v5m8-5v5M6 8h12v3a6 6 0 0 1-12 0Zm6 9v4"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'logout' => '<path d="M9 4H4v16h5m5-12 4 4-4 4m-6-4h13"/>',
        'spark' => '<path d="m12 3 3 6 6 3-6 3-3 6-3-6-6-3 6-3Z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"/>',
    ];
    return '<svg class="icon ' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['grid']) . '</svg>';
}
function page_head(string $title, array $metadata = []): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#172f2b"><title>' . e($title) . ' · Simplr Suite</title><link rel="icon" href="/public/images/brand.svg" type="image/svg+xml"><link rel="stylesheet" href="/public/styles/suite.css"><link rel="stylesheet" href="/public/styles/toast.css"><script src="/public/scripts/toast.js" defer></script><script src="/public/scripts/suite.js" defer></script>';
    if (isset($metadata['description'])) echo '<meta name="description" content="' . e($metadata['description']) . '"><meta property="og:description" content="' . e($metadata['description']) . '"><meta property="og:title" content="' . e($title) . '"><meta property="og:type" content="website">';
    if (isset($metadata['canonical'])) echo '<link rel="canonical" href="' . e($metadata['canonical']) . '"><meta property="og:url" content="' . e($metadata['canonical']) . '">';
    if (isset($metadata['robots'])) echo '<meta name="robots" content="' . e($metadata['robots']) . '">';
    echo '</head>';
}
function brand(): string { return '<a class="brand" href="/" aria-label="Simplr Suite home"><span class="brand-mark">s</span>simplr<span class="brand-suffix">suite</span></a>'; }
function auth_start(string $title, string $subtitle): void {
    page_head($title);
    echo '<body class="auth-layout"><aside class="auth-story">' . brand() . '<div><span class="eyebrow">LESS BUSYWORK. MORE BUSINESS.</span><h2>A little more clarity.<br>A lot more possibility.</h2><p>Bring your people, projects, and everyday operations together in one connected workspace.</p><div class="story-orbit">' . icon('grid') . icon('users') . icon('chart') . icon('folder') . '</div></div><small>Built for the way your business works.</small></aside><main class="auth-main"><a class="back-link" href="/">← Back to Simplr</a><div class="auth-card"><span class="eyebrow">YOUR WORK, SIMPLIFIED</span><h1>' . e($title) . '</h1><p class="subtle">' . e($subtitle) . '</p>';
    show_flash();
}
function auth_end(): void { echo '</div><p class="auth-footer">Simplr Suite · A calmer way to run your business</p></main></body></html>'; }
function suite_start(string $title, string $active, array $user): void {
    page_head($title);
    $nav = [
        ['overview','Overview','grid','/users/admin/newdashboard.php'],
        ['projects','Projects','folder','/users/admin/workspace.php?module=projects'],
        ['tasks','Tasks','task','/users/admin/workspace.php?module=tasks'],
        ['people','People & teams','users','/users/admin/workspace.php?module=departments'],
        ['sales','Sales & CRM','chart','/users/admin/workspace.php?module=leads'],
        ['finance','Finance','wallet','/users/admin/workspace.php?module=expenses'],
        ['recruiting','Recruiting','briefcase','/users/admin/talent-directory.php'],
        ['calendar','Calendar','calendar','/users/admin/calendar.php'],
        ['reports','Reports','chart','/users/admin/reports.php'],
        ['activity','Activity','shield','/users/admin/activity.php'],
        ['help','Workspace help','users','/users/help.php'],
        ['settings','Settings','settings','/users/admin/settings.php'],
        ['access','Staff access','shield','/users/admin/access.php'],
        ['modules','All tools','box','/users/admin/modules.php'],
        ['billing','Billing','wallet','/users/admin/billing.php'],
        ['integrations','Integrations','plug','/users/admin/integrations.php'],
    ];
    if (in_array($_SESSION['auth_role'] ?? '', ['employee','manager'], true)) {
        $nav = [['overview','My workspace','grid','/users/employees/workspace.php'], ['tasks','My tasks','task','/users/employees/workspace.php#tasks'], ['modules','My tools','box','/users/admin/modules.php'], ['reports','Reports','chart','/users/admin/reports.php'], ['help','Workspace help','users','/users/help.php'], ['profile','My profile','users','/users/employees/workspace.php#profile']];
    }
    if (($_SESSION['auth_role'] ?? '') === 'talent') {
        $nav = [['profile','My profile','users','/users/talents/talentpool.php'], ['jobs','Browse roles','briefcase','/jobs/'], ['applications','My applications','task','/users/talents/applications.php'], ['security','Account security','shield','/users/talents/resetpassword.php']];
    }
    $workspaceKind = ($_SESSION['auth_role'] ?? '') === 'talent' ? 'Talent workspace' : 'Business workspace';
    echo '<body class="suite"><a class="skip-link" href="#main">Skip to content</a><aside class="suite-sidebar" id="sidebar">' . brand() . '<button type="button" class="icon-button sidebar-close" data-menu-close aria-label="Close navigation">×</button><div class="workspace-label"><span class="workspace-avatar">' . e(mb_strtoupper(mb_substr($user['company_name'], 0, 1))) . '</span><div><strong>' . e($user['company_name']) . '</strong><small>' . e($workspaceKind) . '</small></div></div><span class="nav-caption">WORKSPACE</span><nav aria-label="Main navigation">';
    foreach ($nav as [$id,$label,$glyph,$url]) echo '<a href="' . e($url) . '"' . ($id === $active ? ' class="active" aria-current="page"' : '') . '>' . icon($glyph) . e($label) . '</a>';
    echo '</nav><div class="sidebar-bottom"><div class="sidebar-note">' . icon('spark') . '<strong>Space to do your best work.</strong><p>Everything you need, a little simpler.</p></div><form method="post" action="/users/admin/logout.php">' . csrf_field() . '<button class="logout-button" type="submit">' . icon('logout') . 'Sign out</button></form></div></aside><button type="button" class="menu-backdrop" data-menu-close aria-label="Close navigation" hidden></button><div class="suite-body"><header class="suite-topbar"><button class="icon-button mobile-menu" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false" data-menu>' . icon('menu') . '</button><span class="breadcrumb">Workspace <span>/</span> <strong>' . e($title) . '</strong></span><div class="topbar-end"><span class="workspace-pill"><span class="status-dot"></span> ' . e($workspaceKind) . '</span><span class="avatar" title="' . e($user['email']) . '">' . e(mb_strtoupper(mb_substr($user['company_name'],0,1))) . '</span></div></header><main id="main" class="suite-main">';
    show_flash();
}
function suite_end(): void { echo '<footer class="suite-footer">Made for a simpler workday.<span>Simplr Suite</span></footer></main></div></body></html>'; }
