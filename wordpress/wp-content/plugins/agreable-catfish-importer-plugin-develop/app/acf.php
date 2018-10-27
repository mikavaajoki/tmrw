<?php

add_action( 'acf/init', function () {

	acf_add_local_field_group( array(
		'key'                   => 'article_catfish_importer_group',
		'title'                 => 'Catfish Importer',
		'fields'                => array(
			array(
				'key'               => 'article_catfish_importer_imported',
				'label'             => 'Imported',
				'name'              => 'catfish_importer_imported',
				'type'              => 'true_false',
				'instructions'      => 'Is this post imported from Catfish?',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '49%',
					'class' => '',
					'id'    => '',
				),
				'message'           => '',
				'default_value'     => 0,
			),
			array(
				'key'               => 'article_catfish_imported_url',
				'label'             => 'URL',
				'name'              => 'catfish_importer_url',
				'type'              => 'url',
				'instructions'      => 'The URL from imported from.',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '49%',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'placeholder'       => '',
			),
			array(
				'key'               => 'article_catfish_importer_date_created',
				'label'             => 'Imported',
				'name'              => 'catfish_importer_date_created',
				'type'              => 'date_time_picker',
				'instructions'      => 'The import date for this post.',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '49%',
					'class' => '',
					'id'    => '',
				),
				'show_date'         => 'true',
				'date_format'       => 'yy-m-d',
				'time_format'       => 'h:mm tt',
				'show_week_number'  => 'false',
				'picker'            => 'slider',
				'save_as_timestamp' => 'true',
				'get_as_timestamp'  => 'true',
			),
			array(
				'key'               => 'article_catfish_importer_date_updated',
				'label'             => 'Updated',
				'name'              => 'catfish_importer_date_updated',
				'type'              => 'date_time_picker',
				'instructions'      => 'The last updated date for this post.',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '49%',
					'class' => '',
					'id'    => '',
				),
				'show_date'         => 'true',
				'date_format'       => 'yy-m-d',
				'time_format'       => 'h:mm tt',
				'show_week_number'  => 'false',
				'picker'            => 'slider',
				'save_as_timestamp' => 'true',
				'get_as_timestamp'  => 'true',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'post',
				),
				array(
					'param'    => 'current_user_role',
					'operator' => '==',
					'value'    => 'administrator',
				),
			),
		),
		'menu_order'            => 100,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => '',
	) );


	register_field_group( array(
		'key'                   => 'group_catfish_importer_plugin',
		'title'                 => 'Catfish Importer Credentials',
		'fields'                => array(
			array(
				'key'               => 'apple_news_url',
				'label'             => 'Catfish Website URL',
				'name'              => 'catfish_website_url',
				'type'              => 'url',
				'instructions'      => 'Please put in either http://www.stylist.co.uk/ or http://www.shortlist.com/',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => '',
				'maxlength'         => '',
				'readonly'          => 0,
				'disabled'          => 0,
			),
			array(
				'key'        => 'catfish_default_author',
				'label'      => 'Default Article Author',
				'name'       => 'catfish_default_author',
				'type'       => 'user',
				'required'   => 1,
				'allow_null' => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'acf-options',
				),
			),
		),
		'menu_order'            => 10,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => '',
	) );

} );
