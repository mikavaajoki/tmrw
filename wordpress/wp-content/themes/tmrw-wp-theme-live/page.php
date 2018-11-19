<?php

  $context = Timber::get_context();
  $post = new TimberPost();
  $context['post'] = $post;
  $args = array('post_type' => array('post','legacy'), 'posts_per_page' => 100);   
	$context['posts'] = Timber::get_posts($args);
	$context['categories'] = Timber::get_terms('category', array('orderby' => 'name','order' => 'ASC'));
	Timber::render( array('page-' . $post->post_name . '.twig','page.twig'), $context );
?>

