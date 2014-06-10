<?php
	/*
		Template Name: The Homepage
	*/
?>
<?php get_header(); ?>

<?php $classy_page = Classy_Page::find_by_id(get_the_ID()); ?>

<?php echo Classy::loop(Classy_Slideshow::get_options(), 'loop', 'slideshow'); ?>

<div id="content-primary">

	<div<?php $classy_page->the_attr('class'); ?>>
		<?php $classy_page->the_content(); ?>
	</div>
	
	<?php echo Classy::loop(Classy_Featured::get_options(), 'loop', 'featured'); ?>

<!-- end of div #content-primary -->
</div>

<?php get_footer(); ?>