<?php
	$current_category = single_cat_title( '', false);
	$context = Timber::get_context();
	$args = array('post_type' => 'post', 'posts_per_page' => 100,'category_name' => ''.$current_category.'');   
	$context['posts'] = Timber::get_posts($args);
	$context['categories'] = Timber::get_terms('category', array('orderby' => 'name','order' => 'ASC'));
	Timber::render('category.twig', $context );
?>