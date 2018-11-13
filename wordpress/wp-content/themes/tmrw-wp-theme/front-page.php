<?php
  $context = Timber::get_context();
  $post = new TimberPost();
  $args = array('post_type' => array('post','legacy'), 'posts_per_page' => 100);   	
  $issueArgs = array('post_type' => 'issue', 'posts_per_page' => 100);   
  $productArgs = array('post_type' => 'product', 'posts_per_page' => 100);   
  $context['issueProds'] = Timber::get_posts($productArgs);
  $context['issues'] = Timber::get_posts($issueArgs);
	$context['posts'] = Timber::get_posts($args);
  $context['post'] = $post;
  Timber::render('front-page.twig', $context );
?>