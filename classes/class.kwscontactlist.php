<?php
/**
 * @package CTCT
 * @version 3.0
 */

use Ctct\Components\Contacts\ContactList;

class KWSContactList extends ContactList {

	private static $read_only = array( 'contact_count', 'id' );

	public function __construct( $List = null ) {

		if ( is_array( $List ) ) {
			$List = $this->prepare( $List, true );
		}

		if ( ! empty( $List ) && ( is_array( $List ) || $List instanceof ContactList ) ) {
			foreach ( $List as $k => &$v ) {
				$this->{$k} = $v;
			}
		} elseif( ! is_null( $List ) ) {
			parent::__construct( $List );
		}

		return $this;
	}

	/**
	 * Factory method to create a Contact object from an array
	 *
	 * @param array $props - Associative array of initial properties to set
	 *
	 * @return KWSContactList
	 */
	public static function create( array $props ) {
		$List = new KWSContactList( $props );

		return $List;
	}

	public function update( array $new_contact_array ) {

		$existing_contact = clone( $this );

		$new_contact = new KWSContactList( $new_contact_array, true );

		unset( $new_contact->id, $new_contact->status, $new_contact->source, $new_contact->source_details );

		foreach ( $new_contact as $k => $v ) {
			$existing_contact->{$k} = $v;
		}

		return $existing_contact;
	}

	private function prepare( array $list_array, $add = false ) {

		$defaults = array(
			'id'            => NULL,
			'name'          => NULL,
			'status'        => NULL,
			'contact_count' => NULL,
		);

		$List = wp_parse_args( $list_array, $defaults );

		return $List;
	}

	/**
	 * Get the label for a field based on the key
	 *
	 * @param  string $key Key, like Addr2 or FirstName
	 *
	 * @return [type]            [description]
	 */
	function getLabel( $key ) {

		return ctct_get_label_from_field_id( $key );
	}

	function set( $key, $value ) {
		switch ( $key ) {
			case 'name':
				$this->{$key} = $value;
				break;
			case 'status':
				// Only these two values are allowed.
				if ( $value === 'ACTIVE' || $value === 'HIDDEN' ) {
					$this->{$key} = $value;
				}
				break;
			default:
				return false;
				break;
		}

		return true;
	}

