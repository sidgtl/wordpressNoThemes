<?php
/**
 * Model for interfacing with a Wordpress backend
 *
 * @author Alex Crooks, Si digital http://sidigital.co
 **/

// Load the wordpress bootstrap and it's helpers
define('WP_USE_THEMES', false);
require_once(APP_PATH.WP_INSTALL_FOLDER.'wp-blog-header.php');

class WordpressNoTheme 
{    
    function __construct() {
        // STUB
    }

    // Get posts of any type
    function getPosts($numOfPosts = MAX_ARTICLES, $offset = 0, $type = 'post', $dateFilter = FALSE, $orderBy = 'post_date', $order = 'DESC') {

        $query = array(
            'post_status'   => 'publish',
            'post_type'     => $type,
            'orderby'       => $orderBy,
            'order'         => $order,
            'offset'        => $offset,
            'numberposts'   => $numOfPosts,
        );

        if($dateFilter) {
            $query['monthnum'] = $dateFilter['month'];      
            $query['year'] = $dateFilter['year'];           
        }

        return get_posts($query);
    }

    // Get 'post' or 'page' etc by slug ("post_name" in wp db)
    function getEntryBySlug($slug = FALSE, $type = 'post') {

        if(!$slug)
            return FALSE;

        $getPost = get_page_by_path($slug, 'OBJECT', $type);

        if($getPost) {

            if($getPost->post_status == "publish")
                return $getPost;
            else
                return false;
        } else {
            return false;
        }
    }

    // Get 'post' or 'page' etc by id ("id" in wp db)
    function getEntryById($id = FALSE, $type = 'post') {
        if(!$id)
            return FALSE;
        $getPost = get_post($id, 'OBJECT', $type);
        if($getPost) {
            if($getPost->post_status == "publish")
                return $getPost;
            else
                return false;
        } else {
            return false;
        }
    }
	
    function getAttachedImage($id, $type) {
        return wp_get_attachment_url(get_post_meta($id, $type, true));
    }

    // Get preview to a post
    function getPreview($id = FALSE) {
        if(!$id)
            return FALSE;

        return get_post($id, 'OBJECT');        
    }

    // Get related posts based on tags used in original post
    function getRelated($postID, $exclude = '', $numResults = 5) {

        $related = array();
        $tags = array();
        $getTags = wp_get_post_tags($postID);

        if($getTags && $postID) 
        {            
            foreach($getTags as $tag) {
                $tags[] = $tag->term_id;
            }

            $related = get_posts(array(
                'numberposts'   => $numResults,
                'tag__in'       => $tags,
                'post__not_in'  => array($postID),
                'exclude'       => $exclude,
            ));
        }

        return $related;
    }

    function getCategoryBySlug($slug) {
        return get_category_by_slug($slug);
    }

    // Return post's category info
    function getPostCategory($postID) {

        $category = get_the_category($postID);
        
        if($category) {
            $data = new stdClass();

            $data->ID = $category[0]->cat_ID;
            $data->name = $category[0]->name;
            $data->slug = $category[0]->slug;

            return $data;
        } else {
            return FALSE;
        }
    }

    // Return posts in a particular category, by category ID
    function getPostsInCategory($categoryID, $exclude = '', $offset = 0, $maxPosts = MAX_ARTICLES, $type = 'post') {

        return get_posts(array(
            'post_status'   => 'publish',
            'post_type'     => $type,
            'category_name' => $categoryID,
            'exclude'       => $exclude,
            'offset'        => $offset,
            'numberposts'   => $maxPosts
        ));
    }

    // Return posts in a custom taxonomy
    function getPostsInCustomCategory($taxonomy, $categoryID, $type = 'post', $maxPosts = MAX_ARTICLES) {
        return get_posts(array(
            'post_status'   => 'publish',
            'post_type'     => $type,
            $taxonomy       => $categoryID,
            'numberposts'   => $maxPosts
        ));
    }

    // Return our latest blog posts
    function getLatest($numOfPosts = MAX_ARTICLES, $offset = 0) {
        return wp_get_recent_posts(array(
            'numberposts' => $numOfPosts,
            'offset' => $offset,
            'post_status' => 'publish'
        ), 'OBJECT');
    }

    function search($term, $offset = 0, $maxPosts = MAX_ARTICLES) {
        return get_posts(array(
            's'             => $term,
            'post_status'   => 'publish',
            'offset'        => $offset,
            'numberposts'   => $maxPosts
        ));
    }

    // Clean output
    function clean($content) {
        return stripslashes(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
    }
}

$wp = new WordpressNoTheme();