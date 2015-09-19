<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

//if BuddyPress is active and directory is enabled, redirect archive page to BuddyPress Gallery Directory
add_action( 'mpp_actions', 'mpp_gallery_archive_redirect', 11 );
function mpp_gallery_archive_redirect() {
	
	if( is_post_type_archive( mpp_get_gallery_post_type() ) && mediapress()->is_bp_active() && mpp_get_option( 'has_gallery_directory' ) && isset( buddypress()->pages->mediapress->id ) ) {
		
		wp_safe_redirect( get_permalink( buddypress()->pages->mediapress->id ), 301 );
		exit( 0 );
	}
}

//only list public galleries on the archive page

function mpp_filter_archive_page_galleries( $query ) {
	
	if( is_admin() ) {
		return ;
	}
	
	if( ! $query->is_main_query() || ! $query->is_post_type_archive( mpp_get_gallery_post_type() ) ) {
		return ;
	}
	
	//confirmed that we are on gallery archive page
	
	$active_components = mpp_get_active_components();
	$active_types = mpp_get_active_types();
	
	$status = 'public';
	
	$tax_query = $query->get( 'tax_query' );
	
	if( empty( $tax_query ) ) {
		$tax_query = array();
	}
	//it will be always true
	if( $status ) {
		$status = mpp_string_to_array( $status );//future proofing
		
		$status_keys = array_map('mpp_underscore_it', $status );
		
		$tax_query[] = array(
			'taxonomy'	=> mpp_get_status_taxname(),
			'field'		=> 'slug',
			'terms'		=> $status_keys,
			'operator'	=> 'IN',
		);
	}
	//should we only show sitewide galleries here? will update based on feedback
	if( ! empty( $active_components ) ) {
		$component_keys = array_keys( $active_components );
		$component_keys = array_map('mpp_underscore_it', $component_keys );
					
		$tax_query[] = array(
			'taxonomy'	=> mpp_get_component_taxname(),
			'field'		=> 'slug',
			'terms'		=> $component_keys,
			'operator'	=> 'IN',
		);
	}
	
	if( ! empty( $active_types ) ) {
		
			$type_keys = array_keys( $active_types );
			$type_keys = array_map( 'mpp_underscore_it', $type_keys );

			$tax_query[] = array(
				'taxonomy'	=> mpp_get_type_taxname(),
				'field'		=> 'slug',
				'terms'		=> $type_keys,
				'operator'	=> 'IN',
			);
	}
	
	if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
	}
	
	$query->set( 'tax_query', $tax_query );
	$query->set('update_post_term_cache', true );
	$query->set('update_post_meta_cache', true );
	$query->set('cache_results', true );
	
}
add_action( 'pre_get_posts', 'mpp_filter_archive_page_galleries' );

//hooks applied which are not specific to any gallery component and applies to all

function mpp_modify_page_title( $complete_title, $title, $sep, $seplocation ) {
  
	
	$sub_title = array();
   
	if( ! mpp_is_component_gallery() && ! mpp_is_gallery_component() )
	   return $complete_title;
   
	
	
	if( mpp_is_single_gallery() ){
		
		$sub_title[] = get_the_title( mpp_get_current_gallery_id() );
	}
	if( mpp_is_single_media() ) {
		
		$sub_title[] = get_the_title( mpp_get_current_media_id() );
	}
	
	if( mpp_is_gallery_management() || mpp_is_media_management() ) {
		
		$sub_title[] = ucwords( mediapress()->get_action() );
		$sub_title[] = ucwords( mediapress()->get_edit_action() );
		
	}
	
	$sub_title = array_filter( $sub_title );
	
	if( !empty( $sub_title ) )
		$complete_title = $complete_title .  join( ' | ', $sub_title ) . ' | ';
	
	return $complete_title;
}

add_filter( 'bp_modify_page_title', 'mpp_modify_page_title', 20, 4 );

//filter body class
function mpp_filter_body_class( $classes, $class ) {
	
	$new_classes = array();
	
	$component = mpp_get_current_component();

	//if not mediapress pages, return 
	if( ! mpp_is_gallery_component() && ! mpp_is_component_gallery() ) {
		
		return $classes;
	}
	
	//ok, It must be mpp pages

	$new_classes[] = 'mpp-page'; //for all mediapress pages

	//if it is a directory page
	if( mpp_is_gallery_directory() ) {
		
		$new_classes[]= 'mpp-page-directory';
		
	} elseif( mpp_is_gallery_component() || mpp_is_component_gallery() ) {
		//we are on user gallery  page or a component gallery page
		//append class mpp-page-members or mpp-page-groups or mpp-page-events etc depending on the current associated component
		$new_classes[] = 'mpp-page-'. $component;
		
		if( mpp_is_media_management() ) {
			//is it edit media?	
			$new_classes[] = 'mpp-page-media-management';
			$new_classes[] = 'mpp-page-media-management-' . mpp_get_media_type();//mpp-photo-management, mpp-audio-management
			$new_classes[] = 'mpp-page-media-manage-action-' . mediapress()->get_edit_action();//mpp-photo-management, mpp-audio-management
		
			
		}elseif( mpp_is_single_media() ) {
			//is it single media
			$new_classes[] = 'mpp-page-media-single';
			$new_classes[] = 'mpp-page-media-single-'. mpp_get_media_type();
			
		}elseif( mpp_is_gallery_management() ) {
			//id gallery management?
			$new_classes[] = 'mpp-page-gallery-management';
			$new_classes[] = 'mpp-page-gallery-management-'. mpp_get_gallery_type();
			
			$new_classes[] = 'mpp-page-gallery-manage-action-'. mediapress()->get_edit_action();
			
		}elseif( mpp_is_single_gallery() ) {
			//is singe gallery
			$new_classes[] = 'mpp-page-single-gallery';
			$new_classes[] = 'mpp-page-single-gallery-' . mpp_get_gallery_type();
			$new_classes[] = 'mpp-page-single-gallery-'. mpp_get_gallery_status();
			
		}else {
			//it is the gallery listing page of the component
			
			$new_classes[] = 'mpp-page-gallery-list';//home could have been a better name
			$new_classes[] = 'mpp-page-gallery-list-'. $component;//home could have been a better name
		}
			
	}
	
	if( ! empty( $new_classes ) )
			$classes = array_merge ( $classes, $new_classes );
	
	return $classes;
	
}
add_filter( 'body_class', 'mpp_filter_body_class', 10, 2 );