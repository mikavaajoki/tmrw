  <?php

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(
	array(
	'key' => 'group_5aab97c8d1890',
	'title' => 'Options',
	'fields' => array(
		array(
			'key' => 'field_5aab9a73936dc',
			'label' => 'Social Media',
			'name' => 'social_',
			'type' => 'group',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'row',
			'sub_fields' => array(
				array(
					'key' => 'field_5aab9aff936dd',
					'label' => 'Facebook',
					'name' => 'facebook',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_5aab9b22936de',
					'label' => 'Twitter',
					'name' => 'twitter',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_5aab9b2d936df',
					'label' => 'Instagram',
					'name' => 'instagram',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_5aab9b3b936e0',
					'label' => 'Tumblr',
					'name' => 'tumblr',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
			),
		),

		array(
			'key' => 'field_5bcb54d21c5a2',
			'label' => 'Recommended Feature',
			'name' => 'recommended_feature',
			'type' => 'post_object',
			'instructions' => 'This is where you select the homepage recommended feature.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
				0 => 'post'
			),
			'taxonomy' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'return_format' => 'object',
			'ui' => 1,
		),

		array(
			'key' => 'field_5bcb54d21c5a2',
			'label' => 'Recommended Feature',
			'name' => 'recommended_feature',
			'type' => 'post_object',
			'instructions' => 'This is where you select the homepage recommended feature.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array(
				0 => 'post'
			),
			'taxonomy' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'return_format' => 'object',
			'ui' => 1,
		),

		
		array(
			'key' => 'group_5be0b3492df03',
			'label' => 'Instagram Module',
			'name' => 'instagram_module',
			'type' => 'group',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'row',
			'sub_fields' => array(
		array(
			'key' => 'field_5be0b355d1d81',
			'label' => 'Title',
			'name' => 'title',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5be0b35bd1d82',
			'label' => 'Subtitle',
			'name' => 'subtitle',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5be0b36bd1d83',
			'label' => 'Image',
			'name' => 'image',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
	),
),
	



array(
			'key' => 'group_5be0366a160c0',
			'label' => 'Front Page Video Module',
			'name' => 'video_module',
			'type' => 'group',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => '0',			
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'row',
			'sub_fields' => array(
				array(
			'key' => 'field_5be037b154a71',
			'label' => 'Title',
			'name' => 'title',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
					'conditional_logic' => 0,

			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5be0383954a73',
			'label' => 'Subtitle',
			'name' => 'subtitle',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
					'conditional_logic' => 0,

			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5be037e054a72',
			'label' => 'Description',
			'name' => 'description',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
					'conditional_logic' => 0,

			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => '',
		),
		array(
			'key' => 'field_5be0369054a6f',
			'label' => 'Video',
			'name' => 'video',
			'type' => 'file',
			'instructions' => '',
			'required' => 0,
					'conditional_logic' => 0,

			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'url',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'mp4, avi',
		),
		array(
			'key' => 'field_5be0379654a70',
			'label' => 'Video Poster',
			'name' => 'video_poster',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
					'conditional_logic' => 0,

			'wrapper' => array(
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'acf-options',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));


endif;