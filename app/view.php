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
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2"/>',
        'pulse' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
        'card' => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6.5 15h4"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.2a2.5 2.5 0 1 1 3.4 2.3c-.7.3-1 .8-1 1.6M12 16.8h.01"/>',
        'book' => '<path d="M5 4.5A2.5 2.5 0 0 1 7.5 2H20v16H7.5A2.5 2.5 0 0 0 5 20.5Zm0 16A2.5 2.5 0 0 0 7.5 23H20v-5M9 7h7m-7 4h5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'columns' => '<rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="10" y="4" width="5" height="11" rx="1.5"/><rect x="17" y="4" width="4" height="7" rx="1.5"/>',
        'list' => '<path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
        'trend' => '<path d="m3 17 6-6 4 4 8-8m-6 0h6v6"/>',
        'alert' => '<path d="M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4m0 4h.01"/>',
        'chevron' => '<path d="m6 9 6 6 6-6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'trash' => '<path d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/>',
        'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6m-6 4h4"/>',
        'upload' => '<path d="M12 16V4m-5 5 5-5 5 5M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
        'download' => '<path d="M12 4v12m-5-5 5 5 5-5M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>',
        'github' => '<path d="M9 19c-4 1.3-4-2-6-2.5m12 5v-3.4a3 3 0 0 0-.8-2.3c2.7-.3 5.5-1.3 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.3 4.3 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.6 11.6 0 0 0-6 0C6.5 2.1 5.5 2.4 5.5 2.4a4.3 4.3 0 0 0-.1 3.2A4.6 4.6 0 0 0 4 8.8c0 4.6 2.8 5.7 5.5 6a3 3 0 0 0-.8 2.3V21"/>',
        'branch' => '<circle cx="6" cy="5" r="2"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="7" r="2"/><path d="M6 7v10m12-8c0 5-6 4-11 8"/>',
        'refresh' => '<path d="M20 11a8 8 0 0 0-14.8-4M4 4v4h4m-4 5a8 8 0 0 0 14.8 4M20 20v-4h-4"/>',
    ];
    return '<svg class="icon ' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['grid']) . '</svg>';
}
function page_head(string $title, array $metadata = []): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#172f2b"><title>' . e($title) . ' · Simplr Suite</title><link rel="icon" href="/public/images/brand.svg" type="image/svg+xml"><link rel="stylesheet" href="/public/styles/suite.css"><link rel="stylesheet" href="/public/styles/toast.css"><script src="/public/scripts/toast.js" defer></script><script src="/public/scripts/suite.js" defer></script><script src="/public/scripts/richtext.js" defer></script>';
    if (isset($metadata['description'])) echo '<meta name="description" content="' . e($metadata['description']) . '"><meta property="og:description" content="' . e($metadata['description']) . '"><meta property="og:title" content="' . e($title) . '"><meta property="og:type" content="website">';
    if (isset($metadata['canonical'])) echo '<link rel="canonical" href="' . e($metadata['canonical']) . '"><meta property="og:url" content="' . e($metadata['canonical']) . '">';
    if (isset($metadata['robots'])) echo '<meta name="robots" content="' . e($metadata['robots']) . '">';
    echo '</head>';
}
function brand(): string { return '<a class="brand" href="/" aria-label="Simplr Suite home"><svg class="brand-logo" viewBox="0 0 40 40" aria-hidden="true"><rect width="40" height="40" rx="12" fill="#172f2b"/><path d="M27 12H17a5 5 0 0 0 0 10h6a3 3 0 0 1 0 6H13" fill="none" stroke="#cce987" stroke-width="5" stroke-linecap="round"/></svg><span class="brand-words"><span class="brand-name">simplr</span><span class="brand-suffix">suite</span></span></a>'; }
function auth_start(string $title, string $subtitle): void {
    page_head($title);
    echo '<body class="auth-layout"><aside class="auth-story">' . brand() . '<div><h2>Business workspace</h2><p>Manage projects, people, customers, and finances.</p><div class="story-orbit">' . icon('grid') . icon('users') . icon('chart') . icon('folder') . '</div></div></aside><main class="auth-main"><a class="back-link" href="/">← Back to Simplr</a><div class="auth-card"><h1>' . e($title) . '</h1><p class="subtle">' . e($subtitle) . '</p>';
    show_flash();
}
function auth_end(): void { echo '</div><p class="auth-footer">Simplr Suite</p></main></body></html>'; }
function command_palette_items(string $role, array $nav, array $grants): array {
    $items = [];
    foreach ($nav as [$id,$label,$glyph,$url]) $items[] = ['group'=>'Go to','label'=>$label,'url'=>$url,'icon'=>$glyph];
    if (!in_array($role, ['admin','employee','manager'], true)) return $items;
    require_once __DIR__ . '/modules.php';
    $urls = array_column($items, 'url');
    $tools = $role === 'admin' ? modules() : array_intersect_key(assignable_modules(), $grants);
    foreach ($tools as $key=>$module) {
        $url = '/users/admin/workspace.php?module=' . $key;
        if (!in_array($url, $urls, true)) $items[] = ['group'=>'Go to','label'=>$module['title'],'url'=>$url,'icon'=>$module['icon']];
        if ($role === 'admin' || $grants[$key] === 'edit') $items[] = ['group'=>'Create','key'=>$key,'label'=>'New ' . $module['singular'],'url'=>$url . '&action=new','icon'=>$module['icon']];
    }
    return $items;
}
function time_ago(string $utc): string {
    $seconds = time() - strtotime($utc . ' UTC');
    if ($seconds < 60) return 'just now';
    foreach ([[86400 * 365,'year'],[86400 * 30,'month'],[86400 * 7,'week'],[86400,'day'],[3600,'hour'],[60,'minute']] as [$size,$unit]) {
        if ($seconds >= $size) { $count = intdiv($seconds, $size); return $count . ' ' . $unit . ($count === 1 ? '' : 's') . ' ago'; }
    }
    return 'just now';
}
function suite_start(string $title, string $active, array $user): void {
    page_head($title);
    $role = $_SESSION['auth_role'] ?? '';
    // [id, label, icon, url, sidebar group]
    $nav = [
        ['overview','Overview','grid','/users/admin/newdashboard.php',''],
        ['projects','Projects','folder','/users/admin/workspace.php?module=projects','Work'],
        ['tasks','Tasks','task','/users/admin/workspace.php?module=tasks','Work'],
        ['calendar','Calendar','calendar','/users/admin/calendar.php','Work'],
        ['files','Files','file','/users/admin/files.php','Work'],
        ['people','People & teams','users','/users/admin/workspace.php?module=departments','People'],
        ['recruiting','Recruiting','briefcase','/users/admin/talent-directory.php','People'],
        ['sales','Sales & CRM','target','/users/admin/workspace.php?module=leads','Business'],
        ['finance','Finance','wallet','/users/admin/workspace.php?module=expenses','Business'],
        ['accounting','Accounting','book','/users/admin/accounting.php','Business'],
        ['reports','Reports','chart','/users/admin/reports.php','Insights'],
        ['activity','Activity','pulse','/users/admin/activity.php','Insights'],
        ['modules','All tools','box','/users/admin/modules.php','Workspace'],
        ['access','Staff access','shield','/users/admin/access.php','Workspace'],
        ['settings','Settings','settings','/users/admin/settings.php','Workspace'],
        ['billing','Billing','card','/users/admin/billing.php','Workspace'],
        ['integrations','Integrations','plug','/users/admin/integrations.php','Workspace'],
        ['help','Workspace help','help','/users/help.php','Workspace'],
    ];
    $grants = [];
    if (in_array($role, ['employee','manager'], true)) {
        $nav = [['overview','My workspace','grid','/users/employees/workspace.php',''], ['tasks','My tasks','task','/users/employees/workspace.php#tasks',''], ['modules','My tools','box','/users/admin/modules.php',''], ['files','Files','file','/users/admin/files.php',''], ['reports','Reports','chart','/users/admin/reports.php',''], ['help','Workspace help','help','/users/help.php',''], ['profile','My profile','user','/users/employees/workspace.php#profile','']];
        foreach (db_rows(database(),'SELECT module_key, access_level FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ? AND access_level IN (\'view\',\'edit\')',[(int)$user['company_id'],$role,(int)$user['id']]) as $grant) $grants[$grant['module_key']] = $grant['access_level'];
        if (isset($grants['jobs'])) $nav[] = ['recruiting','Recruiting','briefcase','/users/admin/talent-directory.php',''];
    }
    if ($role === 'talent') {
        $nav = [['profile','My profile','user','/users/talents/talentpool.php',''], ['jobs','Browse roles','briefcase','/jobs/',''], ['applications','My applications','task','/users/talents/applications.php',''], ['opportunities','Invitations & offers','mail','/users/talents/opportunities.php',''], ['security','Account security','shield','/users/talents/resetpassword.php','']];
    }
    $workspaceKind = $role === 'talent' ? 'Talent workspace' : 'Business workspace';
    $commands = command_palette_items($role, $nav, $grants);
    $tourGroups = match ($role) {
        'employee', 'manager' => [
            ['Your work','Use My workspace for your assigned projects and profile, or jump straight to My tasks.',['overview','tasks']],
            ['Tools and updates','Open My tools for the areas your administrator has shared with you. Reports gives you a broader view of workspace activity.',['modules','reports']],
            ['Profile and help','Check your profile details, or send a private request to your workspace administrator.',['profile','help']],
        ],
        'talent' => [
            ['Your profile','Keep your professional profile current and choose whether employers can find you.',['profile']],
            ['Find opportunities','Browse published roles and open a listing to learn about the position and apply.',['jobs']],
            ['Applications and opportunities','Track applications, respond to recruiter messages, or update account security.',['applications','opportunities','security']],
        ],
        default => [
            ['Overview','Start here for a snapshot of projects, open tasks, key counts, and your setup checklist.',['overview']],
            ['Projects, tasks, and people','Use these pages to organize work, track tasks, keep files, and manage departments and team records.',['projects','tasks','files','people']],
            ['Business tools','Open the relevant area for customer relationships, finances, recruiting, or browse all available tools.',['sales','finance','recruiting','modules']],
            ['Accounting','Post balanced journal entries and review financial statements and ratios.',['accounting']],
            ['Planning and insight','Use Calendar to review events, Reports to summarize activity, and Activity to review workspace changes.',['calendar','reports','activity']],
            ['Workspace controls','Manage staff access, settings, billing, and integrations, or ask for help.',['access','settings','billing','integrations','help']],
        ],
    };
    $navById = [];
    foreach ($nav as $item) $navById[$item[0]] = $item;
    $tourSteps = [['title'=>'Getting around','description'=>'Use the navigation on the left to move between areas. On a phone, open it with the menu button. Choose a link below to go directly to that area.','links'=>[]]];
    foreach ($tourGroups as [$groupTitle,$description,$ids]) {
        $links = [];
        foreach ($ids as $id) {
            if (isset($navById[$id])) $links[] = ['label'=>$navById[$id][1],'url'=>$navById[$id][3]];
        }
        if ($links) $tourSteps[] = ['title'=>$groupTitle,'description'=>$description,'links'=>$links];
    }
    $personName = match ($role) {
        'employee' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
        'manager' => trim((string)($user['manager_name'] ?? '')),
        default => (string)$user['company_name'],
    } ?: (string)$user['company_name'];
    $initial = e(mb_strtoupper(mb_substr($personName, 0, 1)));
    echo '<body class="suite"><a class="skip-link" href="#main">Skip to content</a><aside class="suite-sidebar" id="sidebar">' . brand() . '<button type="button" class="icon-button sidebar-close" data-menu-close aria-label="Close navigation">×</button><div class="workspace-label"><span class="workspace-avatar">' . e(mb_strtoupper(mb_substr($user['company_name'], 0, 1))) . '</span><div><strong>' . e($user['company_name']) . '</strong><small>' . e($workspaceKind) . '</small></div></div><nav class="sidebar-nav" aria-label="Main navigation">';
    $group = '';
    foreach ($nav as [$id,$label,$glyph,$url,$section]) {
        if ($section !== '' && $section !== $group) echo '<span class="nav-group-label">' . e($section) . '</span>';
        $group = $section;
        echo '<a href="' . e($url) . '"' . ($id === $active ? ' class="active" aria-current="page"' : '') . '>' . icon($glyph) . e($label) . '</a>';
    }
    echo '</nav><div class="sidebar-bottom"><form method="post" action="/users/admin/logout.php">' . csrf_field() . '<button class="logout-button" type="submit">' . icon('logout') . 'Sign out</button></form></div></aside><button type="button" class="menu-backdrop" data-menu-close aria-label="Close navigation" hidden></button><div class="suite-body"><header class="suite-topbar"><button class="icon-button mobile-menu" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false" data-menu>' . icon('menu') . '</button><span class="breadcrumb">Workspace <span>/</span> <strong>' . e($title) . '</strong></span>';
    echo '<button type="button" class="command-trigger" data-command-open aria-keyshortcuts="Control+K Meta+K">' . icon('search') . '<span>Search or jump to…</span><kbd>Ctrl K</kbd></button><div class="topbar-end">';
    $creates = array_values(array_filter($commands, fn($item) => $item['group'] === 'Create' && in_array($item['key'] ?? '', ['tasks','projects','events','leads','customers','deals','expenses','invoices','jobs'], true)));
    if ($creates) {
        echo '<details class="topbar-menu quick-create"><summary class="button small">' . icon('plus') . '<span>New</span></summary><div class="menu-popover"><span class="menu-caption">Create</span>';
        foreach ($creates as $item) echo '<a href="' . e($item['url']) . '">' . icon($item['icon']) . e(ucfirst(substr($item['label'], 4))) . '</a>';
        echo '</div></details>';
    }
    echo '<button type="button" class="icon-button tour-trigger" data-tour-open aria-label="Dashboard tour" title="Take the dashboard tour">' . icon('help') . '</button><details class="topbar-menu user-menu"><summary aria-label="Account menu"><span class="avatar">' . $initial . '</span><span class="user-menu-name">' . e($personName) . '</span>' . icon('chevron') . '</summary><div class="menu-popover"><div class="menu-identity"><span class="avatar">' . $initial . '</span><div><strong>' . e($personName) . '</strong><small>' . e($user['email']) . '</small></div></div><span class="menu-workspace"><span class="status-dot"></span>' . e($workspaceKind) . '</span>';
    if ($role === 'admin') echo '<a href="/users/admin/settings.php">' . icon('settings') . 'Workspace settings</a><a href="/users/admin/billing.php">' . icon('card') . 'Billing</a>';
    if (in_array($role, ['employee','manager'], true)) echo '<a href="/users/employees/workspace.php#profile">' . icon('user') . 'My profile</a>';
    if ($role !== 'talent') echo '<a href="/users/help.php">' . icon('help') . 'Get help</a>';
    echo '<form method="post" action="/users/admin/logout.php">' . csrf_field() . '<button class="menu-signout" type="submit">' . icon('logout') . 'Sign out</button></form></div></details></div></header><main id="main" class="suite-main">';
    show_flash();
    echo '<dialog class="command-dialog" data-command aria-label="Search and jump to"' . (in_array($role, ['admin','employee','manager'], true) ? ' data-record-search="/api/search.php"' : '') . '><div class="command-search">' . icon('search') . '<input type="text" data-command-input placeholder="' . (in_array($role, ['admin','employee','manager'], true) ? 'Search records, pages and actions…' : 'Jump to a page…') . '" aria-label="Search" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-controls="command-results" aria-autocomplete="list"><kbd>Esc</kbd></div><div class="command-results" id="command-results" role="listbox" aria-label="Results" data-command-results></div><div class="command-foot"><span><kbd>↑</kbd><kbd>↓</kbd> move</span><span><kbd>Enter</kbd> open</span><span><kbd>Esc</kbd> close</span></div><script type="application/json" data-command-items>' . json_encode($commands, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . '</script><div hidden data-command-icons>';
    foreach (array_unique([...array_column($commands, 'icon'), 'arrow', 'search']) as $glyph) echo '<span data-icon="' . e($glyph) . '">' . icon($glyph) . '</span>';
    echo '</div></dialog>';
    echo '<dialog class="tour-dialog" aria-labelledby="tour-title" data-dashboard-tour><div class="tour-dialog-head"><button type="button" class="icon-button" data-tour-close aria-label="Close dashboard tour">×</button></div><h2 id="tour-title">Navigate your workspace</h2><p class="tour-progress" data-tour-progress aria-live="polite"></p>';
    foreach ($tourSteps as $index=>$step) {
        echo '<section class="tour-step"' . ($index === 0 ? '' : ' hidden') . ' data-tour-step><h3>' . e($step['title']) . '</h3><p>' . e($step['description']) . '</p>';
        if ($step['links']) {
            echo '<nav class="tour-links" aria-label="' . e($step['title']) . '">';
            foreach ($step['links'] as $link) echo '<a class="button secondary small" href="' . e($link['url']) . '">' . e($link['label']) . '</a>';
            echo '</nav>';
        }
        echo '</section>';
    }
    echo '<div class="tour-actions"><button type="button" class="button secondary small" data-tour-previous>Previous</button><button type="button" class="button small" data-tour-next>Next</button></div></dialog>';
}
function suite_end(): void { echo '<footer class="suite-footer"><span>Simplr Suite</span></footer></main></div></body></html>'; }
