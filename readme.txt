=== AI Post Visualizer ===
Contributors: CodeAdapted
Tags: AI, DALL·E, Featured Image, Post Management, Image Generator
Requires at least: 5.0 or higher
Tested up to: 6.9
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AI Post Visualizer allows you to generate and manage AI-powered featured images for your WordPress posts using OpenAI image generation (DALL·E).

== Description ==

AI Post Visualizer is a powerful WordPress plugin designed to generate and manage AI-powered images for your posts. The plugin integrates with the DALL·E API to generate custom images based on keywords you provide. Easily manage your post's featured images, filter your posts, and set viewer modes (light/dark) for the admin interface.

= Features =

- **Generate Images with DALL·E**: Generate AI-powered images with DALL·E for your WordPress posts.
- **Featured Image Management**: Set generated images as featured images for any WordPress post with one click.
- **History Management**: Keep track of generated images for each post and restore the original featured image if needed.
- **Viewer Mode**: Toggle between light and dark modes for the admin panel interface.
- **Customizable Post Filtering**: Easily filter posts by post type, alphabetical order, and date.
- **Data Retention Settings**: Control whether to retain or remove plugin data when uninstalling.
- **Role-Based Access Controls**: Plugin UI requires post editing permissions; settings changes are restricted to administrators.

== Installation ==

1. Download and unzip the plugin folder.
2. Upload the `ai-post-visualizer` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Go to the "AI Post Visualizer" settings page to configure the plugin and set your OpenAI API key.

== Usage ==

Note: Access to the plugin UI requires the `edit_posts` capability (typically Editor and above). Actions that modify a specific post require permission to edit that post, and image generation/upload requires media upload permissions.

= Configure Settings =
1. Navigate to the **AI Post Visualizer** settings page in the WordPress admin menu.
2. Configure your OpenAI API key using one of the following methods:
   - **Recommended (server-managed)**: set an environment variable named `AIPV_OPENAI_API_KEY`, or define `AIPV_OPENAI_API_KEY` as a constant in `wp-config.php`. When a server-managed key is detected, the Settings field is disabled.
   - **Via the Settings screen**: enter your key in the API key field. For security, the value is never displayed after saving (you can paste a new value to update it).
   - If you don’t have an API key yet, sign up for one at [OpenAI](https://platform.openai.com/) and retrieve your API key from [the API keys page](https://platform.openai.com/api-keys).
3. Optionally, configure additional settings such as **Data Retention** (see "Manage Data Retention" below).
4. Save your changes.

= Generate AI Images =
1. Go to the **Generate** tab within the AI Post Visualizer interface.
2. In the **Keyword Input** field, type in a keyword or phrase that best describes the image you want to generate.
3. Set the number of images to generate (default: 1) and choose the desired resolution (default: 256×256).
    - Available resolutions include:
        - 256×256
        - 512×512
        - 1024×1024
   - The plugin displays an estimated cost breakdown based on current OpenAI pricing; always verify actual pricing in your OpenAI account.
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

This plugin uses OpenAI's image generation API to generate AI-powered images for your posts. When you use this plugin, your keywords/prompts and image generation requests are sent to OpenAI to generate the images.
- [OpenAI Website](https://openai.com)
- [OpenAI Terms of Use](https://openai.com/policies/terms-of-use)
- [OpenAI Privacy Policy](https://openai.com/policies/privacy-policy)

== Screenshots ==

1. **Settings**: Configure your API key and data retention settings.
2. **Posts**: View and manage the featured images for each post.
3. **Posts Filtering**: Filter and search through your posts to quickly find the posts you need to update.
4. **Generate AI Images**: Effortlessly create custom images using DALL·E by entering keyword prompts, and seamlessly apply them to your posts.
5. **Generation History**: Quickly access and reuse previously generated images for any post, and easily revert to the original image if needed.
6. **Viewer Mode**: Easily switch between light and dark modes for a customized viewing experience.

== Frequently Asked Questions ==

= How do I get a DALL·E API key? =
You can sign up and retrieve your DALL·E API key at [OpenAI](https://platform.openai.com/).

= Can I configure the API key via environment variable or wp-config.php? =
Yes. You can set `AIPV_OPENAI_API_KEY` as an environment variable or define it as a constant in `wp-config.php`. When a server-managed key is detected, the Settings input is disabled and the key cannot be edited from the WordPress admin.

= Is my API key stored securely? =
If you enter the key in the plugin Settings UI, it is stored encrypted in the WordPress database and is never displayed back in the UI after saving. For the best security posture, use a server-managed key via environment variable/constant.

= Why does the Generate screen show a cost estimate? =
The Generate screen shows an estimated cost per image and a total estimate based on OpenAI pricing. These are estimates only and pricing can change.

= Can I revert to the original featured image? =
Yes, you can revert back to the original featured image at any time using the "Revert to Original" button. **NOTE** The original featured image will still need to exist in the WordPress Media Library for you to be able to revert back to it.

= What happens to my images when I uninstall the plugin? =
If the **Data Retention** toggle is enabled, all plugin-related data (including generated images) will be removed when the plugin is uninstalled.

== Changelog ==

= 1.2.0 =
* Security: Added support for server-managed API keys via `AIPV_OPENAI_API_KEY` (env/constant) and encrypted API key storage when saved via the Settings UI.
* Security: Added capability checks to restrict plugin access to users who can edit posts, and restrict post-specific actions to users who can edit that post (and upload media).
* UI improvements: Added estimated cost breakdown and pricing link on the Generate screen.

= 1.1.0 =
* Accessibility improvements: Added ARIA labels, roles, and keyboard navigation support to all main admin view files.
* Improved translation coverage: Wrapped all user-facing strings in translation functions and fixed untranslated strings.
* Compliance review: Ensured plugin meets WordPress.org requirements for security, translation, and accessibility.
* General code review: Applied best practices and minor code quality improvements.

= 1.0.2 =
* Remove unwanted logging from production files.

= 1.0.1 =
* Add Spanish translations.

= 1.0.0 =
* Initial release with AI image generation, post filtering, and data retention settings.

== License ==

This plugin is licensed under the GPLv2 or later. You can view the full license here: [GPLv2 License](http://www.gnu.org/licenses/gpl-2.0.html).

== Credits ==

* **DALL·E**: [OpenAI DALL·E](https://openai.com/dall-e)
