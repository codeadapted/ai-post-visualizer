=== AI Post Visualizer ===
Contributors: CodeAdapted
Tags: AI, DALL·E, Featured Image, Post Management, Image Generator
Requires at least: 5.0 or higher
Tested up to: 6.6.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AI Post Visualizer allows you to generate and manage AI-powered featured images for your WordPress posts using the DALL·E API.

== Description ==

AI Post Visualizer is a powerful WordPress plugin designed to generate and manage AI-powered images for your posts. The plugin integrates with the DALL·E API to generate custom images based on keywords you provide. Easily manage your post's featured images, filter your posts, and set viewer modes (light/dark) for the admin interface.

= Features =

- **Generate Images with DALL·E**: Generate AI-powered images with DALL·E for your WordPress posts.
- **Featured Image Management**: Set generated images as featured images for any WordPress post with one click.
- **History Management**: Keep track of generated images for each post and restore the original featured image if needed.
- **Viewer Mode**: Toggle between light and dark modes for the admin panel interface.
- **Customizable Post Filtering**: Easily filter posts by post type, alphabetical order, and date.
- **Data Retention Settings**: Control whether to retain or remove plugin data when uninstalling.

== Installation ==

1. Download and unzip the plugin folder.
2. Upload the `ai-post-visualizer` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Go to the "AI Post Visualizer" settings page to configure the plugin and input your DALL·E API key.

== Usage ==

= Configure Settings =
1. Navigate to the **AI Post Visualizer** settings page in the WordPress admin menu.
2. Enter your DALL·E API key into the designated field.
   - If you don’t have an API key yet, sign up for one at [OpenAI](https://platform.openai.com/) and retrieve your API key from [the API keys page](https://platform.openai.com/api-keys).
3. Optionally, configure additional settings such as **Data Retention** (see Step 5 for details).
4. Save your changes.

= Generate AI Images =
1. Go to the **Generate** tab within the AI Post Visualizer interface.
2. In the **Keyword Input** field, type in a keyword or phrase that best describes the image you want to generate.
3. Set the number of images to generate and choose the desired resolution (default: 256x256).
    - Available resolutions include:
        - 256×256
        - 512×512
        - 1024×1024
4. Click the **Render Images** button to initiate the image generation process.
   - A loading indicator will appear while your images are being rendered.
5. Once complete, your generated images will appear in the **Rendered Images** section below and also appear in the **Generation History** sidebar.

= Manage Featured Images =
1. Navigate to the **Posts** tab.
2. Browse through your posts and locate the one you wish to update with a new featured image.
3. Click the **Generate New Image** button for the post you want to update.
4. You can either:
   - Follow the **Generate AI Images** step to generate new images, or
   - Load a previously generated set of images from the **Generation History** sidebar.
5. Once the images have rendered, find the image you want to use under **Rendered Images**.
6. Click the **Set Featured Image** button underneath the image you want to apply to your post.
7. If at any time you want to undo the change, you can revert to the original featured image by clicking the **Revert to Original** button.

= Filter Posts =
1. Use the available filters to search or sort your posts:
   - **Post Types**: Filter by specific post types (e.g., posts, pages).
   - **Alphabetical Order**: Sort posts by title in ascending or descending order.
   - **Date**: Sort posts by creation date (newest or oldest first).
2. You can also use the **Search** field to look for posts by title, helping you quickly find the one you need.
3. If you would like to clear all filters you can click the **Reset Filters** button to reset your posts view.

= Manage Data Retention =
1. Go to the **Settings** tab to manage data retention settings.
2. Toggle the **Data Retention** option to enable or disable automatic data removal upon plugin uninstallation:
   - **Enabled**: All plugin-related data (including generated images and settings) will be removed when the plugin is uninstalled.
   - **Disabled**: Data will be retained after plugin uninstallation for future use.

== Third-Party Service Disclosure ==

This plugin uses OpenAI's DALL·E API to generate AI-powered images for your posts. When you use this plugin, your keywords and requests are sent to the DALL·E API to generate the images.
- [OpenAI Website](https://openai.com)
- [OpenAI Terms of Use](https://openai.com/policies/terms-of-use)
- [OpenAI Privacy Policy](https://openai.com/policies/privacy-policy)

== Screenshots ==

1. Settings Page: Configure your API key and data retention settings.
2. Generate AI Images: Generate custom images using DALL·E based on keyword inputs.
3. Post Management: View and manage the featured images for each post.

== Frequently Asked Questions ==

= How do I get a DALL·E API key? =
You can sign up and retrieve your DALL·E API key at [OpenAI](https://platform.openai.com/).

= Can I revert to the original featured image? =
Yes, you can revert back to the original featured image at any time using the "Revert to Original" button. **NOTE** The original featured image will still need to exist in the WordPress Media Library for you to be able to revert back to it.

= What happens to my images when I uninstall the plugin? =
If the **Data Retention** toggle is enabled, all plugin-related data (including generated images) will be removed when the plugin is uninstalled.

== Changelog ==

= 1.0.0 =
* Initial release with AI image generation, post filtering, and data retention settings.

== License ==

This plugin is licensed under the GPLv2 or later. You can view the full license here: [GPLv2 License](http://www.gnu.org/licenses/gpl-2.0.html).

== Credits ==

* **DALL·E**: [OpenAI DALL·E](https://openai.com/dall-e)
