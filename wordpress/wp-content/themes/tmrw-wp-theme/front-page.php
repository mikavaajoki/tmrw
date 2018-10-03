<?php
  $context = Timber::get_context();
  $post = new TimberPost();
  $args = array('post_type' => 'post', 'posts_per_page' => 100);   
	$context['posts'] = Timber::get_posts($args);
  $context['post'] = $post;
  Timber::render('front-page.twig', $context );
?>