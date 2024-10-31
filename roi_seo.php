<?php
/*  
Plugin Name:    ROI_SEO
Plugin URI:     
Description:    THIS PLUGIN IS NO LONG SUPPORTED
Version:        0.3
Author:         roiseo
Author URI:     http://wordpress.org/plugins/roi-seo/
License:        GPL2
Start:          Nov 18, 2011

Read /readme.txt for more information.


/*====================================================================================================================
     /*! COPYRIGHT */
/*--------------------------------------------------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General
Public License, version 2, as published by the Free Software Foundation. This program is distributed in the
hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have
received a copy of the GNU General Public License along with this program; if not, write to the Free Soft-
ware Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA



/*====================================================================================================================
     /*! MAIN OUTPUT FUNCTIONALITY */
/*--------------------------------------------------------------------------------------------------------------------*/

/**
 * The plugins main function: output the <title>, <meta keywords/description
 * This function should be in the templates header.php tag below the opening
 * <head> tag
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo( $show_title = true, $show_description = true, $show_keywords = true ) {
    	
    	// if not using "auto-output" and doing manual instead
    	if (!isset($roi_seo_options['add_html']) || (isset($roi_seo_options['add_html']) && $roi_seo_options['add_html'] != 'auto')) {

	        // get SEO data for this post/page/id
	        $ths_seo = get_roi_seo();
	        
	        //define three tags to add SEO
	        $title          = roi_seo_format_title($ths_seo); //alterate string for dynamic formats
	        $description    = roi_seo_format_description($ths_seo); //alterate string for dynamic formats
	        $keywords       = roi_seo_create_keywords($ths_seo); //create keywords based on tags/titles
	        
	        // OUTPUT TAGS
	        if ($show_title)        echo "\n\t\t<title>{$title}</title>\n";
	        if ($show_description)  echo "\t\t<meta name=\"description\" content=\"{$description}\" />\n";
	        if ($show_keywords)     echo "\t\t<meta name=\"keywords\" content=\"{$keywords}\" />\n";
        
        }
    }
    
    // For themers only looking for a single tag instead of all three
    function roi_seo_title()        { roi_seo(true,false,false); }
    function roi_seo_description()  { roi_seo(false,true,false); }
    function roi_seo_keywords()     { roi_seo(false,false,true); }
    
    
/**
 * If set to "Automatically" for adding HTML tags, tap into Wordpress hooks
 * to add <title> value
 *
 * @since 0.2
 * @author roiseo
 */
add_filter('wp_title','roi_seo_change_wp_title',999);    
    function roi_seo_change_wp_title($default_title) {
    	global $roi_seo_options;

    	// if we're using "auto-output", let's disregard this and tap into hooks
    	if (isset($roi_seo_options['add_html']) && $roi_seo_options['add_html'] == 'auto') {
	        $ths_seo = get_roi_seo();
	        
	        //define three tags to add SEO
	        return roi_seo_format_title($ths_seo); //alterate string for dynamic formats
        } 
        // if manual, don't worry about wp_title() hook
        else {
        	return $default_title;
        }
    }
    
/**
 * If set to "Automatically" for adding HTML tags, tap into Wordpress hooks
 * to add two <meta> tags: note this may result in duplicate tags depending on theme!, 
 * manual suggested
 *
 * @since 0.2
 * @author roiseo
 */
add_filter('wp_head','roi_seo_add_meta_tags',0);    
    function roi_seo_add_meta_tags($default_title) {
    	global $roi_seo_options;
    
    	// if we're using "auto-output", let's disregard this and tap into hooks
    	if (isset($roi_seo_options['add_html']) && $roi_seo_options['add_html'] == 'auto') {
    		    		
	        $ths_seo = get_roi_seo();
	        
        	//define three tags to add SEO
        	$description    = roi_seo_format_description($ths_seo); //alterate string for dynamic formats
        	$keywords       = roi_seo_create_keywords($ths_seo); //create keywords based on tags/titles
	        
	        
	        echo "\t\t<meta name=\"description\" content=\"{$description}\" />\n";
	        echo "\t\t<meta name=\"keywords\" content=\"{$keywords}\" />\n";
        } 
    }
    
    
    
    
