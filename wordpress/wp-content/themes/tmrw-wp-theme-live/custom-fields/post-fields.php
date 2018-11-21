<?php

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
  'key' => 'group_5aa93f8988f02',
  'title' => 'Article Details',
  'fields' => array(
    array(
      'key' => 'field_5aa942f05fc51',
      'label' => 'Article Details',
      'name' => 'article_details',
      'type' => 'group',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array(
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'layout' => 'block',
      'sub_fields' => array(
        array(
          'key' => 'field_subtitle',
          'label' => 'Subtitle',
          'name' => 'subtitle',
          'type' => 'text',
          'instructions' => '',
          'required' => 1,
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
          'key' => 'field_5aa9be1149f5a',
          'label' => 'Opening Image',
          'name' => 'opening_image',
          'type' => 'gallery',
          'required' => 1,
          'conditional_logic' => 0,
          'min' => 1,
          'max' => 2,
          'insert' => 'append',
          'library' => 'all'
        ),
        array(
          'key' => 'field_5aa943285fc53',
          'label' => 'Standfirst',
          'name' => 'standfirst',
          'type' => 'textarea',
          'instructions' => '',
          'required' => 1,
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
          'key' => 'field_5aa951377b88c',
          'label' => 'Author',
          'name' => 'author',
          'type' => 'user',
          'required' => 1,
          'conditional_logic' => 0,
          'role' => '',
          'allow_null' => 0,
          'multiple' => 0,
          'return_format' => 'array'
        ),
        array(
          'key' => 'field_5aa943375fc54',
          'label' => 'Credits',
          'name' => 'credits',
          'type' => 'repeater',
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'collapsed' => '',
          'min' => 0,
          'max' => 0,
          'layout' => 'table',
          'button_label' => '',
          'sub_fields' => array(
            array(
              'key' => 'field_5aa943485fc55',
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
              'key' => 'field_5aa9435f5fc56',
              'label' => 'Name',
              'name' => 'name',
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
      ),
    ),
  ),
  'location' => array(
    array(
      array(
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'post',
      ),
    ),
  ),
  'menu_order' => 0,
  'position' => 'normal',
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => array(
    0 => 'permalink',
    1 => 'the_content',
    2 => 'excerpt',
    3 => 'discussion',
    4 => 'comments',
    5 => 'revisions',
    6 => 'slug',
    7 => 'format',
    8 => 'page_attributes',
    9 => 'tags',
    10 => 'send-trackbacks',
  ),
  'active' => 1,
  'description' => '',
));


acf_add_local_field_group(array (
  'key' => 'group_57a74b51e813b',
  'title' => 'Section Widgets',
  'fields' => array (
    array (
      'key' => 'field_57a74b6631edc',
      'label' => 'Blocks',
      'name' => 'blocks',
      'type' => 'flexible_content',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'button_label' => 'Add Row',
      'min' => '',
      'max' => '',
      'layouts' => array (
        array (
          'key' => '57a74b6f42da2',
          'name' => 'paragraph',
          'label' => 'Paragraph',
          'display' => 'block',
          'sub_fields' => array (
            array (
              'key' => 'field_57a74cbce07c8',
              'label' => '',
              'name' => 'paragraph',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'tabs' => 'all',
              'toolbar' => 'full',
              'media_upload' => 1,
            ),
          ),
          'min' => '',
          'max' => '',
        ),
        array (
          'key' => '57a74cfbe07ca',
          'name' => 'embed',
          'label' => 'Embed',
          'display' => 'block',
          'sub_fields' => array (
            array (
              'key' => 'field_57a74d02e07cb',
              'label' => 'Embed',
              'name' => 'embed',
              'type' => 'oembed',
              'instructions' => '',
              'required' => '',
              'conditional_logic' => '',
              'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'width' => '',
              'height' => '',
            ),
            array (
              'key' => 'field_57a7509e8fa06',
              'label' => 'Caption',
              'name' => 'embed_caption',
              'type' => 'text',
              'instructions' => '',
              'required' => '',
              'conditional_logic' => '',
              'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'placeholder' => '',
              'prepend' => '',
              'append' => '',
              'maxlength' => '',
              'readonly' => 0,
              'disabled' => 0,
            ),
          ),
          'min' => '',
          'max' => '',
        ),
        array (
          'key' => '57a74d10e07cc',
          'name' => 'image',
          'label' => 'Image',
          'display' => 'block',
          'sub_fields' => array (
            array (
              'key' => 'field_57a74d1ce07cd',
              'label' => 'Image',
              'name' => 'image',
              'type' => 'image',
              'instructions' => '',
              'required' => '',
              'conditional_logic' => '',
              'wrapper' => array (
                'width' => '',
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
            array(
              'key' => 'field_5bc7685c8aca1',
              'label' => 'Width',
              'name' => 'width_',
              'type' => 'select',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'choices' => array(
            'small' => 'Small',
            'standard' => 'Standard',
            'large' => 'Large',
            'full-width' => 'Full Width',
            ),
            'default_value' => array(
            0 => 'standard',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
            ),
            array (
              'key' => 'field_57a74f75dddcf',
              'label' => 'Caption',
              'name' => 'image_caption',
              'type' => 'text',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'placeholder' => '',
              'prepend' => '',
              'append' => '',
              'maxlength' => '',
              'readonly' => 0,
              'disabled' => 0,
            ),
          ),
          'min' => '',
          'max' => '',
        ),
        array(
          'key' => 'field_5beff4b4e5d1b',
          'label' => 'Video',
          'name' => 'video',
          'type' => 'group',
          'required' => 0,
          'conditional_logic' => 0,
          'layout' => 'row',
          'sub_fields' => array(
            

            array(
              'key' => 'field_5beff59ce6c7d',
              'label' => 'Video',
              'name' => 'video',
              'type' => 'file',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
              ),
              'return_format' => 'url',
              'library' => 'all',
              'min_size' => '',
              'max_size' => '',
              'mime_types' => 'avi, mp4',
            ),
            array(
              'key' => 'field_5beff71a04ff6',
              'label' => 'Caption',
              'name' => 'caption',
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
        array (
          'key' => '5bc77349a8459',
          'name' => 'quote',
          'label' => 'Quote',
          'display' => 'block',
          'sub_fields' => array (
              array(
                'key' => 'field_5bc77349a8459',
                'label' => 'Quote',
                'name' => 'quote',
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
           ),
          'min' => '',
          'max' => '',
        ),
        array (
          'key' => '5818ec52b735b',
          'name' => 'heading',
          'label' => 'Heading',
          'display' => 'block',
          'sub_fields' => array (
             array (
              'key' => 'field_5818ec52b735b',
              'label' => 'Heading',
              'name' => 'heading',
              'type' => 'text',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array (
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
          'min' => '',
          'max' => '',
        ),
      ),
    ),
  ),
  'location' => array (
    array (
      array (
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'page',
      ),
    ),
    array (
      array (
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'post',
      ),
    ),
  ),
  'menu_order' => 3,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => array (
    0 => 'the_content',
  ),
  'active' => 1,
  'description' => '',
));

endif;


if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
  'key' => 'group_5bea1c9d1377b',
  'title' => 'Issue Heading',
  'fields' => array(
    array(
      'key' => 'field_5bea1eefd3839',
      'label' => 'Number',
      'name' => 'number',
      'type' => 'number',
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
      'min' => '',
      'max' => '',
      'step' => '',
    ),
    array(
      'key' => 'field_5bea1eefd4009',
      'label' => 'Heading',
      'name' => 'heading',
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
      'min' => '',
      'max' => '',
      'step' => '',
    ),
  ),
  'location' => array(
    array(
      array(
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'product',
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