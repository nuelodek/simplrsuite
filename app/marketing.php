<?php
declare(strict_types=1);
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/audiences.php';
function marketing_head(string $audience, string $title, string $description): void {
    $origin = configured_audience_url($audience) ?: 'https://' . audiences()[$audience]['host'];
    page_head($title, ['description'=>$description,'canonical'=>$origin . '/', 'robots'=>config('APP_ENV') === 'local' ? 'noindex, nofollow' : 'index, follow']);
}
function audience_switcher(string $active): void {
    echo '<div class="audience-bar"><nav class="public-container audience-nav" aria-label="Choose your Simplr experience"><span>One Simplr. Your kind of work.</span><div>';
    foreach (audiences() as $key=>$entry) echo '<a href="' . e(audience_url($key)) . '"' . ($key === $active ? ' aria-current="page"' : '') . '>' . e($entry['name']) . '</a>';
    echo '</div></nav></div>';
}
function marketing_header(string $audience, string $signIn, string $actionLabel, string $action): void {
    audience_switcher($audience);
    echo '<header class="public-header public-container">' . brand() . '<nav class="public-nav" aria-label="Main navigation"><a href="#features">Why Simplr</a><a href="#how-it-works">How it works</a></nav><div class="public-actions"><a href="' . e($signIn) . '">Sign in</a><a class="button" href="' . e($action) . '">' . e($actionLabel) . icon('arrow') . '</a></div></header>';
}
function marketing_footer(string $audience): void {
    echo '<footer class="public-footer public-container">' . brand() . '<span>© ' . date('Y') . ' Simplr Suite. Built for better work.</span><div class="footer-links"><a href="/public/privacypolicy.php">Privacy</a><a href="' . e(audience_url('business')) . '">Business</a><a href="' . e(audience_url('talents')) . '">Talent</a><a href="' . e(audience_url('work')) . '">Work</a></div></footer></body></html>';
}
function render_landing(string $audience): void {
    if (!isset(audiences()[$audience])) fail('This page could not be found.',404);
    require __DIR__ . '/landing/' . $audience . '.php';
}
