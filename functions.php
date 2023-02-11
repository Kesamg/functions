<?php
/*
 * Add your own functions here. You can also copy some of the theme functions into this file. 
 * Wordpress will use those functions instead of the original functions then.
 */
 
// set builder mode to debug - Added by Jared
add_action('avia_builder_mode', "builder_set_debug");
function builder_set_debug() {
    return "debug";
}

add_action('admin_head', 'custom_roles_admin');

function custom_roles_admin() {
  echo '<style>
    .custom-roles .acf-input {
      float: left;
    } 
  </style>';
}

// Jared - Add google font 
add_filter('avf_google_heading_font', 'avia_add_heading_font');

function avia_add_heading_font($fonts) {
    $fonts['Nanum Gothic'] = 'Nanum Gothic:400,700,800';
    return $fonts;
}

add_filter('avf_google_content_font', 'avia_add_content_font');

function avia_add_content_font($fonts) {
    $fonts['Nanum Gothic'] = 'Nanum Gothic:400,700,800';
    return $fonts;
}

//GET Variation
add_action('wp_ajax_get_gfox_variation', 'get_gfox_variation');
add_action('wp_ajax_nopriv_get_gfox_variation', 'get_gfox_variation');
function get_gfox_variation($id = null, $pid = null){
	$SizeArr = array();
	$size_html = "";
	$product_id = $id ? $id : $_POST['product_id'];
	$attribute_pa_color = $pid ? $pid : $_POST['attribute_pa_color'];
	
	

	$handle=new WC_Product_Variable($product_id);
	$variations1=$handle->get_children();
    foreach ($variations1 as $value) {
		$single_variation=new WC_Product_Variation($value);
		// var_dump($single_variation);
	
		// $ImagesArr = WC_Product_Variation::get_images($value);
		// var_dump($ImagesArr);
		
		
		
		
		$CombinationArr = $single_variation->get_variation_attributes();
		if ($CombinationArr['attribute_pa_color'] == $attribute_pa_color) {
			//  If the color mathces then add it to the array for later
			$SizeArr[] = $CombinationArr;
			if ($variation_image == '') {
				$variation_image = $single_variation->get_image();
			}
			
		}
		else {
			// the color does not match, so we ignore it.
		}
	}
	
	if (count($SizeArr) > 0) {
		for ($x=0; $x<count($SizeArr); $x++) {
			$size = $SizeArr[$x]['attribute_pa_size'];
			$size_obj = get_term_by('name', $size, 'pa_size');
			$size_html .= '<div class="checkbox-list product_color_checkboxes"><label for="color_checkboxes['.$size.']"> '.$size_obj->name.' <input class="option-input checkbox" name="sizes[]" id="color_checkboxes['.$size.']" value="'. $size_obj->slug .'" type="checkbox"></label></div>';
		}
	}
	else{
		$size_html .= '<span>No Sizes available for this product in this color</span>';
	}
	
	
	
	$results = array(
		'status' => 'true', 
		'size_html' => $size_html, 
		'variation_image'=> $variation_image
	);
	if ($id && $pid) {
	    return $size_html;
    }
	echo json_encode($results);
	exit;
}

// Jared - Add CSS / Javascript files
function add_my_script() {
	wp_enqueue_style(
			'flexisel', 
			get_stylesheet_directory_uri() . '/plugins/flexisel/css.flexisel.css',
			false,'1.0.0','all'
	);
	wp_enqueue_style(
			'responsive-tables', 
			get_stylesheet_directory_uri() . '/plugins/responsive-tables/footable.standalone.min.css',
			false,'3.1.5','all'
	);
	wp_enqueue_script(
            'height-script', // name your script so that you can attach other scripts and de-register, etc.
            get_stylesheet_directory_uri() . '/js/jquery.matchHeight-min.js', // this is the location of your script file
            array('jquery') // this array lists the scripts upon which your script depends
    );
    wp_enqueue_script(
            'flexisel-script', // name your script so that you can attach other scripts and de-register, etc.
            get_stylesheet_directory_uri() . '/plugins/flexisel/jquery.flexisel.js', // this is the location of your script file
            array('jquery') // this array lists the scripts upon which your script depends
    );
	/*wp_enqueue_script(
            'table-script', // name your script so that you can attach other scripts and de-register, etc.
            get_stylesheet_directory_uri() . '/plugins/responsive-tables/footable.min.js', // this is the location of your script file
            array('jquery') // this array lists the scripts upon which your script depends
    );*/
}
add_action('wp_enqueue_scripts', 'add_my_script');

