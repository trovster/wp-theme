Classy
======

Classy is a set of classes for WordPress, which wraps post types (Posts, Pages and custom post types) and users in a class.
It includes common methods which replicate built in functionality (such as `the_content` and `the_permalink`), but means you
can easily override their behaviour per post type without having to modify the view template files.

##Example##

    <?php
    $classy_post = Classy_Post::find_by_id(get_the_ID());
    $classy_post->get_ID();
    $classy_post->get_title();
    $classy_post->get_content();
    $classy_post->the_attr('class');
    $classy_post->the_attr('data');
    ?>>