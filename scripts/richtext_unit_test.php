<?php
// Database-independent checks for the rich text sanitizer and renderers.
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/richtext.php';
$checks = 0; $failures = [];
function check_rich(bool $ok, string $name): void { global $checks, $failures; $checks++; if (!$ok) $failures[] = $name; }
$clean = rich_text_clean('<p onclick="x()">Hi <b>there</b> <script>alert(1)</script><img src=x onerror=alert(2)><a href="javascript:alert(3)">bad</a> <a href="https://example.test/a?b=1&c=2" style="color:red">good</a></p>');
check_rich(!str_contains($clean,'script') && !str_contains($clean,'onerror') && !str_contains($clean,'onclick') && !str_contains($clean,'<img'), 'Scripts, event handlers and images are removed');
check_rich(!str_contains($clean,'javascript:') && str_contains($clean,'bad'), 'Unsafe links keep their text but lose the link');
check_rich(str_contains($clean,'<a href="https://example.test/a?b=1&amp;c=2" rel="noopener noreferrer nofollow" target="_blank">good</a>'), 'HTTPS links are kept with safe rel and target');
check_rich(!str_contains($clean,'style='), 'Inline styles are removed');
check_rich(str_contains($clean,'<strong>there</strong>'), 'Bold is normalised to strong');
check_rich(rich_text_clean('<ul><li>One</li><li>Two<ol><li>Nested</li></ol></li></ul>') === '<ul><li>One</li><li>Two<ol><li>Nested</li></ol></li></ul>', 'Lists survive unchanged');
check_rich(rich_text_clean('<h1>Title</h1><h2>Sub</h2>') === 'Title<h3>Sub</h3>', 'Headings are limited to one level');
check_rich(rich_text_clean("Line one\nLine two\n\nNext <b") === '<p>Line one<br>Line two</p><p>Next &lt;b</p>', 'Plain text becomes escaped paragraphs');
check_rich(rich_text_html('Product designer <script>alert(1)</script>') === '<p>Product designer &lt;script&gt;alert(1)&lt;/script&gt;</p>', 'Legacy plain text with markup is escaped, not executed');
check_rich(rich_text_clean('<p><br></p><p>&nbsp;</p>') === '', 'Empty editor content becomes an empty value');
check_rich(rich_text_clean('<p>Emoji 🎉 and naïve café</p>') === '<p>Emoji 🎉 and naïve café</p>', 'UTF-8 text is preserved');
check_rich(rich_text_plain('<p>Hello <strong>team</strong></p><ul><li>One</li><li>Two &amp; three</li></ul>') === "Hello team\n• One\n• Two & three", 'Plain text export keeps structure and decodes entities');
check_rich(!str_contains(rich_text_clean('<svg><a href="https://x.test">x</a></svg><math><mi>y</mi></math>'),'href'), 'Foreign SVG and MathML content is dropped');
check_rich(rich_text_clean('<a href="mailto:hello@example.test">Mail</a>') === '<a href="mailto:hello@example.test" rel="noopener noreferrer nofollow" target="_blank">Mail</a>', 'Mail links are kept');
check_rich(!str_contains(rich_text_clean('<p><!-- secret --><div><form><input value="x"></form>ok</div></p>'),'secret'), 'Comments and form controls are removed');
if ($failures) { fwrite(STDERR, 'Rich text checks failed: ' . implode('; ', $failures) . PHP_EOL); exit(1); }
echo "Rich text checks passed: $checks" . PHP_EOL;