// Jared - Custom Javascript
function add_custom_js() {
    ?>
    <script type="text/javascript">
        (function ($) {
			$(document).ready(function() {
				var width = $(document).width();
				if (width > 767 ) {
					$("#menu-item-search").detach().prependTo('#header_meta .menu');
					$(".single-product-main-image .sidebar").detach().appendTo('.single-product-summary');
					$(".single-product-summary .summary .product_title").detach().prependTo('.single-product .template-shop .container');
				} else {
					return false;
				};
				
				
				var imgs = $('.portraitLandscape');//jQuery class selector
				  imgs.each(function(){
					  
					var img = $(this);
			
					var width = parseInt(img.attr('width')); //jQuery width method
					var height = parseInt(img.attr('height')); //jQuery height method

					if(width <= height){	
						console.log(width+' <= '+height);
					  img.parent().addClass('portrait');
					}else{
					   img.parent().addClass('landscape');
					}
				  })
				
			});
			
			
			
			
			/* Fixed Menu */
            try {
                var stickyTop = $('#header_main_alternate').offset().top;
                $(window).on( 'scroll', function(){
                    if ($(window).scrollTop() >= stickyTop) {
                        $('#header_main_alternate').addClass('header-fixed');
                    } else {
                        $('#header_main_alternate').removeClass('header-fixed');
                    }
                });
            } catch (e) {
                console.log('scroll top error: ', e.messages)
            }
			
			//Equal Height
            $('#top .inner_product_header').matchHeight();
			
			$('.color-list > li > a').click(function(){
			    var variation_image = $(this).data('image-url');
			    var $image_gallery_wrapper = $('.woocommerce-product-gallery__wrapper');
			    var $image_gallery_thumbnails = $('.woocommerce-product-gallery__wrapper .thumbnails a');

			    if($image_gallery_wrapper.length && $image_gallery_thumbnails.length > 1) {
			        var $loaded_image_link = $('.woocommerce-product-gallery__wrapper a').first();
			        var $loaded_image = $loaded_image_link.find('img').first();

			        var $select_thumbnail_link = $('.woocommerce-product-gallery__wrapper .thumbnails a[href="'+ variation_image + '"]');
			        var $select_thumbnail_link_image = $select_thumbnail_link.find('img').first();

                    $loaded_image_link.attr('href', variation_image);
                    $loaded_image.attr('src', $select_thumbnail_link_image.attr('src'));
                    $loaded_image.attr('srcset', $select_thumbnail_link_image.attr('srcset'));

                }

				$(this).parent().toggleClass('active').siblings().removeClass('active');
				if ($(this).parent().hasClass('active')) {
					$(this).siblings('input').click();
				} else {
					$(this).siblings('input').prop('checked', false);
				}
				return false;
			});
			$('.color-section input[type="radio"]').on('change', function () {
				var value = $(this).data('colorname');
				$(".color-text").html(value);
			});
			$(window).load(function() {
    			$(".product_column .up-sells .products").flexisel({
					visibleItems: 3,
					itemsToScroll: 1,
					animationSpeed: 500,
					infinite: false,
					navigationTargetSelector: null,
					autoPlay: {
						enable: false,
						interval: 5000,
						pauseOnHover: true
					},
					responsiveBreakpoints: { 
						portrait: { 
							changePoint:480,
							visibleItems: 1,
							itemsToScroll: 1
						}, 
						landscape: { 
							changePoint:640,
							visibleItems: 2,
							itemsToScroll: 2
						},
						tablet: { 
							changePoint:768,
							visibleItems: 3,
							itemsToScroll: 3
						}
					}	
				});
				$(".product_column .related .products").flexisel({
					visibleItems: 3,
					itemsToScroll: 1,
					animationSpeed: 500,
					infinite: false,
					navigationTargetSelector: null,
					autoPlay: {
						enable: false,
						interval: 5000,
						pauseOnHover: true
					},
					responsiveBreakpoints: { 
						portrait: { 
							changePoint:480,
							visibleItems: 1,
							itemsToScroll: 1
						}, 
						landscape: { 
							changePoint:640,
							visibleItems: 2,
							itemsToScroll: 2
						},
						tablet: { 
							changePoint:768,
							visibleItems: 3,
							itemsToScroll: 3
						}
					}	
				});
				// Responsive Table
                /*
				if($('.responsive-table').length){
				  jQuery(function ($) {
					  if ($('.responsive-table').length) {
						  $('.responsive-table').footable({
							  "showToggle": false,
							  "expandAll": true,
							  "cascade": true
						  });
					  }
				  });
				};
				*/
				// @TODO fix .footable is not a function error and uncomment above
			});
			
			//Overlay
			function hideOverlay(){
				jQuery(".overlay-processing").remove();
			}

			function showOverlay(){
				var overlay = '<div id="overlay" class="overlay-processing">';

				overlay += '	<div class="center-icon">';

				overlay += '			<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>';

				overlay += '	<p>Please wait...</p>';

				overlay += '	</div>';

				overlay += '</div>';

				if(jQuery('.overlay-processing').length > 0){
					//ignore
				}else{
					jQuery('body').prepend(overlay);
					//alert('Overlay added successfully');
				}	

			}
			
			$(document).on('change', ".pa_colors", function () {
                $('.order-container table tbody td.color_column').html($('ul.color-list li.active a').data('avia-tooltip'));
				$color = $(this).val();
				$product_id = $(this).data("product_id");
                load_get_variation_size($color, $product_id);
			});
			
			function load_get_variation_size($color, $product_id){
				hideVariationsContainer();
				showOverlay();
				$('.size-section').html("");
				$.ajax({
					dataType: 'json',
					type: "POST",
					cache: false,
					async: false,
					url: "/wp-admin/admin-ajax.php",
					data: {action: 'get_gfox_variation','attribute_pa_color': $color, 'product_id': $product_id},
					success: function (data) {
						if(data['status'] == 'true'){
							$('.size-section').removeClass('hidden').html(data['size_html']);
						}
						hideOverlay();
					}
				});
			}
			
			$(document).ready(function(){
				$color = getUrlParameter('attribute_pa_color');
				$size = getUrlParameter('attribute_pa_size');
				if($color){
					$product_id = $(":radio[value="+$color+"]").data('product_id');
					load_get_variation_size($color, $product_id);
					if($size){
						$("input:checkbox[name='sizes[]']").each( function () {
						   if($size == $(this).val()){
							   $(this).trigger("click");
						   }
					   });
					}
				}
			});
			
			var getUrlParameter = function getUrlParameter(sParam) {
				var sPageURL = decodeURIComponent(window.location.search.substring(1)),
					sURLVariables = sPageURL.split('&'),
					sParameterName,
					i;

				for (i = 0; i < sURLVariables.length; i++) {
					sParameterName = sURLVariables[i].split('=');

					if (sParameterName[0] === sParam) {
						return sParameterName[1] === undefined ? true : sParameterName[1];
					}
				}
			};
			
            function hideVariationsContainer () {
                $('.order-container, .order-container table tbody tr.variations_row').addClass('hidden');
            }
			// show/hide place order table
            $(document).on('change', 'input[name="sizes[]"]', function () {
                try {
                    showOverlay();
                    var selectedColor = $('ul.color-list li.active input').first().val().toLowerCase();
                    var $checkedItems = $('[name="sizes[]"]:checked');
                    var listOfSizes = [];
                    $checkedItems.each(function () {
                        listOfSizes.push($(this).val());
                    });
                    if($checkedItems.length && selectedColor) {
                        var $variationsItemRow = $('.order-container table tbody tr.variations_row');
                        var variationsFound = 0;
                        $.each($variationsItemRow, function (i, elem) {
                            var $element = $(elem);
                            if(listOfSizes.indexOf($element.data('size').toString()) >= 0 && $element.data('color') === selectedColor) {
                                variationsFound++;
                                $element.removeClass('hidden');
                            } else {
                                $element.addClass('hidden');
                            }
                        });
                        if(variationsFound > 0) {
                            $('.order-container').removeClass('hidden');
                        }
                    } else {
                        hideVariationsContainer();
                    }
                    // Show list of selected sizes
                    var $sizePlaceholder = $('.size-selected-placeholder');
                    if($sizePlaceholder.length && $checkedItems.length) {
                        $sizePlaceholder.removeClass('hidden');
                        var selectedSizeList = listOfSizes.join(', ');
                        $sizePlaceholder.find('span').html(selectedSizeList);
                    } else {
                        $sizePlaceholder.addClass('hidden');
                    }
                    hideOverlay();
                } catch (e) {

                }
            });
            // PLUS MINUS INCREMENT FOR ADD TO CART
			
            $(document).on('click', '.quantity input.minus, .quantity input.plus', function () {
                var actionType = $(this).val();
                var $qtyInputId = $(this).data('field');
                var $qtyInput = $('#'+$qtyInputId);
                var inputValue = $qtyInput.val();
                if(actionType === '+' && inputValue < parseInt($qtyInput.attr('max'))) {
					inputValue++
                } else if(actionType === '-' && inputValue > parseInt($qtyInput.attr('min'))) {
                    inputValue--
                }
                $qtyInput.val(inputValue > 0 ? inputValue : 1);
                $qtyInput.trigger('change');
            });
			
            if($('ul.color-list li').length === 1) {
                $('.color-list > li > a').unbind('click');
                $('.order-container table tbody td.color_column').html($('ul.color-list li.active a').data('avia-tooltip'));
            }

            if($('input[name="sizes[]"]').length === 1) {
                $('input[name="sizes[]"]').prop('checked', true);
                $('input[name="sizes[]"]').trigger('change');
            }

            // var $page_gallery = $('.woocommerce-product-gallery__wrapper');
            // var $page_gallery_thumbnails = $('.woocommerce-product-gallery__wrapper .thumbnails');
            // if($page_gallery.length > 0 && $page_gallery_thumbnails.length > 0) {
            	// var $cloned_loaded_image_link = $('.woocommerce-product-gallery__wrapper a').first().clone();
            	// $cloned_loaded_image_link.find('span').attr('style', '');
            	// $cloned_loaded_image_link.prependTo('.woocommerce-product-gallery__wrapper .thumbnails')
            // }
            
		})(jQuery);
    </script>
    <?php
}
add_action('wp_footer', 'add_custom_js', 100);

