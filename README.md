# CWP Bootstrapify

This repository contains a WordPress plugin called CWP Bootstrapify. It allows you to bootstrapify the content of your WordPress website by adding custom classes and modifying certain elements.

## Features

- Bootstrapify blockquote elements
- Add custom classes to headings
- Add classes to images

## Installation

1. Download the repository as a ZIP file.
2. In your WordPress admin panel, navigate to **Plugins** → **Add New**.
3. Click on the **Upload Plugin** button, and select the ZIP file you downloaded.
4. Activate the plugin.

## Usage

The CWP Bootstrapify plugin provides the following features:

### Bootstrapify blockquote elements

- Applies the `.blockquote` class to `<blockquote>` elements
- Applies style classes to `<blockquote>`
- Wraps `<blockquote>` in `<figure>` element
- Ignores tweet embeds (iframes fall back to blockquote with class `.twitter-tweet`)
- Ignores blockquote followed by a `<p>` containing an `<iframe>` with class "wp-embedded-content"

### Add custom classes to headings

- Adds custom classes to specified heading tags (h2, h3, and h4)
- Modifies the heading tags by adding the desired classes

### Add classes to images

- Adds classes 'w-100' and 'img-fluid' to all images within the HTML content

To utilize these features, activate the plugin, and the modifications will be applied to the corresponding elements in your WordPress content.

## License

This plugin is licensed under the GPL2 License. You can find the full license text in the [LICENSE](LICENSE) file.

## Author

CWP Bootstrapify is developed by Robert Andrews. You can find more information about the author on the [author's website](https://www.robertandrews.co.uk).
