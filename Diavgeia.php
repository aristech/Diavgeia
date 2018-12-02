<?php
/**
 * @package AristechDiavgeia
 */
/**
 * Plugin Name: Diavgeia
 * Description : Custom Diavgeia Shortcodes
 * Author: Aristech
 * Version: 1.0.0
 *License:     GPL2
**/
if ( ! defined('ABSPATH') ) {
    die;
}



class Diavgeia {

    public $plugin;


    function __construct() {

        //$this->update();
        $this->plugin               = plugin_basename( __FILE__ );
        $this->template             = plugin_dir_path( __FILE__ ) . '/templates/';
        $this->date                 = new DateTime();
        $this->aristech_options     = get_option("aristech_options", "");
        $this->title                = get_option( 'aristech_title', '' );
        $this->max                  = get_option( 'aristech_max_posts', '5' );
        $this->radio                = get_option( 'aristech_radio', 'large' );
        $this->prefix               = 'aristech_';
        $this->opendata              = 'https://diavgeia.gov.gr/luminapi/api/search/export?q=';
        $this->custom_meta_fields   = array(
                                        array(
                                            'label'=> 'Επιλέξετε',
                                            'desc'  => 'Φορέα, Θεματική, Είδος',
                                            'id'    => $this->prefix.'select',
                                            'type'  => 'select',
                                            'options' => array (
                                                'one' => array (
                                                    'label' => 'ID Φορέα',
                                                    'value' => 'organizationUid',

                                                ),
                                                'two' => array (
                                                    'label' => 'Θεματική κατηγορία',
                                                    'value' => 'thematicCategory'
                                                ),
                                                'three' => array (
                                                    'label' => 'Είδος',
                                                    'value' => 'decisionType'
                                                )
                                            )
                                        ),
                                        array(
                                            'label'=> 'Οργανωτικές μονάδες',
                                            'desc'  => 'Πρέπει να έχετε επιλέξει ID Φορέα <br> και να έχετε καθορίσει έναν φορέα',
                                            'id'    => $this->prefix.'text',
                                            'type'  => 'text'
                                        ),
                                        array(
                                            'label'=> 'Ημερομηνία έκδοσης (από)',
                                            'desc'  => 'default=όλες',
                                            'id'    => $this->prefix.'datepick1',
                                            'type'  => 'text'
                                        ),
                                        array(
                                            'label'=> 'Ημερομηνία έκδοσης (έως)',
                                            'desc'  => 'default=όλες',
                                            'id'    => $this->prefix.'datepick2',
                                            'type'  => 'text'
                                        ),
                                        array(
                                            'label'=> 'Ημερομηνία τελευταίας τροποποίησης (έως)',
                                            'desc'  => 'default=όλες',
                                            'id'    => $this->prefix.'datepick3',
                                            'type'  => 'text'
                                        ),
                                        array(
                                            'label'=> 'Ημερομηνία τελευταίας τροποποίησης (από)',
                                            'desc'  => 'default=όλες',
                                            'id'    => $this->prefix.'datepick4',
                                            'type'  => 'text'
                                        ),
                                        array(
                                            'label'=> 'Aναζήτηση',
                                            'desc'  => 'Εισάγετε κείμενο για αναζήτηση αποφάσεων χωρισμένα με κόμμα <code>,</code>',
                                            'id'    => $this->prefix.'textarea',
                                            'type'  => 'textarea'
                                        ),
                                        array(
                                            'label'=> 'Extra Parameters',
                                            'desc'  => 'πχ: <code>decisionType:"ΛΟΙΠΕΣ%20ΑΤΟΜΙΚΕΣ%20ΔΙΟΙΚΗΤΙΚΕΣ%20ΠΡΑΞΕΙΣ"</code> <br> πχ: <code>thematicCategory:"ΑΠΑΣΧΟΛΗΣΗ%20ΚΑΙ%20ΕΡΓΑΣΙΑ"</code> <br> πχ custom query: <code>q:"Προγραμματισμού%20οργάνωση"</code>',
                                            'id'    => $this->prefix.'extra',
                                            'type'  => 'textarea'
                                        )

                                    );



    }

    function register() {

        add_action( 'admin_enqueue_scripts', array($this, 'enqueueAdmin'));
        add_action( 'wp_enqueue_scripts', array($this, 'enqueueWp'));
        add_filter( "plugin_action_links_$this->plugin", array($this, 'settings_link'));
        add_action( 'init', array($this, 'user_cpt'));
        add_shortcode( 'diavgeia', array($this ,'aristech_diavgeia'));
        add_action( 'add_meta_boxes', array($this,'user_add_metabox' ));
        add_action('save_post', array($this, 'save_custom_meta' ));
        add_filter( 'postbox_classes_diavgeia_aristech_datepick1', array($this,'add_metabox_classes' ));



    }