// Jared - Change Upsell text
function my_text_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'You may also like&hellip;' :
            $translated_text = __( 'Complementary Products', 'woocommerce' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'my_text_strings', 20, 3 );

// Jared - Change number of related products
function avia_chance_wc_related_columns(){
	global $avia_config;
	$avia_config['shop_single_column'] 	 	 = 0;			// columns for related products and upsells
	$avia_config['shop_single_column_items'] = 8;	// number of items for related products and upsells
}
add_action('wp_head', 'avia_chance_wc_related_columns', 10);

// Jared - Information below short description
// add_action( 'woocommerce_before_add_to_cart_form', 'product_information', 5 );
 
function product_information() {
	global $product;
	$product_id = $product->get_id();
	
	$material_safety_data_sheet = get_field('material_safety_data_sheet',$product_id);
	$technical_specification = get_field('technical_specification',$product_id);
	?>
	<div class="woocommerce-product-details__short-description">
      <?php echo $product->get_description();?>
    </div>
	
    <div class="document-links">
      <a href="<?php echo $material_safety_data_sheet; ?>" target="_blank" class="btn" ><small>Download PDF</small>Material Safety Data Sheet</a>
      <a href="<?php echo $technical_specification; ?>" target="_blank" class="btn"><small>Download PDF</small>Technical Specification</a>
    </div>
	<?php
$attribute_keys = array_keys($attributes);
foreach ($attributes as $attribute_name => $options){
	
	$selected = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)]) ? wc_clean(stripslashes(urldecode($_REQUEST['attribute_' . sanitize_title($attribute_name)]))) : $product->get_variation_default_attribute($attribute_name);
	
	if ($attribute_name == 'pa_color') {
        $koostis = $product->get_attribute('pa_color');
        $colors_arr = explode(",", $koostis);
        foreach ($colors_arr as $pos => $color) {
            $color_obj = get_term_by('name', $color, 'pa_color');
            $color_code1 = get_field('color1', $color_obj, true);
            $color_code2 = get_field('color2', $color_obj, true);
            $pattern = get_field('pattern', $color_obj, true);
			$checked = "";
			if($color_obj->slug == $_REQUEST['attribute_pa_color']){
				$checked = "checked";
				$selected_name = $color_obj->name;
				$selected_color1 = $color_code1;
				$selected_color2 = $color_code2;
				$selected_pattern = $pattern;
			}
            $color_html .= '<li class="'.($checked ? "active" : "").'"><a data-avia-tooltip="'.$color_obj->name.'" style="background-color:' . $color_code1 . ';" href="#"><span>color 1</span></a><input '.$checked.' name="attribute_pa_color" value="' . $color_obj->slug . '" id="color-' . $pos . '" class="hidden" type="radio"></li>';
        }
    }
}
?>
	
    <div class="color-section">
      <label><strong class="color-black">Color Selected:</strong> <span class="color-text"><?=$selected_name?></span></label>
      <ul class="color-list">
        <?php echo $color_html; ?>
      </ul>
    </div>
    <div class="size-section">
      <label><strong class="color-black">Size:</strong></label>
      <div class="checkbox-list">
        <label class="checkbox-inline" for="checkboxes-0">
          8 <input class="option-input checkbox" name="size" id="checkboxes-0" value="1" type="checkbox">
        </label>
        <label class="checkbox-inline" for="checkboxes-1">
          9 <input class="option-input checkbox" name="size" id="checkboxes-1" value="2" type="checkbox">
        </label>
        <label class="checkbox-inline" for="checkboxes-2">
          10 <input class="option-input checkbox" name="size" id="checkboxes-2" value="3" type="checkbox">
        </label>
        <label class="checkbox-inline" for="checkboxes-3">
          11 <input class="option-input checkbox" name="size" id="checkboxes-3" value="4" type="checkbox">
        </label>
        <label class="checkbox-inline" for="checkboxes-4">
          12 <input class="option-input checkbox" name="size" id="checkboxes-4" value="5" type="checkbox">
        </label>
      </div>
    </div>
    <?php
}
//Cart Page and Table
function hide_coupon_field_on_cart( $enabled ) {
  if ( is_cart() && ! is_user_logged_in() ) {
    $enabled = false;
  }
  return $enabled;
}

add_filter( 'woocommerce_coupons_enabled', 'hide_coupon_field_on_cart' );

add_action( 'woocommerce_cart_collaterals', 'remove_cart_totals', 9 );
function remove_cart_totals(){
    // Remove cart totals block
	if (! is_user_logged_in() ){
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
	}
}

add_action( 'woocommerce_before_cart_table', 'hide_cart_columns' );
function hide_cart_columns() {
	if (! is_user_logged_in() ){
		echo "<style type='text/css'> .woocommerce table.cart td .product-price, .woocommerce table.cart .product-price {display: none;} </style>";
		echo "<style type='text/css'> .woocommerce table.cart td .product-subtotal, .woocommerce table.cart .product-subtotal {display: none;} </style>";
	}
}