/**
 * Create the title by figuring out what current page type is, 
 * getting the format for the title, then replace macros w/ 
 * respected values
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_format_title($ths_seo = '') {
        global $post, $roi_seo_options, $wpdb, $wp_query;
    
            // default <title></title> formatting
            $in = '%blog_title%';
            
            // replace macros
            $replace_me = array(
                '%blog_title%',
                '%blog_description%',
            );
            $with_me = array(
                roi_seo_default_tag_values('title'),
                roi_seo_default_tag_values('description'),
            );
            
            //@TODO: make %title% a universal one (ie: would work for category_title/post_title/page_title)
            
            // For each possible page type, lets add some macro's,
            // and change the output format to the ones set in settings
            if ( is_home() || is_front_page() ) { 
                $in = $roi_seo_options['frmt_title_home'];
            } 
            else if ( is_single() ) {
            	$title = (isset($ths_seo['title']) && !empty($ths_seo['title'])) ? $ths_seo['title'] : $post->post_title;;
                $replace_me[]   = '%post_title%';
                $with_me[]      = $title;
                $replace_me[]   = '%title%';
                $with_me[]      = $title;
                $replace_me[]   = '%date%';
                $with_me[]      = (isset($post->post_date) && !empty($post->post_date)) ? 
                                  date(get_option('date_format'), strtotime($post->post_date)) : date(get_option('date_format'));
                $replace_me[]   = '%terms%';
                $with_me[]      = roi_seo_create_keywords('',$post->ID);
                $replace_me[]   = '%author%';
                $with_me[]      = get_the_author_meta('user_nicename', $post->post_author);
                $in             = $roi_seo_options['frmt_title_post'];
            }
            else if ( is_page() ) { 
            	$title = (isset($ths_seo['title']) && !empty($ths_seo['title'])) ? $ths_seo['title'] : $post->post_title;;
                $replace_me[]   = '%page_title%';
                $with_me[]      = $title;
                $replace_me[]   = '%title%';
                $with_me[]      = $title;
                $replace_me[]   = '%author%';
                $with_me[]      = get_the_author_meta('user_nicename', $post->post_author);
                $in             = $roi_seo_options['frmt_title_page'];
            }
            else if ( is_search() ) { 
                $replace_me[]   = '%s%';
                $replace_me[]   = '%title%';
                $replace_me[]   = '%search%';
                $with_me[]      = $wp_query->query_vars['s'];
                $with_me[]      = $wp_query->query_vars['s'];
                $with_me[]      = $wp_query->query_vars['s'];
                $in             = $roi_seo_options['frmt_title_search'];
            }
            else if ( is_category() ) {
                $cat = get_term_by('id',$wp_query->query_vars['cat'],$wp_query->tax_query->queries[0]['taxonomy']);
                $replace_me[]   = '%category_title%';
                $replace_me[]   = '%title%';
                $replace_me[]   = '%category_description%';
                $with_me[]      = $cat->name;
                $with_me[]      = $cat->name;
                $with_me[]      = strip_tags($cat->description);
                $in             = $roi_seo_options['frmt_title_cat'];
            }
            else if ( is_tag() ) { 
                $tag = get_term_by('slug',$wp_query->query_vars['tag'],$wp_query->tax_query->queries[0]['taxonomy']);
                $replace_me[]   = '%tag%';
                $with_me[]      = $tag->name;
                $replace_me[]   = '%title%';
                $with_me[]      = $tag->name;
                $in             = $roi_seo_options['frmt_title_tag'];
            }
            else if ( is_author() ) { 
            	$authorid = @$wp_query->query_vars['author'];
            	$authorname = @$wp_query->query_vars['author_name'];
				$author = $wpdb->get_row("SELECT display_name, ID FROM $wpdb->users WHERE user_nicename = '{$authorname}'");
				if (!$author['display_name']) {
					$author = get_userdata($authorid);
					$author = (!empty($author->data->display_name)) ? $author->data->display_name : $author->data->user_nicename;
				} else {
					$author = $author['display_name'];
				}
				
                $replace_me[]   = '%author%';
                $with_me[]      = $author;
                $replace_me[]   = '%title%';
                $with_me[]      = $author;
                $in             = $roi_seo_options['frmt_title_author'];
            }
            else if ( is_tax() && ($wp_query->query_vars['taxonomy'] != 'post' && $wp_query->query_vars['taxonomy'] != 'page')) {            	
            	//get the term
            	$term = get_term_by('slug',$wp_query->query_vars[$wp_query->query_vars['taxonomy']],$wp_query->query_vars['taxonomy']);
                $replace_me[]   = '%tax_title%';
                $with_me[]      = $term->name;
                $replace_me[]   = '%post_title%';
                $with_me[]      = $term->name;
                $replace_me[]   = '%tax_name%';
                $with_me[]      = $term->name;
                $replace_me[]   = '%title%';
                $with_me[]      = $term->name;
                $in             = $roi_seo_options['frmt_title_tax'];
            }
            else if ( is_archive() ) {
                $year = substr_replace($wp_query->query_vars['m'],'',4,6);
                $month = substr_replace($wp_query->query_vars['m'],'',0,4);
                $replace_me[]   = '%date%';
                $datee = date('F Y',strtotime("{$year}-{$month}-01"));
                $with_me[]      = ($datee == 'January 1970' || $datee == 'December 1969') ? '' : $datee;
                $in             = $roi_seo_options['frmt_title_archive'];
            }
            else if ( is_404() ) { 
                //@TODO: add: %request_url%, %request_words%, %404_title%
                $in             = $roi_seo_options['frmt_title_404'];
            }
            
            //@TODO: add "paged" clause/argument for " - Part %page%"
            
            //replace macros with content in specified <title> tag formating
            $doctitle = str_replace($replace_me,$with_me,$in);
            
            // return html string, strip tags incase someone tried html formatting: i.e. "<strong>", ect.
            return strip_tags($doctitle);
    }
    
    
    
/**
 * Create the description by getting the format for the title, 
 * then replace macros w/ respected values
 *
 * @since 0.1
 * @author roiseo
 */
