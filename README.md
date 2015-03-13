# RepeatableMetaGroup (RMG)
Easily create repeatable meta field groups with this RepeatableMetaGroup plugin


##Initialize
You can initialize RMG as a plugin, or as a part of your theme. If you want to use it as a plugin, simply download the zip from github and install it as other plugins and then activate it. 

To use it as a part of your theme, keep it inside your theme folder and include it like this. Let's conside you've kept it inside libs/rmg folder in your theme

```php
if(!class_exists("RepeatableMetaGroup")){
    require_once dirname(__FILE__)."/libs/rmg/index.php";

```

##Turn off the default metabox
RMG comes with a default metabox for example purpose. You can easily turn it off by adding a hook like this

```php
add_filter("rmg_display_default_metabox",function(){
	return false;
});
```

or 

```php
function turn_off_default_rmg_mb() {
    return false;
}

add_filter("rmg_display_default_metabox", 'turn_off_default_rmg_mb');
```

## Create a new Repeatable Metabox
It's very easy to create a new repeatable group metabox using RMG. Let's have a look at the following code block

```php
add_filter("rmg_metaboxes", 'create_rmg_metaboxes');
function create_rmg_metaboxes($metaboxes){
	$metaboxes[] = array(
        "name"       => "Sample MetaBox",
        "id"         => "rgm_smb2",
        "post_types" => array("post"),
        "context"    => "normal",
        "priority"   => "default",
        "fields"     => array(
            array(
                "id"      => "name",
                "type"    => "text",
                "name"    => "Name",
                "default" => ""
            ),
            array(
                "id"      => "email",
                "type"    => "text",
                "name"    => "Email",
                "default" => ""
            ),
            array(
                "id"      => "website",
                "type"    => "text",
                "name"    => "Website",
                "default" => "http://google.com"
            ),
            array(
                "id"      => "favcolor",
                "type"    => "color",
                "name"    => "Your favorite color",
                "default" => "#212121"
            ),
            array(
                "id"      => "title",
                "type"    => "select",
                "name"    => "Title",
                "default" => "1",
                "options" => array(
                    "1" => "Mr.",
                    "2" => "Miss",
                    "3" => "Mrs."
                )
            ),
            array(
                "id"      => "photos",
                "type"    => "gallery",
                "name"    => "Photo Gallery",
            ),
        )
    );
    
    return $metaboxes;
}

```

That's mainly it. In the ```post_types``` parameter you can pass "post","page" and "attachment" as an array.

##Get the values
Retrieving values from RMG metaboxes is super easy. You can simply get those, properly grouped, using the following code

```php
$values = get_post_meta($post_id,"rgm_smb2",true);
```

RMG stores all the values in a metakey named same as the id of the metabox. 

##Available fields
At this moment, RMG supports the following fields

1. text
2. textarea
3. wysiwyg
4. color
5. select
6. gallery

Please note that gallery field returns only attachment/image id.