//ADD TO CART FUNCTION
add_action('wp_footer', 'my_custom_wc_button_script', 99);
function my_custom_wc_button_script() {
	?>
	<script>
		jQuery(document).ready(function($) {
			var ajaxurl = "<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>";
			$( document.body).on('click', '.my-custom-add-to-cart-button', function(e) {
				//Check-Not working
				// showOverlay();
				e.preventDefault();
				var $this = $(this);
				if( $this.is(':disabled') ) {
					return;
				}
				var $id = $(this).data("product-id");
				var $qty = $("#quantity_" + $id).val();
				var data = {
					action     : 'my_custom_add_to_cart',
					product_id : $id, 
					qty: $qty
				};
				$.post(ajaxurl, data, function(response) {
					if( response.success ) {
						$this.text("Product Added");
						$this.attr('disabled', 'disabled');
						$('.woocommerce-message').removeClass('hidden');
						//Check-Not working
						// hideOverlay();
						$( document.body ).trigger( 'wc_fragment_refresh' );
					}
				}, 'json');
			})
		});
	</script>
	<?php
}
add_action('wp_ajax_my_custom_add_to_cart', "my_custom_add_to_cart");
add_action('wp_ajax_nopriv_my_custom_add_to_cart', "my_custom_add_to_cart");
function my_custom_add_to_cart() {
	$retval = array(
		'success' => false,
		'message' => ""
	);
	if( !function_exists( "WC" ) ) {
		$retval['message'] = "woocommerce not installed";
	} elseif( empty( $_POST['product_id'] ) ) {
		$retval['message'] = "no product id provided";
	} else {
		$product_id = $_POST['product_id'];
		$qty = $_POST['qty'];
		if( my_custom_cart_contains( $product_id ) ) {
			$retval['message'] = "product already in cart";
		} else {
			$cart = WC()->cart;
			$retval['success'] = $cart->add_to_cart( $product_id , $qty);
			if( !$retval['success'] ) {
				$retval['message'] = "product already in the cart";
			} else {
				$retval['message'] = "product added to cart";
			}
		}
	}
	echo json_encode( $retval );
	wp_die();
}
function my_custom_cart_contains( $product_id ) {
	$cart = WC()->cart;
	$cart_items = $cart->get_cart();
	if( $cart_items ) {
		foreach( $cart_items as $cart_item_key => $item ) {
			$product = $item['data'];
			
			$variation_id = $item['variation_id'];
			
			if($variation_id && $product_id == $variation_id){
				return true;
			}elseif( $product_id == $product->id ) {
				return true;
			}
		}
	}
	return false;
}



// Jared - Place Order Table
add_action( 'woocommerce_after_single_product_summary', 'place_order_table', 5 );
 
function place_order_table() {
	global $product, $post;
	$product_variations = $product->get_available_variations();
	$product_id = $post->ID;
	foreach ($product_variations as $_variation) {
		$SKUinSTR .= "'".$_variation['sku']."',";
	}
	$SKUinSTR = rtrim($SKUinSTR,",");
	
	
	
	//Get Array Value from Index
	
	
	if (is_user_logged_in()) {
		//$Branch = "CPT"; // // When the user is logged in.
		$user_id = get_current_user_id(); 
		$key = 'city'; 
		$Branch = get_user_meta( $user_id, $key, true );
	}
	// else {
		// $Branch = "JHB"; // // Closest store for non-logged in user.
	// }
	
	if ($SKUinSTR != '') {
		$StockBySKU = get_gfox_stock($Branch,$SKUinSTR);
	}
	
	//echo "<pre>";
	//prinnt_r($StockBySKU);
	//echo "</pre>";
	
	//echo "<br />Branch is:" .$Branch; 
	//echo "<br />xxx: $StockBySKU";
	
	
	if (is_user_logged_in()) {
		$key = 'account_code';
		$CustomerCode = get_user_meta( $user_id, $key, true ); // // When the user is logged in, we will know which CustomerCode he belongs to.  That will replace this valuie
		$ThisCustomer = get_gfox_customer($CustomerCode, $Branch);
		if ($SKUinSTR != '') {
			$PriceBySKU = get_gfox_product_price ($Branch,$CustomerCode,$SKUinSTR);
		}
	}
	?>

	<div class="order-container hidden">
	<?php
	//AJAX ADD TO CART BUTTON
	if( !is_user_logged_in()) {
		?>
	<div class="woocommerce-message hidden" role="alert">Quote updated. <a href="https://gfox.zerobox.co.za/cart/" class="button wc-forward">View quote</a></div>
	<?php
	} else {
	?>
	<div class="woocommerce-message hidden" role="alert">Cart updated. <a href="https://gfox.zerobox.co.za/cart/" class="button wc-forward">View cart</a></div>
	<?php
	}
	?>
      <h2 class="font-weight-100">Place an Order</h2>
      <table class="responsive-table" width="100%">
        <thead>
          <tr>
            <th data-breakpoints="xs">Stock Code</th>
            <th data-breakpoints="xs">Colour</th>
            <th data-breakpoints="xs">Size</th>
            <th data-breakpoints="xs" class="<?= is_user_logged_in() ? : 'hidden' ?>">Price (Ex VAT)</th>
            <th data-breakpoints="xs" class="<?= is_user_logged_in() ? : 'hidden' ?>">Qty Available* <?= is_user_logged_in() ? "($Branch)" : null ?></th>
            <th data-breakpoints="xs" style="text-align: center;">Quantity</th>
            <th class="hidden-xs" data-breakpoints="xs"></th>
          </tr>
        </thead>
        <tbody>
        <?php
        $add_to_cart_label = is_user_logged_in() ? 'Add to Cart' : 'Add to Order Basket';
        foreach ($product_variations as $_variation) {
            // Only show items that are in stock and can be purchased
            if(!$_variation['is_in_stock'] && !$_variation['is_purchasable']) {
                continue;
            }
            $_attribute_pa_size = $_variation['attributes']['attribute_pa_size'];
            $_attribute_pa_color = $_variation['attributes']['attribute_pa_color'];
			
			// When the user is logged in, we will know which branch he belongs to.  That will replace the JHB below
			//$max_qty = $_variation['max_qty'];
			$max_qty = (is_user_logged_in() ? $StockBySKU[$_variation['sku']]['AvailableQty'] : 1000);
			$_variation['display_price'] = round($PriceBySKU[$_variation['sku']]['NetPrice'],2);
            ?>
          <tr class="variations_row hidden" data-size="<?= $_attribute_pa_size ?>" data-color="<?= strtolower($_attribute_pa_color) ?>">
            <td><?= $_variation['sku'] ?></td>
            <td class="color_column"><?= $_attribute_pa_color ?></td>
            <td class="text-uppercase"><?= $_attribute_pa_size ?></td>
            <td class="<?= is_user_logged_in() ? : 'hidden' ?>"><strong><?=$ThisCustomer['Currency']?> <?= number_format($_variation['display_price'],2); ?></strong></td>
            <td class="max_qty <?= is_user_logged_in() ? : 'hidden' ?>" data-max-qty="<?= $max_qty ?>"><?= $max_qty ?></td>
            <td style="text-align: center;">
				
				<?
				if($max_qty == 0 || my_custom_cart_contains( $_variation['variation_id'] )){
					echo "N/A";
				}else{
					?>
					<input type="hidden" name="variation_id" value="<?= $_variation['variation_id'] ?>" />
					<input type="hidden" name="product_id" value="<?= esc_attr( $product_id ) ?>" />
					<div class="quantity">
						<label class="screen-reader-text" for="">Quantity</label>
						<input value="-" class="minus" type="button" data-field="quantity_<?= $_variation['variation_id'] ?>"><input readonly id="quantity_<?= $_variation['variation_id'] ?>" class="input-text qty text" step="1" min="1" max="<?= $max_qty ?>" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" aria-labelledby="" type="text"><input value="+" class="plus" type="button" data-field="quantity_<?= $_variation['variation_id'] ?>">
					 </div>
					<?
				}
				?>
			</td>
			<td align="right">
					<?php
					//AJAX ADD TO CART BUTTON
					if($max_qty == 0){
							//echo "N/A";
					}elseif( !my_custom_cart_contains( $_variation['variation_id'] ) ) {				
						
						?>
						<button type="button" name="add-to-cart" class="single_add_to_cart_button button alt margin-0 my-custom-add-to-cart-button"  data-product-id="<?php echo $_variation['variation_id']; ?>"><?= $add_to_cart_label ?></button>
						<?
						
					} else {
							?>
							<button class="single_add_to_cart_button button alt margin-0 my-custom-add-to-cart-button" data-product-id="<?php echo $_variation['variation_id']; ?>" disabled="disabled">Product Added</button>
						<?php
					}
					?>
				
			</td>
			  </tr>
			  															
        <?php } ?>
        </tbody>
      </table>
      <?php 
		if (is_user_logged_in()) {
			echo do_shortcode("[stock_update]");
		}
		?>
    </div>
    <?php
}

