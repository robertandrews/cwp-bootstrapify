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

function wrap_element($dom, $wrapped_element, $new_element, $class = null)
{
    /**
     * Wrap element in another element.
     * eg. wrap <blockquote> in <figure>
     * Utility function.
     *
     * @param DOMDocument   $dom                Whole DOM object containing the elements to be wrapped
     * @param DOMDocument   $wrapped_element    Element to be wrapped
     * @param string        $new_element_name   Name of the new element to be created
     * @param string        $class              Class to be added to the new element
     *
     * @author Robert Andrews, inspired by @XzKto, https://stackoverflow.com/a/8428323/1375163
     */
    // Initialise the new wrapper
    $wrapper = $dom->createElement($new_element);
    // Clone our created element
    $wrapper_clone = $wrapper->cloneNode();
    // Replace image with this wrapper div
    $wrapped_element->parentNode->replaceChild($wrapper_clone, $wrapped_element);
    // Append the element to wrapper div
    $wrapper_clone->appendChild($wrapped_element);
    // Add passed class
    if (!empty($class)) {
        $wrapper_clone->setAttribute('class', $class);
    }
}

add_filter('the_content', 'bootstrap_blockquote', 30);
function bootstrap_blockquote($content)
{
    /**
     * Bootstrapify blockquote elements
     * - Apply .blockquote class to <blockquote> elements
     * - Apply style classes to <blockquote>
     * - Wrap <blockquote> in <figure> - https://getbootstrap.com/docs/5.0/content/typography/#blockquotes
     * - Ignore tweet embeds (iframes fall back to blockquote with class .twitter-tweet)
     *
     * @param DOMDocument   $content            WordPress post content from the_content()
     *
     * @author Robert Andrews
     */

    // Load DOM of post content

    // $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
    // $dom = new DOMDocument('1.0', 'utf-8');
    // libxml_use_internal_errors(true);
    // $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // $content = utf8_decode($content); // https://stackoverflow.com/questions/1269485/how-do-i-tell-domdocument-load-what-encoding-i-want-it-to-use
    $dom = new DOMDocument('1.0', 'iso-8859-1');
    libxml_use_internal_errors(true);
    // $dom->loadHTML($content);
    $dom->loadhtml(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    // For every <blockquote> found
    foreach ($dom->getElementsByTagName('blockquote') as $blockquote) {
        // Except tweet embeds
        $blockquote_class = $blockquote->getAttribute('class');
        if ($blockquote_class != 'twitter-tweet') {
            // Add .blockquote class - class addition contributed by @Gillu13, https://stackoverflow.com/a/63088684/1375163
            $class_to_add = 'blockquote border-start p-4 bg-light';
            $blockquote->setAttribute('class', $class_to_add);
            // Wrap blockquote in <figure>
            wrap_element($dom, $blockquote, 'figure');
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
        $updated_attributes = 'class="flex-fill border-bottom pb-2 mt-5 mb-3 ' . $attributes . '"';

        // Return the modified heading tag
        return '<' . $tag . ' ' . $updated_attributes . '>' . $content . '</' . $tag . '>';
    };

    // Apply the replacement callback to the post content
    $updated_content = preg_replace_callback($pattern, $replacement, $content);

    return $updated_content;
}
add_filter('the_content', 'add_custom_classes_to_headings');

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
add_filter('the_content', 'add_classes_to_images');

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
