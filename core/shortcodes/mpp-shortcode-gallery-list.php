<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Galler Listing shortcode
 */
add_shortcode( 'mpp-gallery', 'mpp_gallery_shortcode' );

function mpp_gallery_shortcode( $atts = null, $content = '' ) {
    //allow everything that can be done to be passed via this shortcode
    
        $defaults = array(
                'type'          => false, //gallery type, all,audio,video,photo etc
                'id'            => false, //pass specific gallery id
                'in'            => false, //pass specific gallery ids as array
                'exclude'       => false, //pass gallery ids to exclude
                'slug'          => false,//pass gallery slug to include
                'status'        => false, //public,private,friends one or more privacy level
                'component'     => false, //one or more component name user,groups, evenets etc
                'component_id'  => false,// the associated component id, could be group id, user id, event id
                'per_page'      => false, //how many items per page
                'offset'        => false, //how many galleries to offset/displace
                'page'          => false,//which page when paged
                'nopaging'      => false, //to avoid paging
                'order'         => 'DESC',//order 
                'orderby'       => 'date',//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
                //user params
                'user_id'       => false,
                'include_users' => false,
                'exclude_users' => false,//users to exclude
                'user_name'     => false,
                'scope'         => false,
                'search_terms'  => '',
            //time parameter
                'year'          => false,//this years
                'month'         => false,//1-12 month number
                'week'          => '', //1-53 week
                'day'           => '',//specific day
                'hour'          => '',//specific hour
                'minute'        => '', //specific minute
                'second'        => '',//specific second 0-60
                'yearmonth'     => false,// yearMonth, 201307//july 2013
                'meta_key'		=> '',
                'meta_value'	=> '',
               // 'meta_query'=>false,
                'fields'    => false,//which fields to return ids, id=>parent, all fields(default)
				'column'	=> 4,
        );
        
    $atts = shortcode_atts( $defaults, $atts );
    
    if( ! $atts['meta_key'] ) {
        
        unset( $atts['meta_key'] );
        unset( $atts['meta_value'] );
    }
    
	$shortcode_column = $atts['column'];
	mpp_shortcode_save_gallery_data( 'column', $shortcode_column );
	
	unset( $atts['column'] );
	
    $query = new MPP_Gallery_Query( $atts );
    
    ob_start();
    
    echo '<div class="mpp-container mpp-shortcode-wrapper mpp-shortcode-gallery-wrapper"><div class="mpp-g mpp-item-list mpp-gallery-list mpp-shortcode-item-list mpp-shortcode-gallery-list"> ';
    
    while( $query->have_galleries() ): $query->the_gallery();
    
        mpp_get_template_part( 'shortcodes/gallery', 'entry' );//shortcodes/gallery-entry.php
    
    
    endwhile;
    mpp_reset_gallery_data();
    echo '</div></div>';   
    
    $content = ob_get_clean();
	
	mpp_shortcode_reset_gallery_data( 'column' );
    
    return $content;
}
add_shortcode( 'mpp-show-gallery', 'mpp_shortcode_show_gallery' );

function mpp_shortcode_show_gallery( $atts = null, $content = '' ) {
    
        $defaults = array(
			'id'            => false, //pass specific gallery id
			'in'            => false, //pass specific gallery ids as array
			'exclude'       => false, //pass gallery ids to exclude
			'slug'          => false,//pass gallery slug to include
			'per_page'      => false, //how many items per page
			'offset'        => false, //how many galleries to offset/displace
			'page'          => false,//which page when paged
			'nopaging'      => false, //to avoid paging
			'order'         => 'DESC',//order 
			'orderby'       => 'date',//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
			//user params
			'user_id'       => false,
			'include_users' => false,
			'exclude_users' => false,//users to exclude
			'user_name'     => false,
			'scope'         => false,
			'search_terms'  => '',

			'meta_key'		=> '',
			'meta_value'	=> '',
			'column'		=> 4,
			'view'			=> '',
        );
        
	$defaults = apply_filters( 'mpp_shortcode_show_gallery_defaults', $defaults );
	
    $atts = shortcode_atts( $defaults, $atts );
    
	if( ! $atts['id'] ) {
		return '';
	}
	
	$gallery_id = absint( $atts['id'] );
	
	$gallery = mpp_get_gallery( $gallery_id );
	//if gallery does not exist, there is no proint in further proceeding
	if( ! $gallery ) {
		return '';
	}
	
	
    if( ! $atts['meta_key'] ) {
        
        unset( $atts['meta_key'] );
        unset( $atts['meta_value'] );
    }
    
	$view = $atts['view'];
	
	unset( $atts['id'] );
	unset( $atts['view'] );
	
	$atts['gallery_id'] = $gallery_id;

	
	$shortcode_column = $atts['column'];
	mpp_shortcode_save_media_data( 'column', $shortcode_column );
	
	mpp_shortcode_save_media_data( 'shortcode_args', $args );
	
	
	unset( $atts['column'] );
	
	$content = apply_filters( 'mpp_shortcode_show_gallery_content',  $args, $view );
	
	if( ! $content ) {
		
		$templates = array(
			'shortcodes/gallery/grid.php',
			'shortcodes/gallery/single.php',
		);
		
		if ( $view ) {
			$type = $gallery->type;
			
			$preferred_template = "shortcodes/gallery/{$type}-{$view}.php";//audio-playlist, video-playlist
			array_unshift( $templates, $preferred_template );
		}
		
		ob_start();
		mpp_locate_template( $templates,  true );//load
		 
		$content = ob_get_clean();
	}
	
	mpp_shortcode_reset_media_data( 'column' );
	mpp_shortcode_reset_media_data( 'shortcode_args' );
    
    return $content;
}