add_action('gform_pre_submission_2', 'pre_submission_handler');
function pre_submission_handler($form) {
    //========== GLOBALIZE THE CART VARIABELS ============== 
    global $product, $woocommerce_loop, $woocommerce;
    //========== GET CART ITEMS ============== 
    $items = $woocommerce->cart->get_cart();
    $cart_items = '';

    $count = 1;
	$cart_items_arr = array();
    foreach ($items as $item => $values) {
        $_product = $values['data']->post;
		$_product_object = wc_get_product($_product->ID);
		$cart_items_arr[] = '['.$_product_object->sku . ' - Quantity: ' . $values['quantity'] . "]";
        $count++;
    }
    //========== PREPOPULATE THE CART ITEMS INTO THIS FIELD ============== 
    $_POST['input_4'] = implode(", ", $cart_items_arr);
}

//Remove Proceed to Checkout Button and Change View Cart Text
if( ! is_user_logged_in() ){
		remove_action( 'woocommerce_proceed_to_checkout','woocommerce_button_proceed_to_checkout', 20);
		
		add_filter('wc_add_to_cart_message', 'wc_add_to_cart_message_handler', 10, 2);
		function wc_add_to_cart_message_handler($message, $product_id) {
			//========== CHANGE SINGLE PRODUCT ADD TO CART MESSAGE ============== 
			return 'Quote updated. <a href="' . esc_url(wc_get_cart_url()) . '" class="button wc-forward">' . esc_html__('View quote', 'woocommerce') . '</a>';
		}
	}


/**
 * Enable ACF 5 early access
 * Requires at least version ACF 4.4.12 to work
 */
define('ACF_EARLY_ACCESS', '5');


/**
 * Export
 */
/**
* Add CSV columns for exporting extra data.
*
* @param  array  $columns
* @return array  $columns
*/
function kia_add_columns( $columns ) {
	$columns[ 'custom_taxonomy_brands' ] = __( 'Brands','custom-post-type-ui' );
	$columns[ 'custom_taxonomy_icons' ] = __( 'Icons','custom-post-type-ui' );
	
	return $columns;
	
}
add_filter( 'woocommerce_product_export_column_names', 'kia_add_columns' );
add_filter( 'woocommerce_product_export_product_default_columns', 'kia_add_columns' );
/**
* MnM contents data column content.
*
* @param  mixed       $value
* @param  WC_Product  $product
* @return mixed       $value
*/
function kia_export_taxonomy_brands( $value, $product ) {
	$terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'brands' ) );
	if ( ! is_wp_error( $terms ) ) {
		$data = array();
		foreach ( (array) $terms as $term ) {
			$data[] = $term->name;
		}
		$value = json_encode( $data );
	}
	return $value;
}

function kia_export_taxonomy_icons( $value, $product ) {
	$terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'icons' ) );
	if ( ! is_wp_error( $terms ) ) {
		$data = array();
		foreach ( (array) $terms as $term ) {
			$data[] = $term->name;
		}
		$value = json_encode( $data );
	}
	return $value;
}
add_filter( 'woocommerce_product_export_product_column_custom_taxonomy_brands', 'kia_export_taxonomy_brands', 10, 2 );
add_filter( 'woocommerce_product_export_product_column_custom_taxonomy_icons', 'kia_export_taxonomy_icons', 10, 2 );


/**
 * Import
 */
/**
 * Register the 'Custom Column' column in the importer.
 *
 * @param  array  $options
 * @return array  $options
 */
 
 
function kia_map_columns( $options ) {
	$options[ 'custom_taxonomy_brands' ] = __( 'Brands', 'custom-post-type-ui' );
	$options[ 'custom_taxonomy_icons' ] = __( 'Icons', 'custom-post-type-ui' );
	
	return $options;
}
add_filter( 'woocommerce_csv_product_import_mapping_options', 'kia_map_columns' );
/**
 * Add automatic mapping support for custom columns.
 *
 * @param  array  $columns
 * @return array  $columns
 */
function kia_add_columns_to_mapping_screen( $columns ) {
	$columns[ __( 'Brands', 'custom-post-type-ui' ) ] 	= 'custom_taxonomy_brands';
	$columns[ __( 'Icons', 'custom-post-type-ui' ) ] 	= 'custom_taxonomy_icons';
	
	// Always add English mappings.
	$columns[ 'Brands' ]	= 'custom_taxonomy_brands';
	$columns[ 'Icons' ]	= 'custom_taxonomy_icons';
	
	return $columns;
}
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'kia_add_columns_to_mapping_screen' );
/**
 * Decode data items and parse JSON IDs.
 *
 * @param  array                    $parsed_data
 * @param  WC_Product_CSV_Importer  $importer
 * @return array
 */
function kia_parse_taxonomy_json( $parsed_data, $importer ) {
	
	$custom_taxonomy_cols = array('custom_taxonomy_brands', 'custom_taxonomy_icons');
	foreach($custom_taxonomy_cols as $col_key){
		if ( ! empty( $parsed_data[ $col_key ] ) ) {
			$data = json_decode( $parsed_data[ $col_key ], true );
			unset( $parsed_data[ $col_key ] );
			if ( is_array( $data ) ) {
				$parsed_data[ $col_key ] = array();
				foreach ( $data as $term_name ) {
					$term_id = $term_name;
					$parsed_data[ $col_key ][] = $term_id;
					
				}
			}
		}
	}	
	return $parsed_data;
	
}
add_filter( 'woocommerce_product_importer_parsed_data', 'kia_parse_taxonomy_json', 10, 2 );
/**
 * Set taxonomy.
 *
 * @param  array  $parsed_data
 * @return array
 */