	/**
	 * Convert an array of List objects into HTML output
	 *
	 * @param  array $passed_items List array
	 * @param  array $atts Settings; `fill`, `selected`, `format`; `format` should use replacement tags with the tag being the name of the var of the List object you want to replace. For example, `%%name%% (%%contact_count%% Contacts)` will return an item with the content "List Name (140 Contacts)"
	 *
	 * `showhidden` If true, will exclude lists that have a status of "hidden" in http://dotcms.constantcontact.com/docs/contact-list-api/contactlist-collection.html
	 *
	 * @return [type]        [description]
	 */
	static function outputHTML( $passed_items = array(), $atts = array() ) {

		$settings = wp_parse_args( $atts, array(
			'type'       => 'checkboxes',
			'fill'       => true, // Fill data into lists
			'format'     => '<span>%%name%%</span>', // Choose HTML format for each item
			'id_attr'    => 'ctct-%%id%%', // Pass a widget instance
			'name_attr'  => 'lists',
			'checked'    => array(), // If as select, what's active?
			'include'    => array(),
			'showhidden' => true,
			'class'      => '',
			'blank'      => '',
		) );

		extract( $settings );

		$items = array();

		if ( $passed_items === 'all' ) {
			$items = WP_CTCT::getInstance()->cc->getAllLists();
		} elseif ( ! empty( $passed_items ) && is_array( $passed_items ) ) {
			foreach ( $passed_items as $item ) {
				global $list_id;

				if ( $fill ) {
					$list_id = is_object( $item ) ? $item->id : $item;

					$list_id = esc_attr( $list_id );

					$item = WP_CTCT::getInstance()->cc->getList( CTCT_ACCESS_TOKEN, $list_id );
				}
				$items[] = $item;
			}
		}

		// There should always be at least one list.
		if ( empty( $items ) || $items instanceof \Ctct\Exceptions\CtctException ) {
			return sprintf( __('There was an error fetching lists. Please <a href="%s">refresh your lists</a> and try again.', 'constant-contact-api'), esc_url( admin_url( 'admin.php?page=constant-contact-lists&refresh=lists' ) ) );
		}

		$before = $before_item = $after_item = $after = $format = $id_attr = '';

		switch ( $type ) {
			case 'hidden':
				$format = '<input type="hidden" value="%%id%%" name="%%name_attr%%[]" />';
				break;
			case 'ul':
				$before      = '<ul class="ul-square">';
				$before_item = '<li>';
				$after_item  = '</li>';
				$after       = '</ul>';
				break;
			case 'dropdown':
			case 'select':
			case 'multiselect':
				$multiple = '';

				// Even though the multiselect option is no longer available
				// in the settings, keep this around for backward compatibility.
				// And if crazy people want multi-selects
				if ( $type === 'select' || $type === 'multiselect' ) {
					$multiple = ' multiple="multiple"';
				}

				$before      = '<select name="%%name_attr%%"' . $multiple . ' class="ctct-lists">';
				$before_item = '<option value="%%id%%">';
				$after_item  = '</option>';
				$after       = '</select>';

				// Allow passing a blank item title
				if ( ! empty( $blank ) ) {
					$before .= '<option value="">' . esc_html( $blank ) . '</option>';
				}

				break;
			case 'checkbox':
			case 'checkboxes':
				$before      = '<ul class="ctct-lists ctct-checkboxes ' . esc_attr( $class ) . '">';
				$before_item = '<li><label><input type="checkbox" id="%%id_attr%%" value="%%id%%" name="%%name_attr%%[]" %%checked%% /> ';
				$after_item  = '</label></li>';
				$after       = '</ul>';
				break;
		}

		$output = $before;

		$items_output = '';
		foreach ( $items as &$item ) {

			// Error was thrown
			if( is_a( $item, 'Ctct\Exceptions\CtctException' ) ) {
				continue;
			}

			// If include was specified, then we need to skip lists not included
			if ( ! empty( $include ) && ! in_array( $item->id, $include ) && ! ( $item->status === 'HIDDEN' && ! $showhidden ) ) {
				continue;
			}

			$item = new KWSContactList( $item );

			$item_content = ( ! empty( $format ) || is_null( $format ) ) ? $format : esc_attr( $item->name );

			$tmp_output = $before_item . $item_content . $after_item . "\n";

			$tmp_output = str_replace( '%%id_attr%%', $id_attr, $tmp_output );
			$tmp_output = str_replace( '%%id%%', sanitize_title( $item->get( 'id' ) ), $tmp_output );
			$tmp_output = str_replace( '%%name%%', $item->get( 'name', false ), $tmp_output );
			$tmp_output = str_replace( '%%status%%', $item->get( 'status', false ), $tmp_output );
			$tmp_output = str_replace( '%%contact_count%%', $item->get( 'contact_count', true ), $tmp_output );

			$tmp_output = str_replace( '%%checked%%', checked( ( in_array( $item->get( 'id' ), (array) $checked ) || ( is_null( $checked ) && $item->get( 'status' ) === 'ACTIVE' ) ), true, false ), $tmp_output );

			$items_output .= $tmp_output;
		}

		$output .= $items_output;

		$output .= $after;

		$output = str_replace( '%%name_attr%%', $name_attr, $output );
		$output = str_replace( '%%id_attr%%', $id_attr, $output );

		return $output;
	}

	private function is_editable( $key ) {
		return ! in_array( $key, $this::$read_only );
	}

	function get( $key, $format = false ) {
		switch ( $key ) {
			default:
				if ( isset( $this->{$key} ) ) {
					return ( $format && $this->is_editable( $key ) ) ? '<span class="editable" data-name="' . $key . '" data-id="' . $this->get( 'id' ) . '">' . esc_html( $this->{$key} ) . '</span>' : $this->{$key};
				} else {
					return '';
				}
				break;
		}
	}
}