function roi_seo_format_description($ths_seo) {
    global $post, $roi_seo_options;
    
    $is_home = (get_the_ID() == get_option('page_on_front')) ? true : false;
    
    $description = (!empty($ths_seo['description']) && !$is_home) ? $ths_seo['description'] : roi_seo_default_tag_values('description');
    $title = (isset($post) && is_object($post) && !empty($post->post_title)) ? $post->post_title : '';
    return str_replace(array('%description%','%wp_title%'),array($description,$title),$roi_seo_options['frmt_description']);
}
    
    
    
/**
 * Create the keywords by using dynamically entered ones, or
 * if not set: find a posts tags or categories.
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_create_keywords($ths_seo = '', $id='') {
        global $wp_query;
        
        // get the id
        $id = (empty($id)) ? get_the_ID() : $id;

        $is_home = ($id == get_option('page_on_front')) ? true : false;
        
        // if the post has dynamic keywords, use them.
        $keywords = (!empty($ths_seo['keywords']) && !$is_home) ? $ths_seo['keywords'] : roi_seo_default_tag_values('keywords');
        
        // if there's no keywords, lets see if we can use the tags / cat
        if (empty($keywords) && !is_home()) {
            $keys = array();
            // get a list of taxonomies to handle custom post types..
            $taxonomies = array();
            $get_taxonomies = get_taxonomies();
            foreach ($get_taxonomies as $a_taxonomy) 
                $taxonomies[] = $a_taxonomy;
            
            $terms = wp_get_object_terms($id, $taxonomies);
            foreach ($terms as $k => $term) {
                $keys[] = $term->name;
            }
            $keywords = implode(', ',$keys);
        }
        
        // if no keywords, return defaults
        if (empty($keywords))
            $keywords = roi_seo_default_tag_values('keywords');
        
        // return string keywords
        return $keywords;
    }
    
    
    
/**
 * Called to when a dynamic field isn't set for post types, or 
 * when one of three tags is called to for other page types.
 * Returns the default values of the three main tags; first 
 * checking the "Default" (global) values set in ROI_SEO Options, 
 * if not filled, returns WP's name/tag line fields.
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_default_tag_values($tag='') {
        global $roi_seo_options;
    
        if ($tag == 'title') {
            return (isset($roi_seo_options['home_title']) && !empty($roi_seo_options['home_title'])) 
                    ? $roi_seo_options['home_title'] : get_bloginfo('name');
        } elseif ($tag == 'description') {
            return (isset($roi_seo_options['home_description']) && !empty($roi_seo_options['home_description'])) 
                    ? $roi_seo_options['home_description'] : get_bloginfo('description');
        } elseif ($tag == 'keywords') {
            $keywords = (isset($roi_seo_options['home_keywords']) && !empty($roi_seo_options['home_keywords'])) 
                    ? $roi_seo_options['home_keywords'] : get_bloginfo('description');
            return $keywords;
        } else {
            return get_bloginfo('name');
        }
    }



/**
 * returns array of <title>, <meta keywords/descript> 
 * if entered for that post/page/ect
 *
 * @since 0.1
 * @author roiseo
 */
    function get_roi_seo($id = '') {
        $id = (empty($id)) ? get_the_ID() : $id;
        if (is_home() || $id == get_option('page_on_front')) { $id = get_option('page_on_front'); }
        return @get_post_meta($id,'roi_seo',true);
    }
    
    
    