function kia_set_taxonomy( $product, $data ) {
	
	$terms = get_terms( array( 'taxonomy' => array('brands', 'icons') ,'hide_empty' => false) );
	
	if ( ! is_wp_error( $terms ) ) {
		$data_all_tax_terms_arr = array();
		foreach ( (array) $terms as $term ) {
			
			$data_all_tax_terms_arr[$term->name] = $term->term_id;
		}
	}
	wp_set_object_terms( $product->get_ID(), 0, 'brands', false );
	wp_set_object_terms( $product->get_ID(), 0, 'icons', false );
	
	if ( is_a( $product, 'WC_Product' ) ) {
		if( ! empty( $data[ 'custom_taxonomy_brands' ] ) ) {
			
			foreach($data[ 'custom_taxonomy_brands' ] as $excel_tax_term){
				$term_id = $data_all_tax_terms_arr[$excel_tax_term];
				$results = wp_set_object_terms( $product->get_ID(), $term_id, 'brands', true );
			}
			
		}
		if( ! empty( $data[ 'custom_taxonomy_icons' ] ) ) {
			
			foreach($data[ 'custom_taxonomy_icons' ] as $excel_tax_term){		
				$term_id = $data_all_tax_terms_arr[$excel_tax_term];
				$results = wp_set_object_terms( $product->get_ID(), $term_id, 'icons', true );
			}
			
		}
	}
	
	return $product;
}
add_filter( 'woocommerce_product_import_inserted_product_object', 'kia_set_taxonomy', 10, 2 );

//Add Brands Logo
function add_woocommerce_brands($product){
	$terms = get_the_terms( $product->get_id , 'brands');
	if(is_array($terms)){
	foreach ( $terms as $term ) {
    echo '<img class="brand-image" src="' . get_field( 'brand_logo', $term ) . '">';
	}
	}
}
add_action( 'woocommerce_product_thumbnails', 'add_woocommerce_brands', 20 );

//============ lets get all the custom functionality ===================== 
$dir = get_stylesheet_directory()."/_custom_include_functions/";
foreach (glob($dir . "*.php") as $function) {
    require $dir . basename($function);
}





/**
 * Plugin Name: T5 Embed Post Shortcode
 * Description: Embed any page, post or custom post type with shortcode.
 * Plugin URI:  http://wordpress.stackexchange.com/q/62156/73
 * Version:     2012.08.17
 * Author:      Thomas Scholz
 * Author URI:  http://toscho.de
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 *
 * T5 Embed Page Shortcode, Copyright (C) 2012 Thomas Scholz
 */

add_shortcode( 'promo_template', 't5_embed_post' );

/**
 * Get a post per shortcode.
 *
 * @param  array $atts There are three possible attributes:
 *         id: A post ID. Wins always, works always.
 *         title: A page title. Show the latest if there is more than one post
 *              with the same title.
 *         type: A post type. Only to be used in combination with one of the
 *              first two attributes. Might help to find the best match.
 *              Defaults to 'page'.
 * @return string
 */
function t5_embed_post( $atts )
{
    extract(
        shortcode_atts(
            array (
                'id'    => 77,
                'title' => FALSE,
                'type'  => 'page'
            ),
            $atts
        )
    );

    // Not enough input data.
    if ( ! $id and ! $title )
    {
        return;
    }

    $post = FALSE;

    if ( $id )
    {
        $post = get_post( $id );
    }
    elseif( $title )
    {
        $post = get_page_by_title( $title, OBJECT, $type );
    }

    // Nothing found.
    if ( ! $post )
    {
        return;
    }

    return apply_filters( 'the_content', $post->post_content );
}


// Add Variation Settings
add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );
// Save Variation Settings
add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );
/**
 * Create new fields for variations
 *
*/
function variation_settings_fields( $loop, $variation_data, $variation ) {
	// Text Field
	woocommerce_wp_text_input( 
		array( 
			'id'          => 'packaging[' . $variation->ID . ']', 
			'label'       => __( 'Packaging', 'woocommerce' ), 
			// 'placeholder' => 'http://',
			// 'desc_tip'    => 'true',
			// 'description' => __( 'Enter the custom value here.', 'woocommerce' ),
			'value'       => get_post_meta( $variation->ID, 'packaging', true )
		)
	);
}

/**
 * Save new fields for variations
 *
*/
function save_variation_settings_fields( $post_id ) {
	// Text Field
	$packaging_field = $_POST['packaging'][ $post_id ];
	if( ! empty( $packaging_field ) ) {
		update_post_meta( $post_id, 'packaging', esc_attr( $packaging_field ) );
	}
}

// Add New Variation Settings
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );
/**
 * Add custom fields for variations
 *
*/
function load_variation_settings_fields( $variations ) {
	
	// duplicate the line for each field
	$variations['packaging_field'] = get_post_meta( $variations[ 'variation_id' ], 'packaging', true );
	
	return $variations;
}

