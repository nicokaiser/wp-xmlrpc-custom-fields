=== XML-RPC custom_fields for Comments ===
Contributors: Nico Kaiser
Tags: comments, custom, field, custom field
Requires at least: 3.4
Tested up to: 3.4.2
License GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add support for custom_fields (Meta data) for Comment functions in XML-RPC.

== Description ==

Like with Posts, custom_fields can now be added to the WordPress XML-RPC functions by adding a "custom_fields" struct to the "content" struct, see wp.newPost for details:

* wp.newComment
* wp.editComment

All metadata (custom_fiedls) is returned by

* wp.getComment
* wp.getComments
