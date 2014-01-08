<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'RWMB_Select_Text_Field' ) )
{
	class RWMB_Select_Text_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', array(), RWMB_VER );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$meta[0] = isset( $meta[0] ) ? $meta[0] : '';
			$meta[1] = isset( $meta[1] ) ? $meta[1] : '';

			$html = sprintf(
				'<select class="rwmb-select rwmb-select2" name="%s[]" id="%s" size="%s"%s>',
				$field['field_name'],
				$field['id'],
				$field['size2'],
				$field['multiple'] ? ' multiple="multiple"' : ''
			);

			$html .= self::options_html( $field, $meta[0] );

			$html .= '</select>';
			$html .= sprintf(
				' <input type="text" class="rwmb-text" name="%s[]" id="%s" value="%s" placeholder="%s" size="%s">',
				$field['field_name'],
				$field['id'],
				$meta[1],
				$field['placeholder'],
				$field['size']
			);

			return $html;
		}

		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$single = $field['clone'] || !$field['multiple'];
			$meta = get_post_meta( $post_id, $field['id'], $single );
			$meta = ( !$saved && '' === $meta || array() === $meta ) ? array('','') : $meta;

			if(!empty($meta)) {
				foreach ($meta as $key => $value) {
					$meta[$key] = array_map( 'esc_attr', (array) $meta[$key] );
				}
			}
			return $meta;
		}

		/**
		 * Save meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			foreach ( $new as &$arr ) {

        		if ( empty( $arr[0] ) || empty( $arr[1] ) ) {
        			//if any of them is empty then we don't save
		        	$arr = false;

		        }
      		}
      			
      		$new = array_filter( $new );
      		
      		if ( !$field['clone'] ) {
				parent::save( $new, $old, $post_id, $field );
				return;
			}

			if ( empty( $new ) ) {
				delete_post_meta( $post_id, $field['id'] );
			}
			else {
				update_post_meta( $post_id, $field['id'], $new );
			}
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'desc'        => '',
				'name'        => $field['id'],
				'size2'       => $field['multiple'] ? 5 : 0,
				'size'        => 30,
				'placeholder' => '',
				'select_placeholder' => ''
			) );
			return $field;
		}

		/**
		 * Creates html for options
		 *
		 * @param array $field
		 * @param mixed $meta
		 *
		 * @return array
		 */
		static function options_html( $field, $meta )
		{
			$html = '';
			if ($field['select_placeholder']!="") $html = '<option value="">'.$field['select_placeholder'].'</option>';

			$option = '<option value="%s"%s>%s</option>';

			foreach ( $field['options'] as $value => $label )
			{
				$html .= sprintf(
					$option,
					$value,
					selected( in_array( $value, (array)$meta ), true, false ),
					$label
				);
			}

			return $html;
		}
	}
}