//Add Icons Image
function add_icon_image($product){
	$args = array( 
        'post_type' => 'product', 
        );
		$product = new WP_Query( $args );

	if (is_object($product)) {
		$terms = get_the_terms( $product->get_id , 'icons');
		if (is_array($terms)) {
			echo '<aside class="sidebar ">';
			echo '<div class="iconrow">';
			foreach ( $terms as $term ) {
				if (get_field( 'icon_image', $term )){	
				echo '<div class="iconcolumn">';
				
				$image_path = get_field( 'icon_image', $term );
				
				$siteurl = get_site_url();
				
				$path = ABSPATH;
				
				$new_path = str_replace($siteurl, $path, $image_path);
				
				list($width, $height) = getimagesize($new_path);
				
				echo '<img width="'.$width.'" height="'.$height.'" id="portraitLandscape" alt="'. $term->name .'" class="portraitLandscape" src="' . get_field( 'icon_image', $term ) . '">';
				echo '</div>';
				}
				else{
				echo '<div class="iconcolumn">';
				echo '<span class="">'. $term->name .'</span>';
				echo '</div>';	
				}				
			}
			echo '</div>';
			echo '</aside>';
		}
	}
}
add_action( 'woocommerce_before_single_product_summary', 'add_icon_image', 5 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

function stock_update (){
	global $wpdb;
	$SQL = "SELECT MAX(LastImportDate) as LastImportDate
			FROM
				gfox_StockInfoImportHistory
	";
	$Line = $wpdb->get_row( $wpdb->prepare($SQL), ARRAY_A);
	echo '<p class="margin-top-0"><small><strong class="color-black">* Stock update as of '.date("d F Y H:i:s",strtotime($Line['LastImportDate'])).'</strong></small></p>';
}
add_shortcode('stock_update', 'stock_update');

// add_action( 'woocommerce_single_product_summary', 'brand_name', 60 );
 
// function brand_name() {
// $args = array( 
        // 'post_type' => 'product', 
        // );
		// $product = new WP_Query( $args );

	// if (is_object($product)) {
		// $terms = get_the_terms( $product->get_id , 'brands');
		// if (is_array($terms)) {
			// echo 'Brands:  ';
			// foreach ( $terms as $term ) {
			// $term_link = get_term_link( $term, 'brands' );
				// echo '<span class=""><a href="'. $term_link .'"> '. $term->name .', </a> </span>';
			// }

		// }
	// }
// }


function get_gfox_customer($CustomerCode, $Branch) {
	global $wpdb;
	$SQL = "SELECT *
			FROM
				gfox_CustomerMaster
			WHERE
				Customer = '".$CustomerCode."'
			AND
				Branch = '".$Branch."'
	";
	$Data = $wpdb->get_row( $wpdb->prepare($SQL), ARRAY_A);
	return $Data;
}

function get_gfox_stock ($Branch,$SKUinSTR) {
	global $wpdb;
	$SQL = "SELECT Branch, StockCode, AvailableQty, StockUom, ProductClass, 
				   TaxCode, QtyOnHand, QtyAllocated, QtyOnBackOrder
			FROM
				gfox_StockInfo
			WHERE
				Branch = '".$Branch."'
			AND
				StockCode IN (".$SKUinSTR.")
			
	";
	//echo "<pre>";
	//echo $SQL;
	//echo "</pre>";exit;
	$Data = $wpdb->get_results( $wpdb->prepare($SQL), ARRAY_A);
	foreach ($Data as $Line) {
		$Results[$Line['StockCode']] = $Line;
	}
	return $Results;
}


function get_gfox_product_price ($Branch,$CustomerCode,$SKUinSTR) {
	
	global $wpdb;
	$SQL = "SELECT Branch, Customer, StockCode, Discount1, Discount2, Discount3, GrossPrice, Discount, NetPrice
			FROM
				gfox_ListPrices
			WHERE
				Branch = '".$Branch."'
			AND
				StockCode IN (".$SKUinSTR.")
			AND
				Customer = '".$CustomerCode."'
	";
	$Data = $wpdb->get_results( $wpdb->prepare($SQL), ARRAY_A);
	foreach ($Data as $Line) {
		$Prices[$Line['StockCode']] = $Line;
	}
	
	$SQL = "SELECT Branch, Customer, StockCode, Discount1, Discount2, Discount3, GrossPrice, Discount, NetPrice
			FROM
				gfox_ContractPrices
			WHERE
				Branch = '".$Branch."'
			AND
				StockCode IN (".$SKUinSTR.")
			AND
				Customer = '".$CustomerCode."'
	";
	$Data = $wpdb->get_results( $wpdb->prepare($SQL), ARRAY_A);
	foreach ($Data as $Line) {
		$StockCode = $Line['StockCode'];
		if ($Line['NetPrice'] < $Prices[$StockCode]['NetPrice']) {
			unset ($Prices[$StockCode]);
			$Prices[$StockCode] = $Line;
		}
	}
	
	
	return $Prices;
}

function generate_mapped_variations_images($variations) {
    $mapped_items = array();
	foreach ($variations as $_variation) {
        $mapped_items[$_variation['attributes']['attribute_pa_color']] = $_variation['image']['full_src'];
    }
    return $mapped_items;
}

//Add User Custom Fields on Account Page (Frontend)
add_action('um_after_account_general', 'show_extra_fields', 100);
function show_extra_fields() {

  $id = um_user('ID');
  $output = '';

  $names = array( 'fax_number','user_address','work_area','business_trading_name','business_legal_name','account_code','city','user_login','user_password');

  $fields = array();
  foreach( $names as $name )
    $fields[ $name ] = UM()->builtin()->get_specific_field( $name );
 $id = um_user('ID');
  $fields = apply_filters( 'um_account_secure_fields', $fields, $id );

  foreach( $fields as $key => $data )
    $output .= UM()->fields()->edit_field( $key, $data );

  echo $output;
}

function get_custom_fields( $fields ) {
  global $ultimatemember;
  $array=array();
  foreach ($fields as $field ) {
    if ( isset( UM()->builtin()->saved_fields[$field] ) ) {
      $array[$field] = UM()->builtin()->saved_fields[$field];
    } else if ( isset( UM()->builtin()->predefined_fields[$field] ) ) {
      $array[$field] = UM()->builtin()->predefined_fields[$field];
    }
  }
  return $array;
}


function work_areas() {
	$areas = array ('Finance and Administration','Management','Sales and Marketing','Other');
	$areas_arr = array();
	foreach($areas as $area){
		$areas_arr[$area] = $area;
	}
	return $areas_arr;
}

function cities() {
	 $cities = [
       'CPT' => 'Cape Town', 
		'DBN' => 'Durban', 
		'JHB' => 'Johannesburg', 
		'MBG' => 'MBG',
		'PE' =>  'Port Elizabeth', 
		'PTB' => 'Pietermaritzburg', 
		'VAAL' =>'Vaal'
	];
	return $cities;
}

//Add User Custom Fields on User Profile Page (Admin)
add_action( 'show_user_profile', 'display_user_custom_fields' );
add_action( 'edit_user_profile', 'display_user_custom_fields' );

function display_user_custom_fields( $user ) { 

?>
    <h3>Extra User Fields</h3>
    <table class="form-table">
        <tr>
            <th><label>Fax Number</label></th>
            <td><input name="fax_number" type="text" value="<?php echo get_user_meta( $user->ID, 'fax_number', true ); ?>" class="regular-text"/></td>
        </tr>
		<tr>
            <th><label>Address</label></th>
            <td>
			<textarea name="user_address"><?php $user_address = get_user_meta( $user->ID, 'user_address', true );?><?php echo ltrim($user_address);?></textarea>
			</td>
        </tr>
		<tr>
            <th><label>Work Area</label></th>
            <td>
			<select name="work_area" id="work_area">
			<option>Select One</option>
			<?php
			$selected_work_area = get_user_meta( $user->ID, 'work_area', true );
			$areas = work_areas();
			foreach($areas as $key => $area){
			?>
			<option <?php if ($area == $selected_work_area ) echo 'selected' ; ?> value="<?php echo $area; ?>"><?php echo $area; ?></option>
			
			<?php
			}
			?>
			</select>
			</td>
        </tr>
		<tr>
            <th><label>Business Trading Name</label></th>
            <td><input name="business_trading_name" type="text" value="<?php echo get_user_meta( $user->ID, 'business_trading_name', true ); ?>" class="regular-text"/></td>
        </tr>
		<tr>
            <th><label>Business Legal Name</label></th>
            <td><input name="business_legal_name" type="text" value="<?php echo get_user_meta( $user->ID, 'business_legal_name', true ); ?>" class="regular-text"/></td>
        </tr>
		<tr>
            <th><label>Account Code</label></th>
            <td><input name="account_code" type="text" value="<?php echo get_user_meta( $user->ID, 'account_code', true ); ?>" class="regular-text"/></td>
        </tr>
		<tr>
            <th><label>City</label></th>
            <td>
			<?php
			$selected_city = get_user_meta( $user->ID, 'city', true );
			$cities = cities();
			?>
			<select name="city" id="city">	
			<option>Select One</option>
			<?php
			
			foreach($cities as $key => $city){
			?>
			<option <?php if ($key == $selected_city ) echo 'selected' ; ?> value="<?php echo $key; ?>"><?php echo $city; ?></option>
			
			<?php
			}
			?>
			</select>
			</td>
        </tr>
    </table>
    <?php
}

 add_action( 'personal_options_update', 'update_user_custom_fields' );
 add_action( 'edit_user_profile_update', 'update_user_custom_fields' );
 function update_user_custom_fields($user_id) {

    if ( !current_user_can('edit_user', $user_id) ) {
		return false;
	}   

	
	$metas = array( 
		'fax_number'			=> $_POST['fax_number'],
		'user_address'			=> $_POST['user_address'],
		'business_trading_name'	=> $_POST['business_trading_name'],
		'business_legal_name'	=> $_POST['business_legal_name'],
		'account_code'			=> $_POST['account_code'],
		'work_area'     		=> $_POST['work_area'],
		'city'      			=> $_POST['city'],
		
	);
	foreach($metas as $key => $value) {
		update_user_meta( $user_id, $key, $value );
	}		
}

//CUSTOM GOOGLE ADDRESS AUTOCOMPLETE
function autocomplete_um(){
	if (is_page( 'register' ) ){
	?>
	<script>
	window.initAutocomplete = function () {
    new google.maps.places.Autocomplete(document.querySelector('#user_address-11588'), { types: ['geocode'] });}; 
    </script>
	<?php
	ob_start();
	
	$apiKey = 'AIzaSyB0p3Eu11ApOngj_y5RGPtl0ZuZwbkD_ec';    
    wp_enqueue_script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$apiKey}&libraries=places&callback=initAutocomplete", array(), false, true);

    wp_add_inline_script('google-maps', ob_get_clean(), 'before');	
	}
	if (is_page( 'request-quote-modal' ) ){
	?>
	<script>
	window.initAutocomplete = function () {
    new google.maps.places.Autocomplete(document.querySelector('#input_2_6_1'), { types: ['geocode'] });}; 
    </script>
	<?php
	ob_start();
	
	$apiKey = 'AIzaSyB0p3Eu11ApOngj_y5RGPtl0ZuZwbkD_ec';    
    wp_enqueue_script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$apiKey}&libraries=places&callback=initAutocomplete", array(), false, true);

    wp_add_inline_script('google-maps', ob_get_clean(), 'before');	
	}
	
}
add_action('wp_footer', 'autocomplete_um');





