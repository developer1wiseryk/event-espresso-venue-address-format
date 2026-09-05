<?php
// Options
$date_format		= get_option( 'date_format' );
$time_format		= get_option( 'time_format' );
// Load Venue View Helper
EE_Registry::instance()->load_helper('Venue_View');
//Defaults
$reg_button_text		= !isset($reg_button_text) ? __('Register', 'event_espresso') : $reg_button_text;
$alt_button_text		= !isset($alt_button_text) ? __('View Details', 'event_espresso') : $alt_button_text;//For alternate registration pages
$sold_out_button_text	= !isset($sold_out_button_text) ? __('Sold Out', 'event_espresso') : $sold_out_button_text;//For sold out events
$category_filter_text	= !isset($category_filter_text) ? __('Category Filter', 'event_espresso') : $category_filter_text;

if ( have_posts() ) :
	// allow other stuff
	do_action( 'AHEE__espresso_events_table_template_template__before_loop' );
	?>

	<?php if ($category_filter != 'false'){ ?>
	<p class="category-filter">
		<label><?php echo $category_filter_text; ?></label>
		<select class="" id="ee_filter_cat" aria-label="Filter">
		<option class="ee_filter_show_all"><?php echo __('Show All', 'event_espresso'); ?></option>
		<?php
		$taxonomy = array('espresso_event_categories');
		$args = array('orderby'=>'name','hide_empty'=>true);
		$ee_terms = get_terms($taxonomy, $args);

		foreach($ee_terms as $term){
			echo '<option class="' . $term->slug . '">'. $term->name . '</option>';
		}
	    ?>
		</select>
	</p>
	<?php } ?>

	<?php if ($footable != 'false' && $table_search != 'false'){ ?>
	<p>
        <?php echo __('Search:', 'event_espresso'); ?> <input id="filter" type="text"/>
    </p>
    <?php } ?>
	
	<style>
            	
        @media only screen and (max-width: 767px) {
            .footable > tbody > tr > td {
                padding: 3px !important;
            }

            .footable {
                font-size: 12px !important;
            }

            a.a_register_link {
                font-size: 12px !important;
            }
			
			select#ee_filter_cat{
				max-width:100% !important;
			}
        }

     </style>

	<table id="ee_filter_table" class="espresso-table footable table" data-page-size="<?php echo $table_pages; ?>" data-filter="#filter">
	<thead class="espresso-table-header-row">
		<tr>
			<th class="th-group"><?php _e('Event','event_espresso'); ?></th>
			<?php if( $show_venues ) { ?>
				<th class="th-group"><?php _e('Venue','event_espresso'); ?></th>
			<?php } ?>
            <!--<th class="th-group"><?php _e('Spaces Remaining','event_espresso'); ?></th>-->
			<th class="th-group"><?php _e('Date','event_espresso'); ?></th>
			<th class="th-group" data-sort-ignore="true"><?php _e('Spaces Remaining','event_espresso'); ?></th>
		</tr>
	</thead>
	<?php if ($footable != 'false' && $table_paging != 'false'){ ?>
	<tfoot>
		<tr>
		<?php echo '<td colspan="' . ($show_venues ? '5' : '4') . '">'; ?> 
				<div class="pagination pagination-centered"></div>
			</td>
		</tr>
	</tfoot>
	<?php } ?>
	<tbody>

	<?php
	// Start the Loop.
	while ( have_posts() ) : the_post();
		// Include the post TYPE-specific template for the content.
		global $post;

		//Debug
		//d( $post );

		//Get the category for this event
		$event = EEH_Event_View::get_event();
		
		
        // Hide Expired Events Code Start
        
        if ( ! $event instanceof EE_Event ) {
            continue;
        }
        
        // 1. Hide event if Event Espresso considers it expired.
        if ( $event->is_expired() ) {
            continue;
        }
        
        // 2. Hide event if ALL tickets have expired.
        // This fixes events where ticket sales have ended
        // but the Event Espresso datetime has not ended yet.
        $active_tickets = $event->active_tickets();
        
        if ( empty( $active_tickets ) ) {
            continue;
        }
        
        // Hide Expired Events Code End
		
		if ( $event instanceof EE_Event ) {
			if ( $event_categories = get_the_terms( $event->ID(), 'espresso_event_categories' )) {
				// loop thru terms and create links
				$category_slugs = array();
				foreach ( $event_categories as $term ) {
					$category_slugs[] = $term->slug;
				}
				$category_slugs = implode(' ', $category_slugs);
			} else {
				// event has no terms
				$category_slugs = '';
			}

		}
		//Create the event link
		$external_url 		= $post->EE_Event->external_url();
		$button_text		= !empty($external_url) ? $alt_button_text : $reg_button_text;
		$registration_url 	= !empty($external_url) ? $post->EE_Event->external_url() : $post->EE_Event->get_permalink();

		//Create the register now button
		$live_button 		= '<a id="a_register_link-'.$post->ID.'" class="a_register_link" href="'.$registration_url.'">'.$button_text.'</a>';

		if ( $event->is_sold_out() ) {
			$live_button	= '<a id="a_register_link-'.$post->ID.'" class="a_register_link_sold_out a_register_link" href="'.$registration_url.'">'.$sold_out_button_text.'</a>';
		}

