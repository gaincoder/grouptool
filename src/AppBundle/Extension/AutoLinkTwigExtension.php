<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 29.07.2017
 * Time: 16:19
 */

namespace AppBundle\Extension;


class AutoLinkTwigExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array('auto_link_text' => new \Twig_Filter_Method($this, 'auto_link_text', array('is_safe' => array('html'))),
        );
    }

    public function getName()
    {
        return "auto_link_twig_extension";
    }

    static public function auto_link_text($string)
    {
        $string = htmlentities($string);
        $text = preg_replace(
            '#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i',
            "<a href=\"$1\" target=\"_blank\">__LINK__</a>$4",
            $string
        );

        if(strlen($text)) {
            $dom = new \DOMDocument('1.0','UTF-8');
            $text = $html_src = '<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head><body>'.$text;
            $dom->loadHTML($text);
            /** @var \DOMNode $node */
            foreach ($dom->getElementsByTagName('a') as $node) {
                $inner = $node->attributes->getNamedItem('href')->nodeValue;
                if (strlen($inner) > 35) {
                    $inner = substr($inner, 0, 30) . '...' . substr($inner, strlen($inner) - 5, 5);
                }
                $node->nodeValue = $inner;
            }
            $text = $dom->saveHTML();
        }
        return $text;
    }
}