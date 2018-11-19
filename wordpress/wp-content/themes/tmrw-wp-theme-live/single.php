<?php

  $context = Timber::get_context();
  $post = new TimberPost();
  $context['post'] = $post;
  $args = array('post_type' => array('post','legacy'), 'posts_per_page' => 100);   
	$context['posts'] = Timber::get_posts($args);
	Timber::render( array(
	    'single-' . $post->post_type . '.twig',
	    'single.twig'
	), $context );
?>