/*====================================================================================================================
     /*! ADMIN: Activation and <head> alteration */
/*--------------------------------------------------------------------------------------------------------------------*/

/**
 * Start the plugin, define main settings if not set, 
 * define global settings
 *
 * @since 0.1
 * @author roiseo
 */
add_action('init','roi_seo_init');
    function roi_seo_init() {
        global $roi_seo_options;
        // get the options for this plugin
        $roi_seo_options = get_option('roi_seo_options');
    }
    
    
    
add_action('admin_init','roi_seo_activate',999);
    function roi_seo_activate() {       
        global $roi_seo_options;
        
        $reset = (isset($_POST['RESET_ROI_SEO']) && $_POST['RESET_ROI_SEO'] == "Restore Default Values") ? true : false;

        if ((isset($_GET['activate']) && $_GET['activate'] == 'true') || (isset($_GET['activate-multi']) && $_GET['activate-multi'] == 'true') || $reset) {
            
            // no options, or requested; add defaults
            if (!$roi_seo_options || $reset) {
                // if reseting settings page:
                if ($reset) {
                    $defaults = roi_seo_options_default_values();
                    delete_option('roi_seo_options');
                    $_POST['roi_seo_options'] = $defaults;
                }
                // add defaults
                add_option('roi_seo_options',roi_seo_options_default_values());
            }
        }
        // Lets get our settings
        $roi_seo_options = get_option('roi_seo_options');                       
    }
    
    
    
/**
 * Add CSS/Javascript to our admin header for the various forms used throughout
 *
 * @since 0.1
 * @author roiseo
 */
add_action('admin_head','roi_seo_admin_header_scripts',9);
    function roi_seo_admin_header_scripts() {
        echo "\n\n\r<link rel='stylesheet' href='".WP_PLUGIN_URL."/ROI_SEO/roi_seo.css' type='text/css' media='all' />
        <!--temp fix of small problem w/ URL -->
        <link rel='stylesheet' href='".WP_PLUGIN_URL."/roi-seo/roi_seo.css' type='text/css' media='all' />
        <!-- // -->\n";

    	if (roi_seo_is_on_this_page()) {
        ?>
            <script type='text/javascript'>
            jQuery(document).ready(function() {
                // Change the number of characters remaining for text/strings
                function limitChars(textid, limit, infodiv) {
                    var text = jQuery('#'+textid).val(); 
                    var textlength = text.length;
                    remaining = limit - textlength;
                    jQuery('#' + infodiv).html(remaining);
                    //change css colour if within 10% of limit
                    good_amount = limit * 0.1;
                    if (remaining < good_amount) {
                        jQuery('#' + infodiv).css('color','green')
                    } else {
                        jQuery('#' + infodiv).css('color','#666')
                    }
                    // cut off string if exceeding limit.
                    if(textlength > limit) {
                        // jQuery('#'+textid).val(text.substr(0,limit));
                        return false;
                    } else {
                        return true;
                    }
                }
                // Change the number of remaining keywords by coma seps.
                function keywordsChars(textid, limit, infodiv) {
                    limit = limit - 1
                    var val = jQuery('#roi_seo_keywords').val()
                    var hascoma = val.indexOf(',')
                    if (hascoma != '-1' && hascoma > 0) {
                        var count = val.split(',').length; 
                        var remaining = limit - count;
                        if (remaining == 0) {
                            jQuery('#' + infodiv).css('color','green')
                        } else if (remaining < 0) {
                            jQuery('#' + infodiv).css('color','red')
                        } else {
                            jQuery('#' + infodiv).css('color','#666')
                        }
                        remaining = remaining;
                        jQuery('#' + infodiv).html(remaining );
                    } else {
                        if (val != '') {
                            minusone = limit - 1
                            jQuery('#' + infodiv).html(minusone);
                            jQuery('#' + infodiv).css('color','#666')
                        } else {
                            jQuery('#' + infodiv).html(limit);
                            jQuery('#' + infodiv).css('color','#666')
                        }
                    }
                    return true;
                }
                // Listeners for any input actions on our counters
                jQuery(function(){
                
                    //init, if values already exist.
                    limitChars('roi_seo_description', 150, 'description_chars_left');
                    limitChars('roi_seo_title', 60, 'title_chars_left');
                    keywordsChars('roi_seo_keywords', 5, 'keywords_left');
                    
                    // a little "over-board" but track every key action
                    jQuery('#roi_seo_description').keyup(function(){
                        limitChars('roi_seo_description', 150, 'description_chars_left');
                    })
                    jQuery('#roi_seo_description').keydown(function(){
                        limitChars('roi_seo_description', 150, 'description_chars_left');
                    })
                    jQuery('#roi_seo_description').blur(function(){
                        limitChars('roi_seo_description', 150, 'description_chars_left');
                    })
                    jQuery('#roi_seo_description').focus(function(){
                        limitChars('roi_seo_description', 150, 'description_chars_left');
                    })
                    jQuery('#roi_seo_title').keyup(function(){
                        limitChars('roi_seo_title', 60, 'title_chars_left');
                    })
                    jQuery('#roi_seo_title').keydown(function(){
                        limitChars('roi_seo_title', 60, 'title_chars_left');
                    })
                    jQuery('#roi_seo_title').blur(function(){
                        limitChars('roi_seo_title', 60, 'title_chars_left');
                    })
                    jQuery('#roi_seo_title').focus(function(){
                        limitChars('roi_seo_title', 60, 'title_chars_left');
                    })
                    jQuery('#roi_seo_keywords').keyup(function(){
                        keywordsChars('roi_seo_keywords', 5, 'keywords_left');
                    })
                    jQuery('#roi_seo_keywords').keydown(function(){
                        keywordsChars('roi_seo_keywords', 5, 'keywords_left');
                    })
                    jQuery('#roi_seo_keywords').blur(function(){
                        keywordsChars('roi_seo_keywords', 5, 'keywords_left');
                    })
                    jQuery('#roi_seo_keywords').focus(function(){
                        keywordsChars('roi_seo_keywords', 5, 'keywords_left');
                    })
                });
            });
            </script>
        <?php
        } //roi_seo_is_on_this_page()
    }
    
    
    
