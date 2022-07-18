<?php

/*declare(strict_types=1);*/

namespace Termwind;

use DOMDocument;
use DOMNode;
use Termwind\Html\CodeRenderer;
use Termwind\Html\PreRenderer;
use Termwind\Html\TableRenderer;
use Termwind\ValueObjects\Node;

/**
 * @internal
 */
final class HtmlRenderer
{
    /**
     * Renders the given html.
     */
    public function render(/*string */$html, /*int */$options)/*: void*/
    {
        $html = cast_to_string($html);

        $options = cast_to_int($options);

        $this->parse($html)->render($options);
    }

    /**
     * Parses the given html.
     */
    public function parse(/*string */$html)/*: Components\Element*/
    {
        $html = cast_to_string($html);

        $dom = new DOMDocument();

        if (strip_tags($html) === $html) {
            return Termwind::span($html);
        }

        $html = '<?xml encoding="UTF-8">'.trim($html);
        $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_COMPACT | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS | LIBXML_NOXMLDECL);

        /** @var DOMNode $body */
        $body = $dom->getElementsByTagName('body')->item(0);
        $el = $this->convert(new Node($body));

        // @codeCoverageIgnoreStart
        return is_string($el)
            ? Termwind::span($el)
            : $el;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Convert a tree of DOM nodes to a tree of termwind elements.
     */
    private function convert(Node $node)/*: Components\Element|string*/
    {
        $children = [];

        if ($node->isName('table')) {
            return (new TableRenderer)->toElement($node);
        } elseif ($node->isName('code')) {
            return (new CodeRenderer)->toElement($node);
        } elseif ($node->isName('pre')) {
            return (new PreRenderer)->toElement($node);
        }

        foreach ($node->getChildNodes() as $child) {
            $children[] = $this->convert($child);
        }

        $children = array_filter($children, function ($child) { return $child !== ''; });

        return $this->toElement($node, $children);
    }

    /**
     * Convert a given DOM node to it's termwind element equivalent.
     *
     * @param  array<int, Components\Element|string>  $children
     */
    private function toElement(Node $node, array $children)/*: Components\Element|string*/
    {
        if ($node->isText() || $node->isComment()) {
            return (string) $node;
        }

        /** @var array<string, mixed> $properties */
        $properties = [
            'isFirstChild' => $node->isFirstChild(),
        ];

        $styles = $node->getClassAttribute();

        switch ($node->getName()) {
            case 'body': return $children[0]; // Pick only the first element from the body node
            case 'div': return Termwind::div($children, $styles, $properties);
            case 'p': return Termwind::paragraph($children, $styles, $properties);
            case 'ul': return Termwind::ul($children, $styles, $properties);
            case 'ol': return Termwind::ol($children, $styles, $properties);
            case 'li': return Termwind::li($children, $styles, $properties);
            case 'dl': return Termwind::dl($children, $styles, $properties);
            case 'dt': return Termwind::dt($children, $styles, $properties);
            case 'dd': return Termwind::dd($children, $styles, $properties);
            case 'span': return Termwind::span($children, $styles, $properties);
            case 'br': return Termwind::breakLine($styles, $properties);
            case 'strong': return Termwind::span($children, $styles, $properties)->strong();
            case 'b': return Termwind::span($children, $styles, $properties)->fontBold();
            case 'em':
            case 'i': return Termwind::span($children, $styles, $properties)->italic();
            case 'u': return Termwind::span($children, $styles, $properties)->underline();
            case 's': return Termwind::span($children, $styles, $properties)->lineThrough();
            case 'a': return Termwind::anchor($children, $styles, $properties)->href($node->getAttribute('href'));
            case 'hr': return Termwind::hr($styles, $properties);
            default: return Termwind::div($children, $styles, $properties);
        }
    }
}
