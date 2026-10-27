<?php
declare(strict_types=1);
// Rich text is stored as a small allowlisted subset of HTML. Output is rebuilt
// from scratch, so unknown tags, attributes, styles, scripts and comments never
// survive. Older plain-text values are escaped and shown with their line breaks.
function rich_text_tags(): array {
    return ['p'=>'p','div'=>'p','br'=>'br','strong'=>'strong','b'=>'strong','em'=>'em','i'=>'em','u'=>'u','s'=>'s','strike'=>'s','del'=>'s','h2'=>'h3','h3'=>'h3','h4'=>'h3','ul'=>'ul','ol'=>'ol','li'=>'li','blockquote'=>'blockquote','code'=>'code','pre'=>'pre','a'=>'a'];
}
function rich_text_is_html(?string $value): bool {
    return $value !== null && preg_match('~<(?:p|br|strong|b|em|i|u|s|ul|ol|li|h[2-4]|blockquote|code|pre|a|div)(?:\s[^<>]*)?/?>~i',$value) === 1;
}
function rich_text_clean(string $html): string {
    if (trim($html) === '') return '';
    if (!rich_text_is_html($html)) return rich_text_from_plain($html);
    $source = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $source->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_COMPACT);
    libxml_clear_errors(); libxml_use_internal_errors($previous);
    $target = new DOMDocument('1.0','UTF-8');
    $root = $target->createElement('root');
    $body = $source->getElementsByTagName('body')->item(0);
    if ($body) rich_text_copy($body,$root,$target,0);
    $out = '';
    foreach ($root->childNodes as $child) $out .= $target->saveHTML($child);
    $out = preg_replace('~<p>(?:\s|&nbsp;|\x{00A0}|<br>)*</p>~u','',$out) ?? '';
    return trim($out);
}
function rich_text_copy(DOMNode $from, DOMElement $to, DOMDocument $doc, int $depth): void {
    if ($depth > 12) return;
    $tags = rich_text_tags();
    foreach ($from->childNodes as $node) {
        if ($node instanceof DOMText) { $to->appendChild($doc->createTextNode($node->nodeValue ?? '')); continue; }
        if (!$node instanceof DOMElement) continue;
        $name = strtolower($node->nodeName);
        if (in_array($name,['script','style','iframe','object','embed','template','svg','math','head','title','meta','link','noscript','textarea','select','button','form','input'],true)) continue;
        if (!isset($tags[$name])) { rich_text_copy($node,$to,$doc,$depth + 1); continue; }
        $element = $doc->createElement($tags[$name]);
        if ($name === 'a') {
            $href = trim((string)$node->getAttribute('href'));
            $scheme = strtolower((string)parse_url($href,PHP_URL_SCHEME));
            if ($href === '' || !in_array($scheme,['http','https','mailto'],true) || mb_strlen($href) > 2000) { rich_text_copy($node,$to,$doc,$depth + 1); continue; }
            $element->setAttribute('href',$href);
            $element->setAttribute('rel','noopener noreferrer nofollow');
            $element->setAttribute('target','_blank');
        }
        if ($tags[$name] !== 'br') rich_text_copy($node,$element,$doc,$depth + 1);
        $to->appendChild($element);
    }
}
function rich_text_from_plain(string $text): string {
    $paragraphs = preg_split('/\R{2,}/u',trim($text)) ?: [];
    return implode('',array_map(fn($paragraph)=>'<p>' . preg_replace('/\R/u','<br>',e(trim($paragraph))) . '</p>',array_filter($paragraphs,fn($paragraph)=>trim($paragraph) !== '')));
}
// Safe HTML for display, whichever format the value was saved in.
function rich_text_html(?string $value): string {
    $value = (string)$value;
    return trim($value) === '' ? '' : rich_text_clean($value);
}
function rich_text_plain(?string $value): string {
    $value = (string)$value;
    if (!rich_text_is_html($value)) return trim($value);
    $value = preg_replace(['~<br\s*/?>~i','~</(p|div|li|h[1-6]|blockquote|pre)>~i','~<li[^>]*>~i'],["\n","\n","• "],$value) ?? $value;
    return trim(preg_replace("/\n{3,}/","\n\n",html_entity_decode(strip_tags($value),ENT_QUOTES | ENT_HTML5,'UTF-8')) ?? '');
}
