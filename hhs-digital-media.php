<?php
/*
Plugin Name: HHS Digital Media
Plugin URI: http://www.cdc.gov
Description: This is a Wordpress module that can be used to search and embed HHS Digital Media content. It has been built and tested on Wordpress 3.9.1.
Version: 1.0
Author: David Cummo
Author URI: 
License: 
*/

function create_hhs_digital_media() {
  register_post_type( 'hhs_digital_media',
    array(
      'labels' => array(
        'name' => 'HHS Digital Media',
        'singular_name' => 'HHS Digital Media Item',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Digital Media Item',
        'edit' => 'Edit',
        'edit_item' => 'Edit Digital Media Item',
        'new_item' => 'New Digital Media Item',
        'view' => 'View',
        'view_item' => 'View Digital Media Item',
        'search_items' => 'Search Digital Media',
        'not_found' => 'No HHS Digital Media Items found',
        'not_found_in_trash' => 'No HHS Digital Media Items found in Trash',
        'parent' => 'Parent Digital Media Item'
      ),
      'capability_type' => 'page',
      'public' => true,
      'menu_position' => 15,
      'supports' => array( 'title', 'thumbnail' ),
      'taxonomies' => array( '' ),
      'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
      'has_archive' => true
    )
  );
  flush_rewrite_rules();
}

function hhs_digital_media_admin() {
  wp_enqueue_script( 'hhs_digital_media', plugins_url( '/html/js/hhs_digital_media.js', __FILE__ ), array( 'json2', 'jquery', 'jquery-ui-autocomplete'), '1.0.0');
  wp_enqueue_script( 'hhs_jstree', plugins_url( '/html/js/jstree.js', __FILE__ ), array( 'jquery'));
  wp_enqueue_script( 'hhs_jquery-maskedinput', plugins_url( '/html/js/jquery.maskedinput.js', __FILE__ ), array( 'jquery'));
  wp_enqueue_style( 'hhs_digital_media', plugins_url( '/html/css/hhs_digital_media.css', __FILE__ ), array(), '1.0.0');
  wp_enqueue_style( 'hhs_treestyle', plugins_url( '/html/css/treestyle.css', __FILE__ ), array(), '1.0.0');

  add_meta_box( 'hhs_digital_media_select_content_meta_box',
    'Select Content',
    'hhs_digital_media_select_content_meta_box',
    'hhs_digital_media', 'normal', 'high'
  );

  add_meta_box( 'hhs_digital_media_display_options_meta_box',
    'Display Options',
    'hhs_digital_media_display_options_meta_box',
    'hhs_digital_media', 'side', 'low'
  );

  add_meta_box( 'hhs_digital_media_preview_meta_box',
    'Content Preview',
    'hhs_digital_media_preview_meta_box',
    'hhs_digital_media', 'normal', 'high'
  );
}

function hhs_digital_media_select_content_meta_box( $hhs_digital_media_item ) {
  // Retrieve current name of the Director and Movie Rating based on review ID
  $source = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_sourceval', true ) );
  $search_type = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_searchtype', true ) );
  if ($search_type == "") {
    $search_type = "0";
  }
  $from_date = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_fromdate', true ) );
  $media_types = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_mediatypesval', true ) );
  $topics = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_topictree', true ) );
  $title = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_titleval', true ) );
  $url_id = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_urlmediaidval', true ) );

  ?>
    <div class="hhs_digital_media_form_field">
      <label for="cdccs_source">Source:</label>
        <select id="cdccs_source" name="cdccs_source">
        </select>
    </div>
    <div class="hhs_digital_media_form_field">
      <label for="cdccs_searchtype">Search Type:</label>
      <input type="radio" name="cdccs_searchtype" value="0" <?php echo checked( $search_type, "0" ); ?>/>Metadata
      <input type="radio" name="cdccs_searchtype" value="1" <?php echo checked( $search_type, "1" ); ?>/>Url
    </div>
    <div id="searchbymetadata">
      <div class="hhs_digital_media_form_field">
        <label for="cdccs_fromdate">From Date:</label>
        <input type="text" id="cdccs_fromdate" name="cdccs_fromdate"/>
      </div>
      <div class="hhs_digital_media_form_field">
        <label for="cdccs_mediatypes">Media Types:</label>
        <select id="cdccs_mediatypes" name="cdccs_mediatypes" multiple>
        </select>
      </div>
      <div class="hhs_digital_media_form_field">
        <div class="label">Topics:</div>
        <div id="cdccs_topictree_control"></div>
      </div>
      <div class="hhs_digital_media_form_field">
        <label for="cdccs_title">Title:</label>
        <select id="cdccs_title" name="cdccs_title">
        </select>
      </div>
    </div>
    <div id="searchbyurl">
      <div class="hhs_digital_media_form_field">
        <label for="cdccs_url">Url Contains:</label>
        <input type="text" id="cdccs_url" name="cdccs_url" size="100"/>
      </div>
    </div>
    <input type="hidden" name="cdccs_sourceval" id="cdccs_sourceval" value="<?php echo $source ?>"/>
    <input type="hidden" name="cdccs_mediatypesval" id="cdccs_mediatypesval"/>
    <input type="hidden" name="cdccs_topictree" id="cdccs_topictree"/>
    <input type="hidden" name="cdccs_titleval" id="cdccs_titleval"/>
    <input type="hidden" name="cdccs_urlmediaidval" id="cdccs_urlmediaidval"/>
  <?php
}

