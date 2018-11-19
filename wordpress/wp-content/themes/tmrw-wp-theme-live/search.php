<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */
  $context = Timber::get_context();

  $context['search_query'] = get_search_query();
  $context['posts'] = new Timber\PostQuery();
  $context['pagination'] = Timber::get_pagination();
	Timber::render( array('page-search.twig','search.twig'), $context );