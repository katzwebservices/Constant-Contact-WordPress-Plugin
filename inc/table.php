<?php
/**
 * Table Helper class file
 *
 * Builds a HTML table of results from data
 *
 * Column headings are taken from the array keys or object properties
 *
 * @package    Tina-MVC
 * @subpackage Core
 * @param   string $tablename a unique name for the form
 */
class CTCT_Admin_Table {
    /**
     * Class variables should only be accessed using setters and getters. Direct acces is not supported.
     */
    private $html,
        $id,
        $data,
        $name,
        $do_not_esc_th,
        $do_not_esc_td;

    /**
     * Constructor
     *
     * Sets up the table
     *
     * @param   string $tablename  unique name for the table e.g. 'users-list'
     */
    function __construct( $tablename=false ) {

        $this->set_name( $tablename );
        $this->set_id( $tablename );

        $this->do_not_esc_th = FALSE;

    }

    /**
     * Set the data you wish to display
     *
     * @param mixed $data An array or object of data you want to display
     * @return object table helper
     */
    public function set_data( $data=FALSE ) {

        if( ! is_array( $data ) AND ! is_object( $data ) ) {
            error( '$data must be an array or an object' );
        }

        $this->data = $data;

        return $this;

    }

    /**
     * Sets the name (after sanitising)
     *
     * @param string $name
     * @return object table helper
     */
    public function set_name( $name='' ) {

        if( ! $name ) {
            error('$name parameter is required.');
        }
        $this->name = str_replace( ' ', '' , $name );

        return $this;

    }

    /**
     * Getter
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Sets the id for the table
     *
     * @param string $id he CSS id for the table
     * @return object table helper
     */
    public function set_id( $id=FALSE ) {

        if( ! $id ) {
            error('$id parameter is required.');
        }
        $this->id = str_replace( ' ', '' , $id );

        return $this;

    }

    /**
     * Alias to $this->render()
     * @return string
     * @see $this->get_html()
     */
    public function render() {
        return $this->get_html();
    }

    /**
     * Builds the table and returns HTML ready to echo to the browser
     *
     * @return string The HTML table
     */
    public function get_html() {

        if( ! empty( $this->data ) ) {

            $this->html = "<table id=\"".$this->get_id()."\" class=\"tina_mvc_table\">";

            $this->html .= "<thead>";

            foreach( $this->data AS & $row ) {
                $this->html .= "<tr>";
                foreach( $row AS $f_name => & $f_value ) {

                    if( $this->do_not_esc_th ) {
                        $this->html .= '<th>'.$f_name.'</th>';
                    }
                    else {
                        $this->html .= '<th>'.$f_name.'</th>';
                    }

                }
                $this->html .= "</tr>";
                break;
            }

            $this->html .= "</thead>";

            reset( $this->data );

            $this->html .= "<tbody>";
            $alt = '';
            foreach( $this->data as &$row ) {
                if($alt == 'alt') { $alt = '';} else { $alt = 'alt'; }
                $this->html .= "<tr class='{$alt}'>";
                foreach( $row AS $f_name => & $f_value ) {

                    if( $this->do_not_esc_td ) {
                        $this->html .= '<td>'.$f_value.'</td>';
                    }
                    else {
                        $this->html .= '<td>'.$f_value.'</td>';
                    }

                }
                $this->html .= "</tr>";
            }
            $this->html .= "</tbody>";

            $this->html .= '</table>';

            return $this->html;

        }
        else {

            return '';

        }

    }

    /**
     * Prevent escaping of the table headings
     *
     * These are taken from the array keys or object properties of
     * the data you have entered. You might want to pass HTML in which case you
     * do not want the text escaped.
     *
     * @param boolean $tf
     * @return object table helper
     */
    public function do_not_esc_th( $tf=FALSE ) {

        $this->do_not_esc_th = (bool) $tf;

        return $this;

    }

    /**
     * Prevent escaping of the table cells
     *
     * These are taken from the array keys or object properties of
     * the data you have entered. You might want to pass HTML in which case you
     * do not want the text escaped.
     *
     * @param boolean $tf
     * @return object table helper
     */
    public function do_not_esc_td( $tf=FALSE ) {

        $this->do_not_esc_td = (bool) $tf;

        return $this;

    }
}