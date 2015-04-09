 === Company Directory ===
Contributors: richardgabriel, ghuger
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V7HR8DP4EJSYN
Tags: staff, directory, directory plugin, staff directory, staff skills, skills matrix, directory with contact form, staff skills matrix, staff skills directory
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 1.2.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Company Directory is a simple-to-use plugin for adding Staff or Faculty Members with a Directory to your WordPress Theme, using a shortcode or a widget.

== Description ==

The Company Directory is an easy way to add your Staff to your website.  Staff are presented in several easy to understand layouts, including a list and single views, allowing visitors to get to know your company and capabilities.

The Pro Version of the Company Directory includes advanced features such as a Table View and Grid View, as well as direct support!

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the contents of `/staff-directory-pro/` to the `/wp-content/plugins/` directory
2. Activate Staff Directory through the 'Plugins' menu in WordPress
3. Visit this address for information on how to configure the plugin: http://goldplugins.com/documentation/company-directory-documentation/

### Adding a New Staff Member ###

Adding a New Staff Member is easy!  There are 3 ways to start adding a new Staff Member

**How to Add a New Staff Member**

1. Click on "+ New" -> Staff Member, from the Admin Bar _or_
2. Click on "Add New Staff Member" from the Menu Bar in the WordPress Admin _or_
3. Click on "Add New Staff Member" from the top of the list of Staff Members, if you're viewing them all.

**New Staff Member Content**

You have a few things to pay attention to:

* **Staff Member Title:** This will be displayed, in the default list, as the Staff Member's Name.
* **Staff Member Body:** This is the Bio or Description of the Staff Member.
* **First Name:** This field is used for List, Grid, and other views where having the First Name separate is necessary.
* **Last Name:** This field is used for List, Grid, and other views where having the Last Name separate is necessary.
* **Title:** This is the Staff Member's Job Title, and it is displayed below their name in the default list.
* **Phone:** This field is displayed as part of the Contact Information meta data, below the Staff Member's Name, Title, and Bio.
* **Email:** This field is displayed as part of the Contact Information meta data, below the Staff Member's Name, Title, and Bio  This will be displayed as a clickable link.
* **Featured Image:** This image is shown to the left of the Staff Member.  We recommend using appropriately sized images for your layout.

### Editing a Staff Member ###

 **This is as easy as adding a New Staff Member!**

1. Click on "Staff Members" in the Admin Menu.
2. Hover over the Staff Member you want to Edit and click "Edit".
3. Change the fields to the desired content and click "Update".

### Deleting a Staff Member ###

 **This is as easy as adding a New Staff Member!**

1. Click on "Staff Members" in the Admin Menu.
2. Hover over the Staff Member you want to Delete and click "Delete".
  
  **You can also change the Status of a Staff Member, if you want to keep it on file.**

### Displaying a List of Staff ###

To display a list of staff on your website, use the shortcode ```[staff_list]``` in the page content area that you want them to appear.  To limit the Staff displayed to a specific category, use the shortcode ```[staff_list category='the_slug']```, where the value of ```category``` is the slug of the Category you want displayed.  You can locate the slugs by looking at the List of Staff Member Categories.   To display a Table of All Staff, use the shortcode ```[staff_list style='table']```.  To display a Grid of All Staff, use the shortcode ```[staff_list style='grid']```.  **Please Note:** Company Directory Pro is required to gain access to advanced features such as the Grid and Table views.

### Displaying a Single Staff Member ###

To display a single Staff Member on your Website, use the shortcode ```[staff_member id="123"]```, where the value of id is the Staff Member's internal ID (you can get this shortcode by looking at the Staff Member List or the Edit Staff Member screen, inside WordPress.)

== Frequently Asked Questions ==

= Ack!  This Staff Directory is too easy to use! Will you make it more complicated? =

Never!  Easy is in our name!  If by complicated you mean new and easy to use features, there are definitely some on the horizon!

= Hey! The Single Staff Member View isn't quite matching my theme - what do I do? =

Our plugin supports creating your own templates, and it includes one by default for the Single Staff Member view.  If the Default HTML isn't right for you, go to the /staff-directory/templates/ folder and copy the file named "single-staff-member.php" to your Theme directory.  From there, you can modify this file as needed to get the HTML to work with your specific Theme.

== Screenshots ==

1. This is the Add New Staff Member Page.
2. This is the List of Staff Members - from here you can Edit or Delete a Staff Member.
3. This is an example of the Staff List being displayed on the 2014 WordPress theme.

== Changelog ==

= 1.2.1 =
* Minor Fixes

= 1.2 =
* Updates Readme and Documentation to better explain plugin use.
* Adds Taxonomies to Staff Members.
* Adds Shortcode to display an individual Staff Member.
* Various UI updates.

= 1.1.1 =
* Compatibility update for WP 4.1.1.

= 1.1 =
* Fix: address issue with incorrectly formatted e-mail link.

= 1.0 =
Initial Release!!

== Upgrade Notice ==

* 1.2.1: Staff Member Taxonomies, UI updates, Documentation, and more!