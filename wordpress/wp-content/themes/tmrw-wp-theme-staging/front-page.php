<?php
  $context = Timber::get_context();
  $post = new TimberPost();
  $args = array('post_type' => 'post', 'posts_per_page' => 100);   	
  $issueArgs = array('post_type' => 'issue', 'posts_per_page' => 100);   	
  $context['issues'] = Timber::get_posts($issueArgs);
	$context['posts'] = Timber::get_posts($args);
  $context['post'] = $post;
  Timber::render('front-page.twig', $context );
?>