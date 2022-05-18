# Gallery Filter

![gif of Gallery Filter](gallery.filter.gif)

## Requirements
You **need** to have **one** of the following:
* Advanced Custom Fields PRO
* Meta Box
* Pods
Install [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/)
* `CPT UI` → `Add/Edit Post Types`
	* Post Type Slug: gallery
	* Plural Label: Galleries
	* Singular Label: Gallery
### Advanced Custom Fields PRO
* `Custom Fields` → `Add New`
		* Title: Gallery
		* Rules: Post Type is equal to Post
		
		* `+ Add Fields`
			* Field Label: Gallery
			* Field Name: gallery
			* Field Type: Gallery
### Meta Box
* `Meta Box` → `Post Types` → `New Post Type`
	* Plural name: `Galleries`
	* Singular name: `Gallery`
	* Slug: `gallery`
	
* `Meta Box` → `Custom Fields` → `Add New`
	* `+ Add Field` → `Image Advanced`
		* Change ID to `gallery`
		* Settings → Location → Post Type → Select Gallery
### Pods
* `Pods` → `Extend Existing`
	* Content Type: `Post Type`
	* Post Type: `Galleries`
		
* `Manage Fields` → `Add Field`
	* Label: `Gallery`
	* Name: `gallery`
	* Field Type: `File / Image / Video`
	* Additional Field Options
		* Upload Limit: `Multiple Files`