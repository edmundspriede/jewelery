<?php




add_filter( 'woocommerce_product_import_process_item_data', function ( $data ) {
	global $raw_image_id, $raw_gallery_image_ids;
	if ( isset( $data['raw_image_id'] ) ) {
		$raw_image_id = $data['raw_image_id'];//save to process later
		unset( $data['raw_image_id'] );//unset this so that images are not imported by WooCommerce
	} else {
		$raw_image_id = '';
	}
	if ( isset( $data['raw_gallery_image_ids'] ) ) {
		$raw_gallery_image_ids = $data['raw_gallery_image_ids'];//save to process later
		unset( $data['raw_gallery_image_ids'] );//unset this so that images are not imported by WooCommerce
	} else {
		$raw_gallery_image_ids = array();
	}

	return $data;
} );
add_action( 'woocommerce_product_import_inserted_product_object', function ( $product, $data ) {
	global $raw_image_id, $raw_gallery_image_ids;
	
	exit();
	if ( class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
		$save = false;
		if ( $raw_image_id ) {
			$add_image = EXMAGE_WP_IMAGE_LINKS::add_image( $raw_image_id, $image_id );
			if ( $add_image['id'] ) {
				$product->set_image_id( $add_image['id'] );
				$save = true;
			}
		}
		if ( $raw_gallery_image_ids ) {
			$gallery_image_ids = array();

			foreach ( $raw_gallery_image_ids as $image_url ) {
				$add_image = EXMAGE_WP_IMAGE_LINKS::add_image( $image_url, $image_id );
				if ( $add_image['id'] ) {
					$gallery_image_ids[] = $add_image['id'];
				}
			}
			if ( $gallery_image_ids ) {
				$product->set_gallery_image_ids( $gallery_image_ids );
				$save = true;
			}
		}
		if ( $save ) {
			$product->save();
		}
	}

}, 10, 2 );

function exmage_add_image( WP_REST_Request $request ) {
  
  $url = $request['url'];
  if ( class_exists( 'EXMAGE_WP_IMAGE_LINKS' ) ) {
				$post_parent    = 0;//ID of the post that you want this image to be attached to
				$external_image = EXMAGE_WP_IMAGE_LINKS::add_image( $url, $image_id, $post_parent );
			}	
	
  $response = new WP_REST_Response( array($url,$external_image ) );	
  return $response;	
}	

add_action( 'rest_api_init', function () {
  register_rest_route( 'exmage/v1', 'add', array(
    'methods' => 'GET',
    'callback' => 'exmage_add_image',
  ) );
} );


function allow_unsafe_urls ( $args ) {
       $args['reject_unsafe_urls'] = false;
       return $args;
    } ;

add_filter( 'http_request_args', 'allow_unsafe_urls' );

function ir_webhook_http_args($http_args , $arg, $id){
  
  return array_merge($http_args, array('sslverify'   => false));
}

add_action( 'woocommerce_webhook_http_args', 'ir_webhook_http_args', 10, 3 );
