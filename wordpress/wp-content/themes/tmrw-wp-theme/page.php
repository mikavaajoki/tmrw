<?php

  $context = Timber::get_context();
  $post = new TimberPost();
  $context['post'] = $post;
  $args = array('post_type' => 'post', 'posts_per_page' => 100);   
	$context['posts'] = Timber::get_posts($args);
  Timber::render('page.twig', $context );

?>