// 		// If the show_all_datetimes parameter is set set the limit to NULL to pull them all,
// 		// if not default to only display a single datetime.
// 		$datetime_limit = $show_all_datetimes ? NULL : 1;

// 		// Pull the datetimes for this event order by start_date/time
// 		$datetimes = EEM_Datetime::instance()->get_datetimes_for_event_ordered_by_start_time( $post->ID, $show_expired, false, $datetime_limit );

// 		// Reset the datetimes pointer to the earliest datetime and use that one.
// 		$datetime = reset( $datetimes );
//         if ($datetime instanceof EE_Datetime) {

        // If show_all_datetimes is enabled get all,
        // otherwise only retrieve the first datetime.
        $datetime_limit = $show_all_datetimes ? NULL : 1;
        
        // ALWAYS exclude expired Event Espresso datetimes.
        // We use boolean false directly instead of relying on
        // the shortcode variable.
        $datetimes = EEM_Datetime::instance()->get_datetimes_for_event_ordered_by_start_time(
            $post->ID,
            false, // include_expired = FALSE
            false, // include_deleted = FALSE
            $datetime_limit
        );
        
        // No valid non-expired datetimes = don't show event.
        if ( empty( $datetimes ) ) {
            continue;
        }
        
        // Get the first valid datetime.
        $datetime = reset( $datetimes );
        
        // Extra safety check.
        if (
            ! $datetime instanceof EE_Datetime ||
            $datetime->is_expired()
        ) {
            continue;
        }
        
        if ( $datetime instanceof EE_Datetime ) {
        ?>
		<tr class="espresso-table-row <?php echo $category_slugs; ?>">
			<td class="event_title event-<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></td>
						<?php if( $show_venues ) { ?>
				<td class="venue_title event-<?php echo $post->ID; ?>"><?php //espresso_venue_name( NULL, FALSE );
                    //echo espresso_venue_address();
                    
                    // Code to Show the address in single row 
                    ob_start();
                    espresso_venue_address();
                    $faCalgary_venue_address = ob_get_clean();
                    $faCalgary_venue_address = str_replace('<br />', ', ', $faCalgary_venue_address);
                    echo $faCalgary_venue_address;
                    // Code End 
                    
                ?></td>
			<?php } ?>
			<!--<td class="space_remaining event-<?php echo $post->ID; ?>"><?php $total_spaces_left = $datetime->spaces_remaining(); echo $total_spaces_left === INF ? '<span class="smaller-text">' .  __( 'unlimited ', 'event_espresso' ) . '</span>' : $total_spaces_left; ?></td>-->
			<td class="start_date event-<?php echo $post->ID; ?>" data-value="<?php echo $datetime->get_raw( 'DTT_EVT_start' ); ?>">
				<ul class="ee-table-view-datetime-list">
					<?php
						// Loop over each datetime we have pulled from the database and output
						foreach ($datetimes as $datetime) {
						?>
							<li class="datetime-id-<?php echo $datetime->ID(); ?>">
								<?php echo date_i18n(  $date_format . ' ' . $time_format, strtotime( $datetime->start_date_and_time('Y-m-d', 'H:i:s') ) ); ?>
							</li>
					<?php
						//end foreach $datetimes
						}
					?>
				</ul>
			</td>
			<!--<td class="td-group reg-col" nowrap="nowrap"><?php echo $live_button; ?></td>-->
			<td class="td-group reg-col" nowrap="nowrap">
                <?php 
                $total_spaces_left = $datetime->spaces_remaining();
                $spaces_text = $total_spaces_left === INF 
                    ? '<div class="spaces-remaining" style:"display: block;"><center>' . __( 'Spaces left: unlimited', 'event_espresso' ) . '</center></div>' 
                    : '<div class="spaces-remaining" style:"display: block;"><center>' . sprintf( __( 'Spaces left: %d', 'event_espresso' ), $total_spaces_left ) . '</center></div>';

                //echo $spaces_text . '<br>' . $live_button; 
                echo '<div style="text-align: center;">' . $spaces_text . '<br>' . $live_button . '</div>';
                ?>
            </td>
			
		</tr>
		<?php
        }
    endwhile;
	echo '</table>';
	// allow moar other stuff
	do_action( 'AHEE__espresso_events_table_template_template__after_loop' );

else :
	// If no content, include the "No posts found" template.
	espresso_get_template_part( 'content', 'none' );

endif;