/**
 * only load what's required and add it to default query instead of over writing
 *
 * @since 0.1.2
 * @author roiseo
 */
function roi_seo_is_on_this_page() {
	global $current_screen;
return 
	(
		(isset($_GET['page']) && $_GET['page'] == 'roi_seo_custom_options_page') || 
		isset($_GET['post']) || 
		(isset($_GET['action']) && $_GET['action'] == 'edit') || 
		isset($_GET['post_type'])  ||
		($current_screen->id == 'post' || $current_screen->id == 'page') //@TODO: make page and post tap into the SELECTED post_types for SEO support
		
	) ? true : false;
}
    
    
    
/**
 * Add metabox to specified posts
 *
 * @since 0.1
 * @author roiseo
 */
add_action( 'add_meta_boxes', 'roi_seo_add_box' );
    function roi_seo_add_box() {
        global $roi_seo_options;
        if (isset($roi_seo_options['showin']) && is_array($roi_seo_options['showin'])) {
            foreach ($roi_seo_options['showin'] as $posttype => $foo) {
                add_meta_box( 'roi_seo_id', __( 'ROI SEO (Search Engine Optimization)', 'roi_seo_txtdmn' ), 
                'roi_seo_in_box', $posttype  );
            }
        }
    } 
    
    
    
/**
 * Print the meta box contents
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_in_box( $post ) {
        global $roi_seo_options;
        //get custom settings for this page
        $cstm = get_roi_seo();
        
        // security 
        wp_nonce_field(plugin_basename( __FILE__ ), 'roi_seo_box' );

        $hide_seo = false;
        if (get_the_ID() == get_option('page_on_front')) {
        	echo "<p><strong>NOTE:</strong> This is the home page &mdash; SEO tags are generated from the \"<strong>Site Defaults</strong>\" in <a href='options-general.php?page=roi_seo_custom_options_page'>Settings > ROI Search Engine Optimization</a></p>";
        	$hide_seo = " style='display:none !important;'";
        }
         ?>
          <table id='roi_seo' <?= $hide_seo ? $hide_seo : '' ?>>
           <tr>
                <td class='roi_seo_label'><code>&lt;title&gt; Custom Title:</code></td>
                <td>
                     <input class="ct_datefield a_title_field" type="text" name="roi_seo[title]" id="roi_seo_title" value="<?php echo @$cstm['title'] ?>"  />
                     <div class='description roi_seo_description'><span id='title_chars_left' class='chars_left'>60</span> characters left
                     &ndash; avoid stop-words like: <em>"and / are / but / as / is"</em>
                     <br />
                     Appears in the browsers title and in search engine results</div>
                </td>
           </tr>
           <tr>
                <td class='roi_seo_label'><code>&lt;meta&gt; Description:</code></td>
                <td>
                     <textarea name="roi_seo[description]" id='roi_seo_description'><?php echo @$cstm['description'] ?></textarea>
                     <div class='description roi_seo_description'><span id='description_chars_left' class='chars_left'>150</span> characters left
                     &ndash; be brief but descriptive.<br />
                     Summarize; use phrases people may search or find answers to within.                     
                     </div>
                </td>
           </tr>
           <tr>
                <td class='roi_seo_label'><code>&lt;meta&gt; Keywords:</code></td>
                <td>
                     <input class="ct_datefield" type="text" id='roi_seo_keywords' name="roi_seo[keywords]" value="<?php echo @$cstm['keywords'] ?>"  />
                     <div class='description roi_seo_description'><span id='keywords_left' class='chars_left'>4</span> keywords/keyphrases left
                      &ndash; obsolete tag &ndash; the fewer, the better<br />
                     Separate keys with coma</div>
                </td>
           </tr>
          </table>
          <?php /* if (@$roi_seo_options['powered_by'] == 'true') */ roi_seo_credit();
    }
    
    
    