    public function settings_link($links){

        $settings_link = '<a href="admin.php?page=aristech_diavgeia">Settings</a>';

        array_push($links, $settings_link);
        return $links;

    }

    function add_metabox_classes($classes) {
        array_push($classes,'datepick');
        return $classes;
      }



    function enqueueAdmin() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_register_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css' );
        wp_enqueue_style('bootstrap');
        wp_register_style( 'databootstrap', 'https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css' );
        wp_enqueue_style('databootstrap');


        wp_register_style( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );


        wp_register_script( 'datatables', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('datatables');

        wp_register_script( 'bootdatatables', 'https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('bootdatatables');

        wp_register_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('popper');

        wp_register_script( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('bootstrap');

        wp_enqueue_script( 'adm_script', plugin_dir_url( __FILE__ ) . 'js/aristech_admin_script.js', array( 'jquery' ), false, true );
    }

    public function enqueueWp() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_register_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css' );
        wp_enqueue_style('bootstrap');
        wp_register_style( 'databootstrap', 'https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css' );
        wp_enqueue_style('databootstrap');

        //wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . '/main.css', array(), '1.0.0', 'all' );

        wp_register_style( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );


        wp_register_script( 'datatables', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('datatables');

        wp_register_script( 'bootdatatables', 'https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('bootdatatables');

        wp_register_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('popper');

        wp_register_script( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script('bootstrap');

        wp_enqueue_script( 'wp_script', plugin_dir_url( __FILE__ ) . 'js/aristech_wp_script.js', array( 'jquery' ), false, true );




    }




    public function user_cpt() {

        $labels = array(
            'name'               => 'Diavgeia',
            'singular_name'      => 'Diavgeia',
            'add_new'            => 'Add Diavgeia',
            'all_items'          => 'All Diavgeias',
            'add_new_item'       => 'Add Diavgeia',
            'edit_item'          => 'Edit Diavgeia',
            'new_item'           => 'New Diavgeia',
            'view_item'          => 'View Diavgeia',
            'search_item'        => 'Search Diavgeia',
            'not_found'          => 'No items found',
            'not_found_in_trash' => 'No items found in trash',
            'parent_item_colon'  => 'Parent Item'
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'publicly_queryable' => false,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array(
                'title',
                'metaboxes',

            ),
            // 'taxonomies' => array('category', 'post_tag'),
            'menu_icon' => 'dashicons-media-spreadsheet',
            'menu_position' => 5,
            'exclude_from_search' => false,
            'show_in_rest'       => false,

        );
        register_post_type( 'diavgeia' ,$args);

    }
    function user_add_metabox()
    {

        add_meta_box(
            'custom_meta_box', // $id
            'Diavgeia Shortcode settings', // $title
            array($this , 'show_custom_meta_box'), // $callback
            'diavgeia', // $page
            'normal', // $context
            'high'); // $priority
    }

    function show_custom_meta_box() {

        global $post;
        // Use nonce for verification
        echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

            // Begin the field table and loop
            echo '<code>[diavgeia id='.$post->ID.']</code>';
            echo '<table class="form-table">';

            foreach ($this->custom_meta_fields as $field) {
                // get value of this field if it exists for this post
                $meta = get_post_meta($post->ID, $field['id'], true);
                // begin a table row with
                echo '<tr>
                        <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                        <td>';
                        switch($field['type']) {
                            // case items will go here
                            // select
                            case 'select':
                            echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                            foreach ($field['options'] as $option) {
                                echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                            }
                            echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // text
                            case 'text':
                            echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;

                            // Datepicker1
                            case 'datepicker1':
                            echo '<input type="text" class="custom_date" name="'.$field['id'].'" id="'.$field['id'].'"  value="'.$meta.'" size="30" />
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // Datepicker2
                            case 'datepicker1':
                            echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" class="datepick" value="'.$meta.'" size="30" />
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // Datepicker3
                            case 'datepicker1':
                            echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" class="datepick" value="'.$meta.'" size="30" />
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // Datepicker4
                            case 'datepicker1':
                            echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" class="datepick" value="'.$meta.'" size="30" />
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // textarea
                            case 'textarea':
                            echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="2">'.$meta.'</textarea>
                                <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                            // checkbox
                            case 'extra':
                            echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
                                <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                            break;

                        } //end switch
                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
            echo '<h2>Preview DATA</h2>';

            $txt = '<div class="container">

            <table id="myTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Ημ/νια Πράξης</th>
                        <th scope="col">Ημ/νια Ανάρτησης</th>
                        <th scope="col">Θεμα</th>
                        <th scope="col">ΑΔΑ</th>
                        <th scope="col">Τύπος Πράξης</th>
                        <th scope="col">Αρ. Πρωτοκόλου</th>
                        <th scope="col">URL Εγγράφου</th>
                    </tr>
                </thead>
                <tbody>';
                function multiPart($items) {
                    $textItems = explode(',', $items);
                    $readyItems = '';

                    foreach ($textItems as $item => $v) {

                        end($textItems);
                        if ($item === key($textItems)) {

                        $readyItems .= '"'.$v .'"';

                        } else {
                            $readyItems .= '"'.$v .'",';
                        }
                    }
                    return $readyItems;
                }
                $date = new DateTime();

                $selectBox = get_post_meta($post->ID, "aristech_select", true);
                $textBox = get_post_meta($post->ID, "aristech_text", true);
                $textBoxReady = $textBox ? '&fq=unitUid:[' . multiPart($textBox) . ']' : '';
                $datepick1 = get_post_meta($post->ID, "aristech_datepick1", true) ? '&fq=submissionTimestamp:[DT(' . get_post_meta($post->ID, "aristech_datepick1", true) . 'T00:00:00)%20TO%20DT(' : '';
                $datepick2 = get_post_meta($post->ID, "aristech_datepick2", true) ? get_post_meta($post->ID, "aristech_datepick2", true) . 'T23:59:59)]' : $date->format('Y-m-d') .'T23:59:59)]';
                $datepick3 = get_post_meta($post->ID, "aristech_datepick3", true) ? '&fq=issueDate:[DT(' . get_post_meta($post->ID, "aristech_datepick3", true) . 'T00:00:00)%20TO%20DT(' : '';
                $datepick4 = get_post_meta($post->ID, "aristech_datepick4", true) ? get_post_meta($post->ID, "aristech_datepick4", true) . 'T23:59:59)]' : $date->format('Y-m-d') .'T23:59:59)]';
                $extra = get_post_meta($post->ID, "aristech_extra", true) ? get_post_meta($post->ID, "aristech_extra", true) . '&fq=' : '';
                $textArea = get_post_meta($post->ID, 'aristech_textarea', true);




                $dateSubmission = $datepick1 != '' ? $datepick1 . $datepick2 : '';
                $dateIssue = $datepick3 != '' ? $datepick3 . $datepick4 : '';

                $searchUrl = $this->opendata . $extra . $selectBox . ':['.multiPart($textArea).']'. $textBoxReady . $dateSubmission . $dateIssue . '&wt=json' ;
                $json = file_get_contents($searchUrl);
                $mobj = json_decode($json);
                echo $this->opendata . $extra . $selectBox . ':['.multiPart($textArea).']'. $textBoxReady . $dateSubmission . $dateIssue . '&wt=json' ;
                $myobj = $mobj->decisionResultList;
                foreach ($myobj as $key => &$value) {


                        $txt .= '<tr>';
                        $txt .= '<td scope="row">'. $value->issueDate.'</td>';
                        $txt .= '<td scope="row">'. $value->submissionTimestamp .'</td>';
                        $txt .= '<td scope="row">'. $value->subject .'</td>';
                        $txt .= '<th scope="row">'. $value->ada .'</th>';
                        $txt .= '<td scope="row">'. $value->decisionTypeLabel .'</td>';
                        $txt .= '<td scope="row">'. $value->protocolNumber  .'</td>';
                        $txt .= '<td scope="row"><a href="'. $value->documentUrl  .'" target="_self"><button type="button" class="btn btn-small btn-outline-secondary">λήψη</button></a><br><a href="'. $value->documentUrl  .'?inline=true" target="_blank"><button type="button" class="btn btn-small btn-outline-secondary">Προβολή αρχείου</button></a></td>';
                        $txt .= '</tr>';
                }


                $txt .= '</tbody>
                </table>
        </div>';
echo $txt;
        }



        // Save the Data
    function save_custom_meta($post_id) {

        //global $custom_meta_fields;

        // verify nonce
        if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))
            return $post_id;
        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        // check permissions
        if ('page' == $_POST['diavgeia']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post_id;
        }

        // loop through fields and save the data
        foreach ($this->custom_meta_fields as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];
            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        } // end foreach
    }


    function aristech_diavgeia($atts) {
        $a = shortcode_atts( array(
            'id' => 6
         ), $atts );
         $txt = '<div class="container" style="overflow-x:auto;">
                    <table id="divgeiaTable" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Ημ/νια Πράξης</th>
                                <th scope="col">Ημ/νια Ανάρτησης</th>
                                <th scope="col">Θεμα</th>
                                <th scope="col">ΑΔΑ</th>
                                <th scope="col">Τύπος Πράξης</th>
                                <th scope="col">Αρ. Πρωτοκόλου</th>
                                <th scope="col">URL Εγγράφου</th>
                            </tr>
                        </thead>
                        <tbody>';
                        function multiPart($items) {
                            $textItems = explode(',', $items);
                            $readyItems = '';

                            foreach ($textItems as $item => $v) {

                                end($textItems);
                                if ($item === key($textItems)) {

                                $readyItems .= '"'.$v .'"';

                                } else {
                                    $readyItems .= '"'.$v .'",';
                                }
                            }
                            return $readyItems;
                        }
                        $date = new DateTime();

                        $selectBox = get_post_meta($a['id'], "aristech_select", true);
                        $textBox = get_post_meta($a['id'], "aristech_text", true);
                        $textBoxReady = $textBox ? '&fq=unitUid:[' . multiPart($textBox) . ']' : '';
                        $datepick1 = get_post_meta($a['id'], "aristech_datepick1", true) ? '&fq=submissionTimestamp:[DT(' . get_post_meta($a['id'], "aristech_datepick1", true) . 'T00:00:00)%20TO%20DT(' : '';
                        $datepick2 = get_post_meta($a['id'], "aristech_datepick2", true) ? get_post_meta($a['id'], "aristech_datepick2", true) . 'T23:59:59)]' : $date->format('Y-m-d') .'T23:59:59)]';
                        $datepick3 = get_post_meta($a['id'], "aristech_datepick3", true) ? '&fq=issueDate:[DT(' . get_post_meta($a['id'], "aristech_datepick3", true) . 'T00:00:00)%20TO%20DT(' : '';
                        $datepick4 = get_post_meta($a['id'], "aristech_datepick4", true) ? get_post_meta($a['id'], "aristech_datepick4", true) . 'T23:59:59)]' : $date->format('Y-m-d') .'T23:59:59)]';
                        $extra = get_post_meta($a['id'], "aristech_extra", true) ? get_post_meta($a['id'], "aristech_extra", true) . '&fq=' : '';
                        $textArea = get_post_meta($a['id'], 'aristech_textarea', true);




                        $dateSubmission = $datepick1 != '' ? $datepick1 . $datepick2 : '';
                        $dateIssue = $datepick3 != '' ? $datepick3 . $datepick4 : '';

                        $searchUrl = $this->opendata . $extra . $selectBox . ':['.multiPart($textArea).']'. $textBoxReady . $dateSubmission . $dateIssue . '&wt=json' ;
                        $json = file_get_contents($searchUrl);
                        $mobj = json_decode($json);
                        $myobj = $mobj->decisionResultList;
                        foreach ($myobj as $key => &$value) {


                                $txt .= '<tr>';
                                $txt .= '<td scope="row">'. $value->issueDate.'</td>';
                                $txt .= '<td scope="row">'. $value->submissionTimestamp .'</td>';
                                $txt .= '<td scope="row">'. $value->subject .'</td>';
                                $txt .= '<th scope="row">'. $value->ada .'</th>';
                                $txt .= '<td scope="row">'. $value->decisionTypeLabel .'</td>';
                                $txt .= '<td scope="row">'. $value->protocolNumber  .'</td>';
                                $txt .= '<td scope="row"><a href="'. $value->documentUrl  .'" target="_self"><button type="button" class="btn btn-small btn-outline-secondary">λήψη</button></a><br><a href="'. $value->documentUrl  .'?inline=true" target="_blank"><button type="button" class="btn btn-small btn-outline-secondary">Προβολή αρχείου</button></a></td>';
                                $txt .= '</tr>';
                        }


                        $txt .= '</tbody>
                        </table>
                </div>';
        return $txt;
     }

}

if (class_exists('Diavgeia')) {
    $AristechDiavgeia =new Diavgeia();
    $AristechDiavgeia->register();
}

//activate
require_once plugin_dir_path( __FILE__ ). 'inc/aristech_diavgeia_activate.php';
register_activation_hook( __FILE__, array('AristechRssActivate', 'activate') );

//deactivate
require_once plugin_dir_path( __FILE__ ). 'inc/aristech_diavgeia_deactivate.php';
register_deactivation_hook( __FILE__, array('AristechRssDeactivate', 'deactivate') );




