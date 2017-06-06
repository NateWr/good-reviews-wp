=== Good Reviews for WordPress ===
Contributors: NateWr
Author URI: https://github.com/NateWr
Plugin URL: http://themeofthecrop.com
Requires at Least: 3.9
Tested Up To: 4.8
Tags: reviews, testimonials, rating, star rating, schema, rich snippets, customer reviews, review widget, testimonial widget
Stable tag: 1.2.1
License: GPLv2 or later
Donate link: http://themeofthecrop.com

Add reviews and testimonials to your website and easily display them in posts, pages or widgets using search-engine-friendly Schema markup.

== Description ==

Add reviews and testimonials to your website and easily display them in posts, pages or widgets using a search-engine-friendly Schema markup.

This plugin will output reviews using Schema.org markup to help search engines like Google identify and integrate the reviews with your listing.

* Add 5-star or numbered ratings to reviews
* Add a photo of the reviewer
* Add a link to the review or the reviewer's organization to increase credibility
* Schema.org markup for better SEO
* Add reviews to any page, post or sidebar
* Show a single review, all reviews or a category of reviews
* List reviews or cycle through them with a fader

**Sorry, it does not allow users to submit reviews.**

This plugin is one of a number of plugins provided by [Theme of the Crop](https://themeofthecrop.com/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Good%20Reviews) to help you build better restaurant websites. Take a look at our [great WordPress restaurant themes](https://themeofthecrop.com/themes/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Good%20Reviews), and our plugins to [take online reservations](https://themeofthecrop.com/plugins/restaurant-reservations/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Good%20Reviews), create [responsive online menus](https://themeofthecrop.com/plugins/food-and-drink-menu/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Good%20Reviews) and [boost a restaurant's SEO](https://themeofthecrop.com/restaurant-seo/?utm_source=Plugin&utm_medium=Plugin%20Description&utm_campaign=Good%20Reviews).

= How to use =

View the [help guide](http://doc.themeofthecrop.com/plugins/good-reviews-wp/?utm_source=Plugin&utm_medium=Plugin%Description&utm_campaign=Good%20Reviews) to learn how to add and display reviews.

= Developers =

This plugin is packed with hooks so you can extend it as needed. Development takes place on [GitHub](https://github.com/NateWr/good-reviews-wp/), so fork it up.

== Installation ==

1. Unzip `good-reviews-wp.zip`
2. Upload the contents of `good-reviews-wp.zip` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Create Reviews from the WordPress admin dashboard.

== Frequently Asked Questions ==

= Is there a shortcode to print all of my reviews? =

Yes, use the `[good-reviews]` shortcode. Consult the help documentation in the /docs/ folder for details on the shortcode attributes available.

= Can users submit reviews with this plugin? =

No, this plugin only allows you to display reviews you've entered yourself.

= Can I customize the output of the reviews? =

Yes, but in order to do this you'll need to be able to write PHP, HTML and CSS code. The `grfwp_print_reviews_output` filter will allow you to hook in before reviews are printed and output your own markup. You'll find it [here](https://github.com/NateWr/good-reviews-wp/blob/master/includes/template-functions.php#L43).

= Is there a template function I can use to print reviews? =

Yes, check out the [grfwp_print_reviews()](https://github.com/NateWr/good-reviews-wp/blob/master/includes/template-functions.php#L41) function.

== Screenshots ==

1. Display any or all of your reviews on a page with the [good-reviews] shortcode.
2. Add information about the review or link out to it to establish credibility.
3. A widget is included to display one or all of the reviews in any sidebar.

== Changelog ==

= 1.2.1 (2015-09-09) =
* Add: Hebrew translation
* Update: textdomain usage to support upcoming plugin language packs
* Fix: reviews aren't ordered by Menu Order on the frontend
* Fix: negative rating numbers cause problems with start rating format
* Fix #2: Strict Standards error can appear if error reporting is high

= 1.2 (2014-12-03) =
* Add .hentry class to ensure valid Google Structured Data in all cases
* Add excerpt shortcode attribute and widget option to replace use of more global
* Don't automatically append to content in search results to improve theme compatibility

= 1.1.1 (2014-09-15) =
* Prevent flash display of all reviews when cycling a group of reviews

= 1.1 (2014-09-15) =
* Add widget/shortcode options to display a category of reviews, randomize the order and limit the number displayed.
* Add fader display option for cycling through multiple reviews.

= 1.0 (2014-07-16) =
* Initial public release on WordPress.org

= 0.0.2 (2014-05-27) =
* Fix letter-case error when loading file

= 0.0.1 (2014-05-26) =
* Initial release

== Upgrade Notice ==

= 1.2.1 =
This release fixes some minor bugs, including one related to the ordering of reviews. You might want to check the order of your reviews after updating. It also adds a Hebrew translation and prepares for the upcoming language packs feature.

= 1.2 =
This release replaces modifies when and how review excerpts are used in place of full reviews. This was done to comply with upcoming changes in WP 4.1. In almost all cases, this will not effect your site. But if it does, there is now an "excerpt" widget option and shortcode attribute if you need it.

= 1.1.1 =
This is a minor fix for featured introducted in 1.1: You can display reviews from a single category, randomize the order and limit how many are displayed. It also adds a fader display mode that will cycle through reviews one by one, fading between each one, instead of listing them all at once.

= 1.1 =
This upgrade adds new features for the shortcode and widget. You can display reviews from a single category, randomize the order and limit how many are displayed. It also adds a fader display mode that will cycle through reviews one by one, fading between each one, instead of listing them all at once.

= 1.0 =
This is an initial public release on the WordPress.org repository.