/**
 * Save content from metabox
 *
 * @since 0.1
 * @author roiseo
 */
add_action( 'save_post', 'roi_seo_save_postdata' );
    function roi_seo_save_postdata( $post_id ) {

        // verify wp_nonce
        if ( !wp_verify_nonce( @$_POST['roi_seo_box'], plugin_basename( __FILE__ ) ) )
        return;
        
        // Check permissions
        if ( 'page' == $_POST['post_type'] ) 
            if ( !current_user_can( 'edit_page', $post_id ) ) return;
        else
            if ( !current_user_can( 'edit_post', $post_id ) ) return;
        
        // Delete meta info if it exists, add $_POST meta data
        @delete_post_meta($post_id,'roi_seo');
        @add_post_meta($post_id,'roi_seo',$_POST['roi_seo']);
    }



/*====================================================================================================================
     /*! CUSTOM SETTINGS ( + SETTINGS PAGE) */
/*--------------------------------------------------------------------------------------------------------------------*/

/**
 * create custom plugin settings menu
 *
 * @since 0.1
 * @author roiseo
 */
add_action('admin_menu', 'roi_seo_create_optionspg');
    
    function roi_seo_create_optionspg() {
        global $menu, $submenu;
        add_submenu_page('options-general.php', 'ROI SEO (Search Engine Optimization)', 
                           'ROI Search Engine Optimization', 'manage_options', 'roi_seo_custom_options_page',
                           'roi_seo_custom_options_page');
        add_action( 'admin_init', 'register_roi_seo_custom_options_settings' );
    }
    function register_roi_seo_custom_options_settings() {
        register_setting( 'roi_seo_custom_options-group', 'roi_seo_options' );
    }
    
    
    