/**
 * Extend WordPress search to include custom fields
 *
 * https://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

// /**
 // * Prevent duplicates
 // *
 // * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 // */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );



add_filter( 'relevanssi_modify_wp_query', 'rlv_force_variation_product' );
function rlv_force_variation_product( $query ) {
    $query->query_vars['post_types'] = 'product,product_variation';
    return $query;
}


add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );
function my_maybe_woocommerce_variation_permalink( $permalink ) {
	if ( ! is_search() ) {
		return $permalink;
	}
	// check to see if the search was for a product variation SKU
	$sku = get_search_query();
	$args = array(
		'post_type'       => 'product_variation',
		'posts_per_page'  => 1,
		'fields'          => 'ids',
		'meta_query'      => array(
			array(
				'key'     => '_sku',
				'value'   => $sku,
			),
		),
	);
	$variation = get_posts( $args );
	// make sure the permalink we're filtering is for the parent product
	if ( get_permalink( wp_get_post_parent_id( $variation[0] ) ) !== $permalink ) {
		return $permalink;
	}
	if ( ! empty( $variation ) && function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
		// this is a variation SKU, we need to prepopulate the filters
		$variation_id = absint( $variation[0] );
		$variation_obj = new WC_Product_Variation( $variation_id );
		$attributes = $variation_obj->get_variation_attributes();
		if ( empty( $attributes ) ) {
			return $permalink;
		}
		$permalink = add_query_arg( $attributes, $permalink );
	}
	return $permalink;
}
add_filter( 'the_permalink', 'my_maybe_woocommerce_variation_permalink' );

//Relevanssi in Ajax Search
add_filter('avf_ajax_search_function', 'avia_init_relevanssi', 10, 4);
function avia_init_relevanssi($function_name, $search_query, $search_parameters, $defaults)
{
    $function_name = 'avia_relevanssi_search';
    return $function_name;
}

function avia_relevanssi_search($search_query, $search_parameters, $defaults)
{
    global $query;
    $tempquery = $query;
    if(empty($tempquery)) $tempquery = new WP_Query();

    $tempquery->query_vars = $search_parameters;
    relevanssi_do_query($tempquery);
    $posts = $tempquery->posts;

    return $posts;
}

// add_filter('relevanssi_punctuation_filter', 'remove_slashes' );
// function remove_slashes($filters) {
    // $filters['/'] = '';
    // return $filters;
// }

add_action('wp_footer', 'clearcart', 98);
function clearcart() {
	?>
	<script>
		jQuery(document).ready(function($) {
			/*
			var ajaxurl = "<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>";
			$( document.body).on('click', '.my-custom-add-to-cart-button', function(e) {
				e.preventDefault();
				var $this = $(this);
				if( $this.is(':disabled') ) {
					return;
				}
				var id = $(this).data("product-id");
				var data = {
					action     : 'my_custom_add_to_cart',
					product_id : id
				};
				$.post(ajaxurl, data, function(response) {
					if( response.success ) {
						$this.text("Product Added");
						$this.attr('disabled', 'disabled');
						$('.woocommerce-message').removeClass('hidden');
						$( document.body ).trigger( 'wc_fragment_refresh' );
					}
				}, 'json');
			})*/
		});
	</script>
	<?php
}

//Clear Cart on request quote submit 
add_action('wp_ajax_nopriv_clearcart', 'woocommerce_clear_cart_url');
add_action('gform_after_submission_2', 'woocommerce_clear_cart_url', 10, 2 );
function woocommerce_clear_cart_url() {
		WC()->cart->empty_cart();
		?>
				<script>
					window.parent.location = '/quote-requested/';
				</script>
				<?	
				exit;
}


add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_catalog_ordering_args', 20, 1 );
function custom_catalog_ordering_args( $args ) {
   $args['orderby'] = 'ID';
   return $args;
    /*if( $args['orderby'] == 'ID' )
	$args['order'] = 'ASC'; // Set order by DESC
	//if( $args['meta_key'] )
	//  unset($args['meta_key']);
	*/
}

//Remove Price on Category Pages
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

add_filter('avia_product_slide_query', 'avia_product_slide_query_mod', 10, 2);
function avia_product_slide_query_mod($query, $params) {
	if(is_search()) {
		$query['s'] = $_GET['s'];
	}
	
	return $query;
}

function toggle_login (){
	if (is_user_logged_in() ){
		$output = '<a href="/logout/" class="avia-button  avia-icon_select-no avia-color-light avia-size-medium " style="margin-bottom:5px; margin-left:5px; "><span class="avia_iconbox_title">Logout</span></a>';
	}
	else{
		$output =  '<a href="/login/" class="avia-button  avia-icon_select-no avia-color-light avia-size-medium " style="margin-bottom:5px; margin-left:5px; "><span class="avia_iconbox_title">Login</span></a>';
	}
	return $output;
}
add_shortcode('toggle_login', 'toggle_login');
