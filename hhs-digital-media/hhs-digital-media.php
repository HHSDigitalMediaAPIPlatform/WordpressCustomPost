<?php
/*
Plugin Name: HHS Digital Media
Plugin URI: https://github.com/HHSDigitalMediaAPIPlatform/WordpressCustomPost
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

function hhs_digital_media_meta_boxes( $post ) {
 
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

function hhs_digital_media_enqueue_scripts($hook) {
  if ( get_post_type() != 'hhs_digital_media' ) {
    return;
  }

  wp_enqueue_script( 'hhs_digital_media', plugins_url( '/html/js/hhs_digital_media.js', __FILE__ ), array( 'json2', 'jquery', 'jquery-ui-autocomplete'), '1.0.0');
  wp_enqueue_script( 'hhs_jstree', plugins_url( '/html/js/jstree.js', __FILE__ ), array( 'jquery'));
  wp_enqueue_script( 'hhs_jquery-maskedinput', plugins_url( '/html/js/jquery.maskedinput.js', __FILE__ ), array( 'jquery'));
  wp_enqueue_style( 'hhs_digital_media', plugins_url( '/html/css/hhs_digital_media.css', __FILE__ ), array(), '1.0.0');
  wp_enqueue_style( 'hhs_treestyle', plugins_url( '/html/css/treestyle.css', __FILE__ ), array(), '1.0.0');
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
  $url = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_url', true ) );
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
        <input type="text" id="cdccs_fromdate" name="cdccs_fromdate" value="<?php echo $from_date ?>"/>
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
        <input type="text" id="cdccs_url" name="cdccs_url" size="100" value="<?php echo $url ?>"/>
      </div>
    </div>
    <input type="hidden" name="cdccs_sourceval" id="cdccs_sourceval" value="<?php echo $source ?>"/>
    <input type="hidden" name="cdccs_mediatypesval" id="cdccs_mediatypesval" value="<?php echo $media_types ?>"/>
    <input type="hidden" name="cdccs_topictree" id="cdccs_topictree" value="<?php echo $topics ?>"/>
    <input type="hidden" name="cdccs_titleval" id="cdccs_titleval" value="<?php echo $title ?>"/>
    <input type="hidden" name="cdccs_urlmediaidval" id="cdccs_urlmediaidval" value="<?php echo $url_id ?>"/>
  <?php
}

function hhs_digital_media_display_options_meta_box( $hhs_digital_media_item ) {
  $strip_images = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripimages', true ) );
  $strip_scripts = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripscripts', true ) );
  $strip_anchors = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripanchors', true ) );
  $strip_comments = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripcomments', true ) );
  $strip_inline_styles = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripinlinestyles', true ) );
  $strip_breaks = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_stripbreaks', true ) );
  $image_float = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_imagefloat', true ) );
  $css_classes = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_cssclasses', true ) );
  $element_ids = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_ids', true ) );
  $xpath = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_xpath', true ) );
  $namespace = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_namespace', true ) );
  $links_same_window = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_linkssamewindow', true ) );
  $width = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_width', true ) );
  $height = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_height', true ) );
  $https = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_https', true ) );
  $encoding = esc_html( get_post_meta( $hhs_digital_media_item->ID, 'cdccs_encoding', true ) );
  ?>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripimages">Strip Images:</label>
        <input type="checkbox" id="cdccs_stripimages" name="cdccs_stripimages" <?php echo checked( $strip_images, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripscripts">Strip Scripts:</label>
        <input type="checkbox" id="cdccs_stripscripts" name="cdccs_stripscripts" <?php echo checked( $strip_scripts, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripanchors">Strip Anchors:</label>
        <input type="checkbox" id="cdccs_stripanchors" name="cdccs_stripanchors" <?php echo checked( $strip_anchors, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripcomments">Strip Comments:</label>
        <input type="checkbox" id="cdccs_stripcomments" name="cdccs_stripcomments" <?php echo checked( $strip_comments, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripinlinestyles">Strip Inline Styles:</label>
        <input type="checkbox" id="cdccs_stripinlinestyles" name="cdccs_stripinlinestyles" <?php echo checked( $strip_inline_styles, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_stripbreaks">Strip Breaks:</label>
        <input type="checkbox" id="cdccs_stripbreaks" name="cdccs_stripbreaks" <?php echo checked( $strip_breaks, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_imagefloat">Image Float:</label>
        <select id="cdccs_imagefloat" name="cdccs_imagefloat">
        <option value="" <?php selected( $image_float, '') ?>>Default</option>
          <option value="left" <?php selected( $image_float, 'left') ?>>Left</option>
          <option value="right" <?php selected( $image_float, 'right') ?>>Right</option>
        </select>
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_cssclasses">CSS Classes (comma separated):</label>
        <input type="text" id="cdccs_cssclasses" name="cdccs_cssclasses" value="<?php echo $css_classes ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_ids">Element Ids (comma separated):</label>
        <input type="text" id="cdccs_ids" name="cdccs_ids" value="<?php echo $element_ids ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_xpath">XPath:</label>
        <input type="text" id="cdccs_xpath" name="cdccs_xpath" value="<?php echo $xpath ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_namespace">Namespace Prefix:</label>
        <input type="text" id="cdccs_namespace" name="cdccs_namespace" value="<?php echo $namespace ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_linkssamewindow">Links Open Same Window:</label>
        <input type="checkbox" id="cdccs_linkssamewindow" name="cdccs_linkssamewindow" <?php echo checked( $links_same_window, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_width">Width:</label>
        <input type="text" id="cdccs_width" name="cdccs_width" value="<?php echo $width ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_height">Height:</label>
        <input type="text" id="cdccs_height" name="cdccs_height" value="<?php echo $height ?>"/>
      </div>
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_https">Use HTTPS:</label>
        <input type="checkbox" id="cdccs_https" name="cdccs_https" <?php echo checked( $https, "1" ); ?> />
      </div>    
      <div class="hhs_digital_media_form_field_options">
        <label for="cdccs_encoding">Encoding:</label>
        <select id="cdccs_encoding" name="cdccs_encoding">
        <option value="" <?php selected( $encoding, '') ?>>Default</option>
          <option value="utf-8" <?php selected( $encoding, 'utf-8') ?>>UTF-8</option>
          <option value="iso-8859-1" <?php selected( $encoding, 'iso-8859-1') ?>>iso-8859-1</option>
        </select>
      </div>    
  <?php
}

function hhs_digital_media_preview_meta_box( $hhs_digital_media_item ) {
  $preview_url = esc_html( get_post_meta( $id, 'cdccs_preview', true ) );
  ?>
    <div id="cdccs_preview_div"></div>
    <input type="hidden" name="cdccs_preview" id="cdccs_preview" value="<?php echo $preview_url ?>"/>
  <?php
}

function add_hhs_digital_media_fields( $hhs_digital_media_item_id, $hhs_digital_media_item ) {
  if ( $hhs_digital_media_item->post_type == 'hhs_digital_media' ) {
    // Store data in post meta table if present in post data
    if ( isset( $_POST['cdccs_sourceval'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_sourceval', $_POST['cdccs_source'] );
    }
    if ( isset( $_POST['cdccs_searchtype'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_searchtype', $_POST['cdccs_searchtype'] );
    }
    if ( isset( $_POST['cdccs_fromdate'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_fromdate', $_POST['cdccs_fromdate'] );
    }
    if ( isset( $_POST['cdccs_mediatypesval'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_mediatypesval', $_POST['cdccs_mediatypesval'] );
    }
    if ( isset( $_POST['cdccs_topictree'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_topictree', $_POST['cdccs_topictree'] );
    }
    if ( isset( $_POST['cdccs_titleval'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_titleval', $_POST['cdccs_titleval'] );
    }
    if ( isset( $_POST['cdccs_url'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_url', $_POST['cdccs_url'] );
    }
    if ( isset( $_POST['cdccs_urlmediaidval'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_urlmediaidval', $_POST['cdccs_urlmediaidval'] );
    }
    if ( isset( $_POST['cdccs_preview'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_preview', $_POST['cdccs_preview'] );
    }
    if ( isset( $_POST['cdccs_stripimages'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripimages', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripimages', FALSE );
    }
    if ( isset( $_POST['cdccs_stripscripts'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripscripts', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripscripts', FALSE );
    }
    if ( isset( $_POST['cdccs_stripanchors'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripanchors', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripanchors', FALSE );
    }
    if ( isset( $_POST['cdccs_stripcomments'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripcomments', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripcomments', FALSE );
    }
    if ( isset( $_POST['cdccs_stripinlinestyles'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripinlinestyles', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripinlinestyles', FALSE );
    }
    if ( isset( $_POST['cdccs_stripbreaks'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripbreaks', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_stripbreaks', FALSE );
    }
    if ( isset( $_POST['cdccs_imagefloat'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_imagefloat', $_POST['cdccs_imagefloat'] );
    }
    if ( isset( $_POST['cdccs_cssclasses'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_cssclasses', $_POST['cdccs_cssclasses'] );
    }
    if ( isset( $_POST['cdccs_ids'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_ids', $_POST['cdccs_ids'] );
    }
    if ( isset( $_POST['cdccs_xpath'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_xpath', $_POST['cdccs_xpath'] );
    }
    if ( isset( $_POST['cdccs_namespace'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_namespace', $_POST['cdccs_namespace'] );
    }
    if ( isset( $_POST['cdccs_linkssamewindow'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_linkssamewindow', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_linkssamewindow', FALSE );
    }
    if ( isset( $_POST['cdccs_width'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_width', $_POST['cdccs_width'] );
    }
    if ( isset( $_POST['cdccs_height'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_height', $_POST['cdccs_height'] );
    }
    if ( isset( $_POST['cdccs_https'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_https', TRUE );
    } else {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_https', FALSE );
    }
    if ( isset( $_POST['cdccs_encoding'] ) ) {
      update_post_meta( $hhs_digital_media_item_id, 'cdccs_encoding', $_POST['cdccs_encoding'] );
    }
  }
}

function add_hhs_content($content) {
  if ( get_post_type() == 'hhs_digital_media' ) {
    $id = get_the_ID();
    $preview_url = esc_html( get_post_meta( $id, 'cdccs_preview', true ) );
    $content = '<div id="'.$id.'"></div>';
    $content .= '<script type="text/javascript">';
    $content .= 'function mediaCallback_'.$id.'(response) {';
    $content .= 'if (response && response.results) {';
    $content .= 'jQuery(\'#'.$id.'\').html(response.results.content);';
    $content .= '}';
    $content .= '}';
    $content .= 'jQuery(document).ready(function() {';
    $content .= 'jQuery.ajaxSetup({cache:false});';
    $content .= 'jQuery.ajax({';
    $content .= 'url: "'.$preview_url.'",';
    $content .= 'dataType: "jsonp",';
    $content .= 'success: mediaCallback_'.$id.',';
    $content .= 'error: function(xhr, ajaxOptions, thrownError) {}';
    $content .= '});';
    $content .= '});';
    $content .= '</script>';
  }
  return $content;
}

add_action( 'init', 'create_hhs_digital_media' );
add_action( 'save_post', 'add_hhs_digital_media_fields', 10, 2 );
add_action( 'the_content', 'add_hhs_content' );
add_action( 'admin_enqueue_scripts', 'hhs_digital_media_enqueue_scripts' );
add_action( 'add_meta_boxes_hhs_digital_media', 'hhs_digital_media_meta_boxes' );
?>
