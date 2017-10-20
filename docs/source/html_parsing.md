# Parsing HTML/DOM in PHP

This module provides the [Php Simple Html DOM Parser](http://simplehtmldom.sourceforge.net/manual_api.htm)

* <https://packagist.org/packages/sunra/php-simple-html-dom-parser>

Here is example code that appends a class and replaces a child node's text with other.

    use Sunra\PhpSimple\HtmlDomParser;
    
    ... 
    
    // Replace the description with the error and add error class.
    $dom = HtmlDomParser::str_get_html($vars['element']['#children']);
    
    // Here we will append a class to already existing classes.
    $item = $dom->find('.form-item')[0];
    $class = $item->class . ' form-item--error';
    $item->class = $class;

    // Look for the description and append if not found
    if (!$dom->find('.form-item__description')) {
        $inner = $item->innertext;
        $inner .= '<span class="form-item__description">' . $error . '</span>';
        $item->innertext = $inner;
    }
    
    // Otherwise replaces it's innerHtml with the error
    else {
        $dom->find('.form-item__description')[0]->innertext = $error;
    }
    $output = (string) $dom;
