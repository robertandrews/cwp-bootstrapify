<?php
/*
Plugin Name: Bootstrapify
Plugin URI:
Description: Bootstrapify WordPress content
Version: 1.0
Author: Robert Andrews
Author URI: https://www.robertandrews.co.uk
License: GPL2
 */

function my_custom_content($content)
{
    /**
     * Run all custom content functions
     * Ensures we only filter the_content once
     */
    $content = bootstrap_blockquote($content);
    $content = add_custom_classes_to_headings($content);
    $content = add_classes_to_images($content);
    $content = wrap_iframe_videos_in_ratio($content);
    return $content;
}
add_filter('the_content', 'my_custom_content');

// add_filter('the_content', 'bootstrap_blockquote', 30);
function bootstrap_blockquote($content)
{
    /**
     * Bootstrapify blockquote elements
     * - Apply .blockquote class to <blockquote> elements
     * - Apply style classes to <blockquote>
     * - Wrap <blockquote> in <figure> - https://getbootstrap.com/docs/5.0/content/typography/#blockquotes
     * - Ignore tweet embeds (iframes fall back to blockquote with class .twitter-tweet)
     * - Ignore blockquote followed by a <p> containing an <iframe> with class "wp-embedded-content"
     *
     * @param DOMDocument   $content            WordPress post content from the_content()
     *
     * @author Robert Andrews
     */

    // Load DOM of post content
    $dom = new DOMDocument('1.0', 'iso-8859-1');
    libxml_use_internal_errors(true);
    $dom->loadhtml(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    // For every <blockquote> found
    foreach ($dom->getElementsByTagName('blockquote') as $blockquote) {
        $blockquote_class = $blockquote->getAttribute('class');
        $next_sibling = $blockquote->nextSibling;
        $skip_blockquote = false;

        while ($next_sibling !== null && $next_sibling->nodeType === 3) { // skip text nodes
            $next_sibling = $next_sibling->nextSibling;
        }

        if ($next_sibling !== null && $next_sibling->nodeName === 'p') {
            foreach ($next_sibling->getElementsByTagName('iframe') as $iframe) {
                if ($iframe->getAttribute('class') === 'wp-embedded-content') {
                    $skip_blockquote = true;
                    break;
                }
            }
        }

        if ($blockquote_class != 'twitter-tweet' && !$skip_blockquote) {
            // Add .blockquote class
            $class_to_add = 'blockquote border-start p-4 bg-light';
            $blockquote->setAttribute('class', $class_to_add);

            // Wrap blockquote in <figure>
            $wrapper = $dom->createElement('figure');
            $wrapper_clone = $wrapper->cloneNode();
            $blockquote->parentNode->replaceChild($wrapper_clone, $blockquote);
            $wrapper_clone->appendChild($blockquote);
        }
    }
    $content = $dom->saveHTML();
    return $content;
}

function add_custom_classes_to_headings($content)
{
    /**
     * Add custom classes to headings
     *
     * This function takes a string argument called $content, which is the post content.
     * It searches for the defined heading tags (h2, h3, and h4) in the $content string and
     * modifies them by adding the desired classes. The updated content is returned.
     * @param string $content The post content
     * @return string The modified post content
     */

    // Define the heading tags you want to target
    $heading_tags = array('h2', 'h3', 'h4');

    // Regular expression pattern to match the heading tags
    $pattern = '/<(' . implode('|', $heading_tags) . ')(.*?)>(.*?)<\/\1>/i';

    // Callback function to modify the matched heading tags
    $replacement = function ($matches) {
        $tag = $matches[1];
        $attributes = $matches[2];
        $content = $matches[3];

        // Add the desired classes to the heading tag
        $updated_attributes = 'class="flex-fill border-bottom pb-2 mt-5 mb-3"' . $attributes;

        // Return the modified heading tag
        return '<' . $tag . ' ' . $updated_attributes . '>' . $content . '</' . $tag . '>';
    };

    // Apply the replacement callback to the post content
    $updated_content = preg_replace_callback($pattern, $replacement, $content);

    return $updated_content;
}
// add_filter('the_content', 'add_custom_classes_to_headings');

function add_classes_to_images($content)
{
    /**
     * Add classes to images.
     *
     * Adds classes 'w-100' and 'img-fluid' to all images within a given HTML content.
     * @param string $content The HTML content to add classes to.
     * @return string The modified HTML content with added classes to images.
     */
    $dom = new DOMDocument();

    // error_log("Content before loadHTML: " . $content);

    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $images = $dom->getElementsByTagName('img');

    foreach ($images as $image) {
        if ($image->parentNode->nodeName === 'p' || $image->parentNode->nodeName === 'div') {
            $existing_classes = $image->getAttribute('class');
            $new_classes = 'w-100 img-fluid';

            if ($existing_classes) {
                $new_classes = $existing_classes . ' ' . $new_classes;
            }

            $image->setAttribute('class', $new_classes);
        }
    }

    return $dom->saveHTML();
}
// add_filter('the_content', 'add_classes_to_images');

/**
 * Override default image caption width
 */
add_filter('img_caption_shortcode_width', '__return_false');
/*
function my_custom_caption_width() {
return 800; // Set the maximum allowed width for captions
}
add_filter('img_caption_shortcode_width', 'my_custom_caption_width');
 */

function remove_youtube_dimensions($html)
{
    // Remove width and height attributes from YouTube iframe
    $html = preg_replace('/(width|height)="[\d]+"/i', '', $html);

    return $html;
}
add_filter('embed_oembed_html', 'remove_youtube_dimensions');

function wrap_iframe_videos_in_ratio($content)
{
    /**
     * Wrap video iframe elements with a div element with class "ratio ratio-16x9"
     *
     * @param string $content WordPress post content
     * @return string Modified post content
     */
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

    $iframes = $dom->getElementsByTagName('iframe');

    foreach ($iframes as $iframe) {
        $src = $iframe->getAttribute('src');
        if (strpos($src, 'youtube.com') !== false || strpos($src, 'vimeo.com') !== false) {
            $wrapper = $dom->createElement('div');
            $wrapper->setAttribute('class', 'ratio ratio-16x9');
            $iframe->parentNode->replaceChild($wrapper, $iframe);
            $wrapper->appendChild($iframe);
        }
    }

    $output = $dom->saveHTML();
    return $output;
}

// add_filter('the_content', 'wrap_iframe_videos_in_ratio', 30);