/**
 * The default values for the settings page, a setting can't be left empty
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_options_default_values($return_only = '') {
            $defaults = array(
            'frmt_title_home'    => '%blog_title% | %blog_description%',
            'frmt_title_post'    => '%post_title%',
            'frmt_title_page'    => '%page_title%',
            'frmt_title_cat'     => '%category_title% | %blog_title%',
            'frmt_title_archive' => '%date% | %blog_title%',
            'frmt_title_tag'     => '%tag% | %blog_title%',
            'frmt_title_tax'	 => '%tax_name% | %blog_title%',
            'frmt_title_author'  => '%author% | %blog_title%',
            'frmt_title_search'  => 'Keyword: %search% | %blog_title%',
            'frmt_description'   => '%description%',
            'frmt_title_404'     => 'Page Not Found | %blog_title%',
            'showin'             => array('post' => 'true','page' => 'true'), //checkbox
            'keywords_from_tags' => 'TRUE', //checkbox
            'keywords_from_cats' => 'TRUE',  //checkbox
            'powered_by'		 => 'TRUE'
        );
        if (!empty($return_only)) {
            return $defaults[$return_only];
        } else {
            return $defaults; 
        }
    }
    
    
    
/**
 * HTML Settings page
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_custom_options_page() {
        
        // only if we want to show     
        if (isset($_GET['page']) && $_GET['page'] == 'roi_seo_custom_options_page') :
        
        // get the settings
        $roi_seo_options = get_option('roi_seo_options');
        
        // get the defaults for undefined settings
        $defaults = roi_seo_options_default_values();
        
        // if there are no settings, insert defaults
        if (!$roi_seo_options) {
            add_option('roi_seo_options',$defaults);
            $roi_seo_options = get_option('roi_seo_options');                       
        }
        
        // We don't want titles to be empty, so if a user clears a field: restore default
        foreach ($defaults as $k => $v) {
            if ($v != 'TRUE' && !is_array($v)) //except on checkboxes
            $roi_seo_options[$k] = (!isset($roi_seo_options[$k]) || empty($roi_seo_options[$k])) ? $v : $roi_seo_options[$k];
        }
        
        // link back to documentation
        $help_url = "";
        ?>
        <div class='wrap' id='roi_seo_settings_page_wrap'>
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>ROI SEO (Search Engine Optimization)</h2>
        <form method="post" action="options.php">
            <?php 
            wp_nonce_field('update-options'); 
            settings_fields('roi_seo_custom_options-group');
            ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="roi_seo_options" />
            <table class="form-table">
                <tr>
                    <td colspan=2><h3 class='title'>Site Defaults</h3></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><code>&lt;title&gt; Home Title</code></th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[home_title]" id="roi_seo_title" value="<?php echo @$roi_seo_options['home_title'] ?>" class='a_title_field' />
                         <div class='description roi_seo_description'><span id='title_chars_left' class='chars_left'>60</span> characters left &ndash; avoid stop words like: <em>"and / are / but / as / is"</em><br />
                         Appears in the browsers title and in search engine results<br />
                         <em>"Settings > General > Site Name" will be used if left empty<br />
                         </div>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><code>&lt;meta&gt; Home Description</code></th>
                    <td>
                     <textarea name="roi_seo_options[home_description]" id='roi_seo_description'><?php echo @$roi_seo_options['home_description'] ?></textarea>
                     <div class='description roi_seo_description'>
                        <span id='description_chars_left' class='chars_left'>150</span> characters left
                        &ndash; be brief but descriptive.<br />
                        Summarize; use phrases people may search or find answers to within.<br />
                     </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><code>&lt;meta&gt; Home Keywords</code></th>
                    <td>
                        <label>
                     <input type='text' name="roi_seo_options[home_keywords]" id='roi_seo_keywords' value='<?php echo @$roi_seo_options['home_keywords'] ?>'>
                     <div class='description roi_seo_description'><span id='keywords_left' class='chars_left'>4</span> keywords/keyphrases left &ndash; obsolete tag &ndash; the fewer, the better<br />
                     Separate keys with coma</div>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <td colspan=2><h3 class='title'>Title Tag Formats</h3></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Home Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_home]" value="<?php echo @$roi_seo_options['frmt_title_home'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Post Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_post]" value="<?php echo @$roi_seo_options['frmt_title_post'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Page Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_page]" value="<?php echo @$roi_seo_options['frmt_title_page'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Category Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_cat]" value="<?php echo @$roi_seo_options['frmt_title_cat'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Archive Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_archive]" value="<?php echo @$roi_seo_options['frmt_title_archive'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tag Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_tag]" value="<?php echo @$roi_seo_options['frmt_title_tag'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom Taxonomy Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_tax]" value="<?php echo @$roi_seo_options['frmt_title_tax'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Author Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_author]" value="<?php echo @$roi_seo_options['frmt_title_author'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Search Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_search]" value="<?php echo @$roi_seo_options['frmt_title_search'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">404 Title Format</th>
                    <td>
                        <label>
                         <input type="text" name="roi_seo_options[frmt_title_404]" value="<?php echo @$roi_seo_options['frmt_title_404'] ?>" class='code' />
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Description Format</th>
                    <td>
                        <label>
                         <textarea type="text" name="roi_seo_options[frmt_description]"><?php echo @$roi_seo_options['frmt_description'] ?></textarea>
                         <?php echo $help_url ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan=2><h3 class='title'>Options</h3></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Use Dynamic SEO Tags for: <?php echo $help_url ?></th>
                    <td>
                <?php if( function_exists( 'get_post_types' ) ) :
                    $post_types = roi_seo_get_post_types();
                    if( count( $post_types ) ) :
                        foreach($post_types as $post_type) : ?>
                            <label>
                            <input 
                                type="checkbox" 
                                name="roi_seo_options[showin][<?php echo $post_type->name; ?>]" 
                                value="true" <?php echo (@$roi_seo_options['showin'][$post_type->name]=='true') ? 'checked="checked"' : '' ?> 
                            />
                           <?php echo $post_type->labels->name; ?></label><br />
                    <?php endforeach;
                    endif;
                 endif ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Generate Posts Keywords: <?php echo $help_url ?></th>
                    <td>
                         <label><input type="checkbox" name="roi_seo_options[keywords_from_tags]" value="TRUE"
                         <?php echo (@$roi_seo_options['keywords_from_tags'] == 'TRUE') ? ' checked="checked"' : '' ?> /> 
                         From tags</label><br />
                         <label><input type="checkbox" name="roi_seo_options[keywords_from_cats]" value="TRUE" 
                         <?php echo (@$roi_seo_options['keywords_from_cats'] == 'TRUE') ? ' checked="checked"' : '' ?> /> 
                         From categories</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Add HTML into theme: <?php echo $help_url;
                    $roi_seo_options['add_html'] = (!isset($roi_seo_options['add_html']) || empty($roi_seo_options['add_html'])) ? 'manual' : $roi_seo_options['add_html'];
                    ?></th>
                    <td>
                         <label><input type="radio" name="roi_seo_options[add_html]" value="manual"
                         <?php echo (@$roi_seo_options['add_html'] == 'manual') ? ' checked="checked"' : '' ?> /> 
                        <strong>Manually</strong></label><br />
                         <div class='description'>
                         	&nbsp; &nbsp; &nbsp; * Adding <code>roi_seo()</code> function (outputs <code>&lt;title&gt;</code>  and <code>&lt;meta&gt;</code> tags) <br />
                         	&nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; - Requires editing your themes <code>header.php</code> file (see <a href='http://wordpress.org/extend/plugins/roi-seo/installation/' target='_Blank'>installation instructions</a>)<br />
                         	&nbsp; &nbsp; &nbsp; * Recommended to prevent duplication of tags<br />
                         	&nbsp; &nbsp; &nbsp; * Recommended for users who don't switch themes<br />
                         </div>
                         <div style='height:10px;'></div>
                         <label><input type="radio" name="roi_seo_options[add_html]" value="auto" 
                         <?php echo (@$roi_seo_options['add_html'] == 'auto') ? ' checked="checked"' : '' ?> /> 
                         <strong>Automatically</strong></label>
                         <div class='description'>
                         	&nbsp; &nbsp; &nbsp; * Uses Wordpress's hooks<br />
                         	&nbsp; &nbsp; &nbsp;&nbsp;  &nbsp; &nbsp; &nbsp; - Replaces themes <code>wp_title()</code> value (may not work on all themes - depending on author)<br />
                         	&nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; - Adds <code>&lt;meta&gt;</code> tags via <code>wp_head()</code><br />
                         	&nbsp; &nbsp; &nbsp; * Recommended for users who do switch themes<br />
                         	&nbsp; &nbsp; &nbsp; * Recommended for users who are not familar with editing theme files (HTML/PHP).<br />
                         	&nbsp; &nbsp; &nbsp; * see <a href='http://wordpress.org/extend/plugins/roi-seo/installation/' target='_Blank'>installation instructions</a>
                         </div>

                    </td>
                </tr>
                <!--
                <tr>
                    <th scope="row">Show Plugin Credits<br /> <span class='description'>will appear in admin only</span></th>
                    <td>
                           <label><input 
                                type="checkbox" 
                                name="roi_seo_options[powered_by]" 
                                value="true" disabled="disabled" checked="checked" <?php /* echo (@$roi_seo_options['powered_by'] == 'true') ? 'checked="checked"' : '' */ ?> 
                            /> <span style='color: #ccc;'>Yes</span>
                            </label>
                </tr>
                -->
            </table>
            <p class="submit">
                 <input type="submit" class="button-primary" value="Save Changes" />
                 <input type="submit" class="button-secondary" name='RESET_ROI_SEO' value="Restore Default Values" />
            </p>
        </form>
        <?php /* if ($roi_seo_options['powered_by'] == 'true') */ roi_seo_credit(); ?>
        </div><!--/wrap-->
        <?php
        endif;
    }
    
    
    
/**
 * Retrieve All Post Types as array()
 *
 * @since 0.1
 * @author roiseo
 */
    function roi_seo_get_post_types() {
        $args           = array( 'public' => true, 'show_ui' => true, '_builtin' => false ); 
        $output         = 'objects';
        $operator       = 'and';
        $post_types     = get_post_types( $args, $output, $operator );
        $post_types['post']->labels->name   = 'Posts';
        $post_types['post']->name           = 'post';
        $post_types['page']->labels->name   = 'Pages';
        $post_types['page']->name           = 'page';
        return $post_types;
    }


    
/**
 */ function roi_seo_credit() {  }
 
?>