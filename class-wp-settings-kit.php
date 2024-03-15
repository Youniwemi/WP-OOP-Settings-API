<?php
/**
 * This file is part of Wp Settings Kit, a fork of "WP-OOP-Settings-API" maintained by "Rahal Aboulfeth".
 * "WP-OOP-Settings-API" was created by ( Ahmad Awais :@MrAhmadAwais )  and is licensed under GNU GENERAL PUBLIC Version 2 LICENSE].
 * This fork includes additional features and improvements by "Rahal Aboulfeth". and is released under the same license (GPL2).
 *
 * @copyright Copyright (c) 2023 Rahal Aboulfeth
 * @license   GPL2
 *
 * @package WP_SKIT_VERSION
 * @version '1.1.4'
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Settings_Kit.
 *
 * WP Settings Kit Class.
 *
 * @since 1.0.0
 */

if ( ! class_exists( 'WP_Settings_Kit' ) ) :

	define( 'WP_SKIT_VERSION', '1.1.4' );

	/**
	 * Main WP Settings Kit class
	 */
	class WP_Settings_Kit {

		/**
		 * Sections array.
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		private $sections_array = array();

		/**
		 * Fields array.
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		private $fields_array = array();

		/**
		 * Metabox options
		 *
		 * @var ?array
		 */
		private $metabox = null;

		/**
		 * Options
		 *
		 * @var array
		 */
		private $options = null;

		/**
		 * Setting name : will affect the hook name setting_ready
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		protected $settings_name = null;


		/**
		 * Constructs a new instance.
		 *
		 * @param ?array $options The options.
		 * @param ?array $metabox The metabox.
		 * @since 1.0.0
		 */
		public function __construct( $options = null, $metabox = null ) {
			$this->options = $options;
			if ( $metabox ) {
				// Use for post metabox.
				$this->metabox = $metabox;
				add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
				add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
			} else {
				$this->init_consts();
				if ( is_admin() ) {
					// Enqueue the admin scripts.
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

					// Hook it up.
					add_action( 'admin_init', array( $this, 'admin_init' ) );

					// Menu.
					add_action( 'admin_menu', array( $this, 'admin_menu' ) );
					$this->init_options();
				}

				// To allow multiple instanciations off this class.
				if ( $this->settings_name ) {
					do_action( 'settings_ready_' . $this->settings_name );
				} else {
					do_action( 'settings_ready' );
				}
			}
		}

		/**
		 * Should we show the field or the section.
		 *
		 * @param array $field_or_section The field or section config array.
		 *
		 * @return bool    should show
		 */
		protected function should_show( array $field_or_section ) {
			if ( isset( $field_or_section['show_if'] ) && is_callable( $field_or_section['show_if'] ) ) {
				return $field_or_section['show_if']();
			}
			return true;
		}


		/**
		 * Initializes the sections and fields.
		 */
		public function init_options() {
			foreach ( $this->options as $section ) {
				if ( ! $this->should_show( $section ) ) {
					continue;
				}

				$name = $title = $fields = $show_if = $description = $side_panel = null;
				extract( $section );
				$this->add_section(
					array(
						'id'         => $name,
						'title'      => $title,
						'show_if'    => $show_if,
						'desc'       => $description,
						'side_panel' => $side_panel,
					)
				);
				if ( $fields ) {
					foreach ( $fields as $field ) {
						if ( ! $this->should_show( $field ) ) {
							continue;
						}

						$this->add_field( $name, $field );
					}
				}
			}
		}

		/**
		 * Defines the constants from the saved options or from the fields default values if options are not saved.
		 */
		public function init_consts() {
			foreach ( $this->options as $section ) {
				$options = get_option( $section['name'] );
				if ( $options ) {
					foreach ( $options as $key => $value ) {
						$option_name = $section['name'] . '_' . $key;
						if ( ! defined( $option_name ) ) {
							define( $option_name, $value );
						}
					}
				}
				// init from default if constant is not defined.
				foreach ( $section['fields'] as $field ) {
					$option_name = $section['name'] . '_' . $field['id'];
					if ( ! defined( $option_name ) && array_key_exists( 'default', $field ) ) {
						define( $option_name, $field['default'] );
					}
				}
			}
		}

		/**
		 * Admin Scripts.
		 *
		 * @since 1.0.0
		 */
		public function admin_scripts() {
			// jQuery is needed.
			wp_enqueue_script( 'jquery' );

			// Color Picker.
			wp_enqueue_script(
				'iris',
				admin_url( 'js/iris.min.js' ),
				array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
				true,
				true
			);

			// Media Uploader.
			wp_enqueue_media();
		}


		/**
		 * Set Sections.
		 *
		 * @param array $sections Sections.
		 * @since 1.0.0
		 */
		public function set_sections( array $sections ) {
			// Assign to the sections array.
			$this->sections_array = $sections;

			return $this;
		}


		/**
		 * Add a single section.
		 *
		 * @param array $section Section Config.
		 * @since 1.0.0
		 */
		public function add_section( array $section ) {
			// Assign the section to sections array.
			$this->sections_array[] = $section;

			return $this;
		}


		/**
		 * Set Fields.
		 *
		 * @param array $fields Fields Config.
		 * @since 1.0.0
		 */
		public function set_fields( array $fields ) {
			// Assign the fields.
			$this->fields_array = $fields;

			return $this;
		}

		/**
		 * Default field configuration
		 *
		 * @var array
		 */
		public const DEFAULT_FIELD = array(
			'id'                         => '',
			'name'                       => 'No name',
			'desc'                       => '',
			'type'                       => 'text',
			'label_for'                  => null,
			'default'                    => null,
			'std'                        => null,
			'size'                       => null,
			'options'                    => null,
			'query'                      => null,
			'callback'                   => null,
			'placeholder'                => null,
			'sanitize_callback'          => null,
			'sanitization_error_message' => null,
		);

		/**
		 * Adds a single field.
		 *
		 * @param      string $section_id      The section Id.
		 * @param      array  $field_array  The field array.
		 * @since 1.0.0
		 *
		 * @return     self    self to allow chainning
		 */
		public function add_field( $section_id, array $field_array ) {
			// Combine the defaults with user's arguements.
			$field_array['section'] = $section_id;
			if ( isset( $field_array['default'] ) ) {
				$field_array['std'] = $field_array['default'];
			}
			if ( isset( $field_array['title'] ) ) {
				$field_array['name'] = $field_array['title'];
			}
			if ( isset( $field_array['description'] ) ) {
				$field_array['desc'] = $field_array['description'];
			}
			$field_array['label_for'] = "{$section_id}[{$field_array['id']}]";

			$arg = wp_parse_args( $field_array, self::DEFAULT_FIELD );

			// Each field is an array named against its section.
			$this->fields_array[ $section_id ][] = $arg;

			return $this;
		}



		/**
		 * Initialize API.
		 *
		 * Initializes and registers the settings sections and fields.
		 * Usually this should be called at `admin_init` hook.
		 *
		 * @since 1.0.0
		 */
		public function admin_init() {
			/**
			 * Register the sections.
			 *
			 * Sections array is like this:
			 *
			 * $sections_array = array (
			 *   $section_array,
			 *   $section_array,
			 *   $section_array,
			 * );
			 *
			 * Section array is like this:
			 *
			 * $section_array = array (
			 *   'id'    => 'section_id',
			 *   'title' => 'Section Title',
			 *   'desc' => 'Section Description'
			 * );
			 *
			 * @since 1.0.0
			 */
			foreach ( $this->sections_array as $section ) {
				if ( false === get_option( $section['id'] ) ) {
					// Add a new field as section ID.
					add_option( $section['id'] );
				}

				// Deals with sections description.
				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					// Create the callback for description.
					$callback = function () use ( $section ) {
						echo '<div class="inside">' . wp_kses_post( $section['desc'] ) . '</div>';
					};
				} elseif ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				/**
				 * Add a new section to a settings page.
				 *
				 * @param string $id
				 * @param string $title
				 * @param callable $callback
				 * @param string $page | Page is same as section ID.
				 * @since 1.0.0
				 */
				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			} // foreach ended.

			/**
			 * Register settings fields.
			 *
			 * Fields array is like this:
			 *
			 * $fields_array = array (
			 *   $section => $field_array,
			 *   $section => $field_array,
			 *   $section => $field_array,
			 * );
			 *
			 *
			 * Field array is like this:
			 *
			 * $field_array = array (
			 *   'id'   => 'id',
			 *   'name' => 'Name',
			 *   'type' => 'text',
			 * );
			 *
			 * @since 1.0.0
			 */
			foreach ( $this->fields_array as $section => $field_array ) {
				foreach ( $field_array as $field ) {
					/**
					 * Add a new field to a section of a settings page.
					 *
					 * @param string   $id
					 * @param string   $title
					 * @param callable $callback
					 * @param string   $page
					 * @param string   $section = 'default'
					 * @param array    $args = array()
					 * @since 1.0.0
					 */

					// @param string     $id
					$field_id = $section . '[' . $field['id'] . ']';
					$type     = $field['type'];
					$name     = $field['name'];
					add_settings_field(
						$field_id,
						$name,
						array( $this, 'callback_' . $type ),
						$section,
						$section,
						$field
					);
				} // foreach ended.
			} // foreach ended.

			// Creates our settings in the fields table.
			foreach ( $this->sections_array as $section ) {
				$section_id = $section['id'];
				/**
				 * Registers a setting and its sanitization callback.
				 *
				 * @param string $field_group   | A settings group name.
				 * @param string $field_name    | The name of an option to sanitize and save.
				 * @param callable  $sanitize_callback = ''
				 * @since 1.0.0
				 */
				register_setting(
					$section_id,
					$section_id,
					function ( $fields ) use ( $section_id ) {
						return $this->sanitize_fields( $fields, $section_id );
					}
				);
			} // foreach ended.
		} // admin_init() ended.

		/**
		 * Default sanitization error message, highly recommanded to be overriden to support translation
		 *
		 * @param array $field_config The field configuration.
		 *
		 * @return string  The localized error message.
		 */
		public function default_sanitization_error_message( array $field_config ) {
			/* translators: 1: the field name */
			return sprintf( __( 'Please insert a valid %s' ), $field_config['type'] );
		}



		/**
		 * Gets the sanitizer function
		 *
		 * @param array $field_config The field configuration.
		 *
		 * @return <type>  The sanitizer.
		 */
		protected function get_sanitizer( array $field_config ) {
			if ( isset( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
				return $field_config['sanitize_callback'];
			}

			return function ( $field_value ) use ( $field_config ) {
				return $this->sanitize_field( $field_value, $field_config );
			};
		}

		/**
		 * Gets the error message.
		 *
		 * @param array $field_config The field configuration.
		 *
		 * @return string  The error message.
		 */
		protected function get_error_message( array $field_config ) {
			if ( isset( $field_config['sanitization_error_message'] ) ) {
				return $field_config['sanitization_error_message'];
			}
			return $this->default_sanitization_error_message( $field_config );
		}

		/**
		 * Sanitize callback for Settings API fields.
		 *
		 * @param array  $fields     The fields.
		 * @param string $section_id The section identifier.
		 *
		 * @return array  The sanitized fields.
		 */
		public function sanitize_fields( array $fields, $section_id ) {
			$old_values = get_option( $section_id, array() );
			foreach ( $fields as $field_slug => $field_value ) {

				if ( ! empty( $field_value ) ) {
					$field_config = $this->get_field_config( $section_id, $field_slug );
					if ( $field_config ) {
						// Use sanitizer from field config, if not provided, use internal sanitization.
						$sanitize_callback = $this->get_sanitizer( $field_config );

						$sanitized = call_user_func( $sanitize_callback, $field_value );
						if ( empty( $sanitized ) ) {
							add_settings_error(
								$section_id,
								$section_id . '[' . $field_slug . ']', // so we can easily access the field ( see script method sanitization errors ).
								$this->get_error_message( $field_config ),
								'error'
							);
							if ( isset( $old_values[ $field_slug ] ) ) {
								// Get the old value.
								$sanitized = $old_values[ $field_slug ];
							}
						}
						$fields[ $field_slug ] = $sanitized;

					}
				}
			}
			return $fields;
		}

		/**
		 * General Sanitize callback for a field, uses the field config to get the type of field
		 *
		 * @param string $field_value  The field value.
		 * @param array  $field_config The field configuration.
		 *
		 * @return mixed   The sanitized field
		 */
		public function sanitize_field( $field_value, $field_config ) {
			$type = $field_config['type'];
			switch ( $type ) {
				case 'checkbox':
					return 'on' === $field_value ? 'on' : 'off';
				case 'range':
				case 'number':
					if ( is_numeric( $field_value ) ) {
						return $field_value;
					}
					return 0;
				case 'textarea':
					return wp_kses_post( $field_value );
				case 'email':
					return sanitize_email( $field_value );
				case 'url':
					return esc_url_raw( $field_value );
				default:
					if ( ! empty( $field_value ) ) {
						return sanitize_text_field( $field_value );
					}
					return '';
			}
		}

		/**
		 * Gets the field configuration.
		 *
		 * @param string $section_id The section identifier.
		 * @param string $field_slug The field slug.
		 *
		 * @return array  The field configuration or null.
		 */
		public function get_field_config( $section_id, $field_slug ) {
			foreach ( $this->fields_array[ $section_id ] as $field ) {
				if ( $field['id'] === $field_slug ) {
					return $field;
				}
			}
			return null;
		}


		/**
		 * Get field description for display.
		 *
		 * @param array $args settings field args.
		 */
		public function get_field_description( array $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', wp_kses_post( $args['desc'] ) );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_text( array $args ) {
			$value      = $this->get_option( $args['id'], $args['section'], $args['std'], $args['placeholder'] );
			$size       = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$type       = isset( $args['type'] ) ? $args['type'] : 'text';
			$attributes = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? wp_sanitize_script_attributes( $args['attributes'] ) : '';

			$after = isset( $args['after'] ) ? $args['after'] : '';

			$html_safe  = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s" %7$s /> %8$s', $type, $size, $args['section'], $args['id'], esc_attr( $value ), esc_attr( $args['placeholder'] ), $attributes, $after );
			$html_safe .= $this->get_field_description( $args );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html_safe;
		}


		/**
		 * Displays a url field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_url( array $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a date field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_date( array $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays an email field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_email( array $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_number( array $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a range field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_range( array $args ) {
			$value         = esc_html( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$args['after'] = "<output>$value</output>";
			if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
				$args['attributes'] = array();
			}
			$args['attributes']['oninput'] = 'this.nextElementSibling.value = this.value';
			$this->callback_text( $args );
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_checkbox( array $args ) {
			$value           = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$section_escaped = esc_attr( $args['section'] );
			$id_escaped      = esc_attr( $args['id'] );
			$html_safe       = '<fieldset>';
			$html_safe      .= sprintf( '<label for="wposa-%1$s[%2$s]">', $section_escaped, $id_escaped );
			$html_safe      .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $section_escaped, $id_escaped );
			$html_safe      .= sprintf( '<input type="checkbox" class="checkbox" id="wposa-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $section_escaped, $id_escaped, checked( $value, 'on', false ) );
			$html_safe      .= sprintf( '%1$s</label>', $args['desc'] );
			$html_safe      .= '</fieldset>';

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a multicheckbox a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_multicheck( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

			$html_safe       = '<fieldset>';
			$section_escaped = esc_attr( $args['section'] );
			$id_escaped      = esc_attr( $args['id'] );
			foreach ( $args['options'] as $key => $label ) {
				$checked    = isset( $value[ $key ] ) ? $value[ $key ] : '0';
				$html_safe .= sprintf( '<label for="wposa-%1$s[%2$s][%3$s]">', $section_escaped, $id_escaped, $key );
				$html_safe .= sprintf( '<input type="checkbox" class="checkbox" id="wposa-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $section_escaped, $id_escaped, $key, checked( $checked, $key, false ) );
				$html_safe .= sprintf( '%1$s</label><br>', wp_kses_post( $label ) );
			}
			$html_safe .= $this->get_field_description( $args );
			$html_safe .= '</fieldset>';

         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a multicheckbox a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_radio( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

			$html_safe       = '<fieldset>';
			$section_escaped = esc_attr( $args['section'] );
			$id_escaped      = esc_attr( $args['id'] );
			foreach ( $args['options'] as $key => $label ) {
				$html_safe .= sprintf( '<label for="wposa-%1$s[%2$s][%3$s]">', $section_escaped, $id_escaped, $key );
				$html_safe .= sprintf( '<input type="radio" class="radio" id="wposa-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $section_escaped, $id_escaped, $key, checked( $value, $key, false ) );
				$html_safe .= sprintf( '%1$s</label><br>', wp_kses_post( $label ) );
			}
			$html_safe .= $this->get_field_description( $args );
			$html_safe .= '</fieldset>';

         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_select( array $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html_safe = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
			$options   = isset( $args['options'] ) ? $args['options'] : array();
			if ( isset( $args['query'] ) && isset( $args['query']['type'] ) && 'callback' === $args['query']['type'] ) {
				if ( isset( $args['query']['function'] ) && is_callable( $args['query']['function'] ) ) {
					$query_args = isset( $args['query']['args'] ) ? $args['query']['args'] : array();
					$options    = call_user_func( $args['query']['function'], $query_args );
				}
			}
			foreach ( $options as $key => $label ) {
				if ( is_array( $label ) ) {
					$desc       = isset( $label['description'] ) ? $label['description'] : '';
					$label      = isset( $label['label'] ) ? $label['label'] : $key;
					$html_safe .= sprintf( '<option title="%s" value="%s" %s>%s</option>', esc_attr( $desc ), esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
				} else {
					$html_safe .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
				}
			}
			$html_safe .= sprintf( '</select>' );
			$html_safe .= $this->get_field_description( $args );

        	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_textarea( $args ) {
			$value_escaped = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size          = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html_safe  = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"  placeholder="%5$s">%4$s</textarea>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), $value_escaped, esc_attr( $args['placeholder'] ) );
			$html_safe .= $this->get_field_description( $args );

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param  array $args settings field args.
		 */
		public function callback_html( $args ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $this->get_field_description( $args );
		}


		/**
		 * Displays a content ( generate via callback )
		 *
		 * @param  array $args settings field args.
		 */
		public function callback_content( $args ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $this->get_field_description( $args );
			if ( isset( $args['callback'] ) ) {
				$callback = $args['callback'];
				if ( isset( $callback['function'] ) && is_callable( $callback['function'] ) ) {
					$args = ( isset( $callback['args'] ) ) ? $callback['args'] : '';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The developper has the responsibility to ensure it's content is safe
					echo call_user_func( $callback['function'], $args );
				} else {
					echo 'Error wrong callback';
				}
			}
		}


		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_wysiwyg( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? esc_attr( $args['size'] ) : '500px';

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			echo '<div style="max-width: ' . $size . ';">';

			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10,
			);
			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}

			wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

			echo '</div>';

        	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $this->get_field_description( $args );
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_file( $args ) {
			$value_escaped = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size          = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$label = isset( $args['options']['button_label'] ) ?
			$args['options']['button_label'] :
			__( 'Choose File' );

			$html_safe  = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), $value_escaped );
			$html_safe .= '<input type="button" class="button wpsa-browse" value="' . esc_attr( $label ) . '" />';
			$html_safe .= $this->get_field_description( $args );

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays an image upload field with a preview
		 *
		 * @param array $args settings field args.
		 */
		public function callback_image( $args ) {
			$value_escaped = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size          = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$label = isset( $args['options']['button_label'] ) ?
			$args['options']['button_label'] :
			__( 'Choose Image' );

			$html_safe  = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, esc_attr( $args['section'] ), esc_attr( $args['id'] ), $value_escaped );
			$html_safe .= '<input type="button" class="button wpsa-browse" value="' . esc_attr( $label ) . '" />';
			$html_safe .= $this->get_field_description( $args );
			$html_safe .= '<p class="wpkit-image-preview"><img src=""/></p>';

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_password( $args ) {
			$value_escapted = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size           = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html_safe  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), $value_escapted );
			$html_safe .= $this->get_field_description( $args );

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_color( $args ) {
			$value_escaped = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'], $args['placeholder'] ) );
			$size          = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$html_safe  = sprintf( '<input type="text" class="%1$s-text color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" placeholder="%6$s" />', esc_attr( $size ), esc_attr( $args['section'] ), esc_attr( $args['id'] ), $value_escaped, esc_attr( $args['std'] ), esc_attr( $args['placeholder'] ) );
			$html_safe .= $this->get_field_description( $args );

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}


		/**
		 * Displays a separator field for a settings field
		 *
		 * @param array $args settings field args.
		 */
		public function callback_separator( $args ) {
			echo '<div class="wpkit-separator"></div>';
		}


		/**
		 * Get the value of a settings field (or meta)
		 *
		 * @param  string $option  settings field name.
		 * @param  string $section the section name this field belongs to.
		 * @param  string $default default text if it's not found.
		 * @return string
		 */
		public function get_option( $option, $section, $default = '' ) {
			if ( isset( $this->metabox ) ) {
				global $post;
				static $metas;
				if ( null === $metas ) {
					$metas = get_post_meta( $post->ID );
				}
				if ( isset( $metas[ $section . '_' . $option ] ) ) {
					return $metas[ $section . '_' . $option ][0];
				}
			} else {
				$options = get_option( $section );
				if ( isset( $options[ $option ] ) ) {
					return $options[ $option ];
				}
			}

			return $default;
		}

		/**
		 * Add submenu page to the Settings main menu.
		 *
		 * @author Ahmad Awais
		 * @since  [version]
		 */
		public function admin_menu() {
			add_options_page(
				'WP Settings Kit',
				'WP Settings Kit',
				'manage_options',
				'wp_settings_kit',
				array( $this, 'plugin_page' )
			);
		}


		/**
		 * Plugin page callback
		 */
		public function plugin_page() {
			echo '<div class="wrap">';
			echo '<h1>WP Settings Kit <span style="font-size:50%;">v' . esc_attr( WP_SKIT_VERSION ) . '</span></h1>';
			$this->show_navigation();
			$this->show_forms();
			echo '</div>';
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 */
		public function show_navigation() {
			$html_safe = '<nav class="nav-tab-wrapper">';

			foreach ( $this->sections_array as $tab ) {
				$desc       = isset( $tab['desc'] ) ? 'data-description="' . esc_attr( $tab['desc'] ) . '"' : '';
				$html_safe .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab" %3$s>%2$s</a>', esc_attr( $tab['id'] ), esc_html( $tab['title'] ), $desc );
			}

			$html_safe .= '</nav>';

         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped and safe html output
			echo $html_safe;
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		public function show_forms() {
			?>
			<div class="metabox-holder">
			<?php foreach ( $this->sections_array as $form ) { ?>
					<div id="<?php echo esc_attr( $form['id'] ); ?>" class="group section_block" >
						<main>
							<form method="post" action="options.php">
				<?php
				do_action( 'wsa_form_top_' . $form['id'], $form );
				settings_errors( $form['id'] );
				settings_fields( $form['id'] );
				do_settings_sections( $form['id'] );
				do_action( 'wsa_form_bottom_' . $form['id'], $form );
				?>
								<div style="padding-left: 10px">
				<?php submit_button( null, 'primary', 'submit_' . $form['id'] ); ?>
								</div>
							</form>
				<?php do_action( 'wsa_after_form_' . $form['id'], $form ); ?>
						</main>
				<?php if ( $form['side_panel'] ) { ?> 
						<aside class="card">
					<?php
					if ( is_callable( $form['side_panel'] ) ) {
						call_user_func( $form['side_panel'] );
					} else {
						echo wp_kses_post( $form['side_panel'] );
					}
					?>
						</aside>
				<?php } ?>
					</div>
			<?php } ?>
			</div>
			<?php
			$this->script();
		}

		/**
		 * Adds a metabox.
		 *
		 * @param string $post_type The post type.
		 */
		public function add_metabox( $post_type ) {
			if ( in_array( $post_type, $this->metabox['post_types'], true ) ) {
				$this->init_options();
				add_meta_box(
					$this->metabox['id'],
					$this->metabox['title'],
					array( $this, 'display_metas' ),
					$this->metabox['post_types'],
					$this->metabox['context'],
					$this->metabox['priority']
				);
			}
		}

		/**
		 * Display the metabox
		 */
		public function display_metas() {
			$this->show_navigation();
			$this->show_forms_metabox();
		}



		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		public function show_forms_metabox() {
			?>
			<div class="metabox-holder">
			<?php
			wp_nonce_field( 'wp_kit_metabox', 'wp_kit_metabox_nonce' );
			foreach ( $this->sections_array as $section ) {
				$section_id = $section['id'];
				echo "<div id='" . esc_attr( $section_id ) . "' class='group' ><h3>" . esc_html( $section['title'] ) . "</h3>\n";
				echo '<table class="form-table" role="presentation">';
				if ( $this->should_show( $section ) ) {
					$fields = $this->fields_array[ $section_id ];
					foreach ( $fields as $field ) {
						if ( $this->should_show( $field ) ) {
							$label_for = $field['label_for'];
							$name      = $field['name'];
							$type      = $field['type'];
							$callback  = 'callback_' . $type;
							echo '<tr>';
							if ( $label_for ) {
								echo '<th scope="row"><label for="' . esc_attr( $label_for ) . '">' . esc_html( $name ) . '</label></th>';
							} else {
								echo '<th scope="row">' . esc_html( $name ) . '</th>';
							}
							echo '<td>';
							$this->$callback( $field );
							echo '</td>';
							echo '</tr>';
						}
					}
				}
				echo '</table></div>';
			}
			?>
			</div>
			<?php
			$this->script();
		}

		/**
		 * Saves the metabox hook
		 *
		 * @param int   $post_id The post ID.
		 * @param mixed $post    The post.
		 */
		public function save_metabox( $post_id, $post ) {
			if ( ! isset( $_POST['wp_kit_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wp_kit_metabox_nonce'] ) ), 'wp_kit_metabox' ) ) {
				return;
			}
			if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $this->metabox['post_types'], true ) ) {
				$this->init_options();
				foreach ( $this->sections_array as $section ) {
					$section_id = $section['id'];

					if ( isset( $_POST[ $section_id ] ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every field will be sanitized before saving with the proper sanitization method.
						$section_data = wp_unslash( $_POST[ $section_id ] );
						foreach ( $section_data as $field_name => $field_value ) {
							$field_config = $this->get_field_config( $section_id, $field_name );
							if ( $field_config ) {
								$sanitizer = $this->get_sanitizer( $field_config );
								if ( $sanitizer ) {
									$field_value = call_user_func( $sanitizer, wp_unslash( $field_value ) );
								}
								update_post_meta( $post_id, $section_id . '_' . $field_name, $field_value );
							}
						}
					}
				}
			}
		}

		/**
		 * Tabbable JavaScript codes & Initiate Color Picker
		 *
		 * This code uses localstorage for displaying active tabs.
		 */
		public function script() {
			?>
			<script>
				jQuery( document ).ready( function( $ ) {

				//Initiate Color Picker.
				$('.color-picker').iris();

				// Switches option sections
				$( '.group' ).hide();
				var activetab = '';
				if ( 'undefined' != typeof localStorage ) {
					activetab = localStorage.getItem( 'activetab' );
				}
				if ( '' != activetab && $( activetab ).length ) {
					$( activetab ).fadeIn();
				} else {
					$( '.group:first' ).fadeIn();
				}
				$( '.group .collapsed' ).each( function() {
					$( this )
						.find( 'input:checked' )
						.parent()
						.parent()
						.parent()
						.nextAll()
						.each( function() {
							if ( $( this ).hasClass( 'last' ) ) {
								$( this ).removeClass( 'hidden' );
								return false;
							}
							$( this )
								.filter( '.hidden' )
								.removeClass( 'hidden' );
						});
				});

				var $nav = $( 'nav.nav-tab-wrapper');

				if ( '' != activetab && $( activetab + '-tab' ).length ) {
					$( activetab + '-tab' ).addClass( 'nav-tab-active' );
				} else {
					$( 'a:first' , $nav ).addClass( 'nav-tab-active' );
				}
				var $navLinks = $( 'a' , $nav );
				$navLinks.click( function( evt ) {
					$navLinks.removeClass( 'nav-tab-active' );
					$( this )
						.addClass( 'nav-tab-active' )
						.blur();
					var clicked_group = $( this ).attr( 'href' );
					if ( 'undefined' != typeof localStorage ) {
						localStorage.setItem( 'activetab', $( this ).attr( 'href' ) );
					}
					$( '.group' ).hide();
					$( clicked_group ).fadeIn();
					evt.preventDefault();
				});

				$( '.wpsa-browse' ).on( 'click', function( event ) {
					event.preventDefault();

					var self = $( this );

					// Create the media frame.
					var file_frame = ( wp.media.frames.file_frame = wp.media({
						title: self.data( 'uploader_title' ),
						button: {
							text: self.data( 'uploader_button_text' )
						},
						multiple: false
					}) );

					file_frame.on( 'select', function() {
						attachment = file_frame
							.state()
							.get( 'selection' )
							.first()
							.toJSON();

						self
							.prev( '.wpsa-url' )
							.val( attachment.url )
							.change();
					});

					// Finally, open the modal
					file_frame.open();
				});

				$( 'input.wpsa-url' )
					.on( 'change keyup paste input', function() {
						var self = $( this );
						self
							.next()
							.parent()
							.children( '.wpkit-image-preview' )
							.children( 'img' )
							.attr( 'src', self.val() );
					})
					.change();

				// Sanitization errors
				$('div.settings-error').each(function(){
					// Get the field mame and make sure to escape brackets
					var field_id = $(this).attr('id')
						.replace('setting-error-', '')
						.replace('[', "\\[") 
						.replace(']', "\\]")
					var $field = $("[name="+field_id+"]");
					$field.addClass('sanitization-error');
					$field.one('change',function(){
						$field.removeClass('sanitization-error');
					});
				});
			});
			</script>

			<style type="text/css">
				/** WordPress 3.8 Fix **/
				.form-table th {
					padding: 20px 10px;
				}

				#wpbody-content .metabox-holder {
					padding-top: 5px;
				}

				.wpkit-image-preview img {
					height: auto;
					max-width: 70px;
				}

				.wpkit-separator {
					background: #ccc;
					border: 0;
					color: #ccc;
					height: 1px;
					position: absolute;
					left: 0;
					width: 99%;
				}
				.group .form-table input.color-picker {
					max-width: 100px;
				}

				/* Pretty much like :focus with red ( like notice-error )*/
				.form-table input.sanitization-error, .form-table select.sanitization-error {
					border-color: #d63638;
					box-shadow: 0 0 0 1px #d63638;
					outline: 2px solid transparent;
				}

				tr output{ 
					font-weight: bold;
					vertical-align: top;
					margin-left: 1em; 
				}

				/* Tooltip container */
				[data-description] {
					position: relative;
					cursor: pointer;
				}

				[data-description]::after {
					background-color: #000; /* Darker grey background */
					color: #ffffff; /* White text */
					padding: 5px; /* Generous padding for a spacious feel */
					border-radius: 2px; /* Smooth, rounded corners */
					font-family: 'Segoe UI', 'Arial', sans-serif; /* A more modern font stack */
					font-size: 12px; /* Clear, readable font size */
					box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25); /* A moderate shadow for depth */
					height: fit-content;
					max-width: 200px;
					position: absolute;
					text-align: center;
					bottom: 0px;
					left: 50%;
					content: attr(data-description);
					transform: translate(-50%, 110%) scale(0);
					transform-origin: top;
					transition: 0.2s;
					word-wrap: break-word;
					white-space: pre-wrap; /* This is the key for multiline tooltips */
					width: max-content;
					z-index: 9999;
				}

				/* Show the title when hovering */
				[data-description]:hover::after {
					display: block;
					transform: translate(-50%, 110%) scale(1);
				}

				.section_block  {
					display:flex;
				}
				.section_block main {
					flex:3;
				}

				.section_block aside {
					min-width: 350px;
					position: sticky;
					align-self: start;
				}
			</style>
			<?php
		}
	}//end class

endif;