function hhs_digital_media_display_options_meta_box( $hhs_digital_media_item ) {
  ?>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripimages">Strip Images:</label>
        <input type="checkbox" id="cdccs_stripimages" name="cdccs_stripimages"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripscripts">Strip Scripts:</label>
        <input type="checkbox" id="cdccs_stripscripts" name="cdccs_stripscripts"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripanchors">Strip Anchors:</label>
        <input type="checkbox" id="cdccs_stripanchors" name="cdccs_stripanchors"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripcomments">Strip Comments:</label>
        <input type="checkbox" id="cdccs_stripcomments" name="cdccs_stripcomments"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripinlinestyles">Strip Inline Styles:</label>
        <input type="checkbox" id="cdccs_stripinlinestyles" name="cdccs_stripinlinestyles"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripbreaks">Strip Breaks:</label>
        <input type="checkbox" id="cdccs_stripbreaks" name="cdccs_stripbreaks"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_imagefloat">Image Float:</label>
        <select id="cdccs_imagefloat" name="cdccs_imagefloat">
          <option value="">Default</option>
          <option value="left">Left</option>
          <option value="right">Right</option>
        </select>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_cssclasses">CSS Classes (comma separated):</label>
        <input type="text" id="cdccs_cssclasses" name="cdccs_cssclasses"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_ids">Element Ids (comma separated):</label>
        <input type="text" id="cdccs_ids" name="cdccs_ids"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_xpath">XPath:</label>
        <input type="text" id="cdccs_xpath" name="cdccs_xpath"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_namespace">Namespace Prefix:</label>
        <input type="text" id="cdccs_namespace" name="cdccs_namespace"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_linkssamewindow">Links Open Same Window:</label>
        <input type="checkbox" id="cdccs_linkssamewindow" name="cdccs_linkssamewindow"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_width">Width:</label>
        <input type="text" id="cdccs_width" name="cdccs_width"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_height">Height:</label>
        <input type="text" id="cdccs_height" name="cdccs_height"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_https">Use HTTPS:</label>
        <input type="checkbox" id="cdccs_https" name="cdccs_https"/>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_encoding">Encoding:</label>
        <select id="cdccs_encoding" name="cdccs_encoding">
          <option value="">Default</option>
          <option value="utf-8">UTF-8</option>
          <option value="iso-8859-1">iso-8859-1</option>
        </select>
      </div>    
  <?php
}

function hhs_digital_media_preview_meta_box( $hhs_digital_media_item ) {
  ?>
    <div id="cdccs_preview_div"></div>
  <?php
}

function add_hhs_digital_media_fields( $hhs_digital_media_item_id, $hhs_digital_media_item ) {
  // Check post type for movie reviews
  if ( $hhs_digital_media_item->post_type == 'hhs_digital_media' ) {
    // Store data in post meta table if present in post data
    if ( isset( $_POST['cdccs_source'] ) && $_POST['cdccs_source'] != '' ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_source', $_POST['cdccs_source'] );
    }
    if ( isset( $_POST['cdccs_searchtype'] ) && $_POST['cdccs_searchtype'] != '' ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_searchtype', $_POST['cdccs_searchtype'] );
    }
  }
}

function add_hhs_content($content) {
  if ( get_post_type() == 'hhs_digital_media' ) {
    $content .= "<div>Search Type: " . esc_html( get_post_meta( get_the_ID(), 'cdccs_searchtype', true ))."<div>";
  }
  return $content;
}

add_action( 'init', 'create_hhs_digital_media' );
add_action( 'admin_init', 'hhs_digital_media_admin' );
add_action( 'save_post', 'add_hhs_digital_media_fields', 10, 2 );
add_action('the_content', 'add_hhs_content');
?>
