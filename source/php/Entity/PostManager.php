<?php

namespace EventManagerIntegration\Entity;

abstract class PostManager
{
    /**
     * Post object sticky values
     */
    public $post_type = null;
    public $post_status = 'publish';

    /**
     * Keys that counts as post object properties
     * Any other key will be treated as meta properties
     * @var array
     */
    public $allowedPostFields = array(
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_content_filtered',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_parent',
        'menu_order',
        'post_mime_type',
        'guid'
    );

    /**
     * Constructor
     * @param array $postData Post object fields as array
     * @param array $metaData Post meta as array
     */
    public function __construct($postData = array(), $metaData = array())
    {
        if (is_null($this->post_type)) {
            throw new \Exception('You need to specify a post type by setting the class property $postType');
            exit;
        }

        // Add post data as separate object parameters
        foreach ($postData as $key => $value) {
            $this->{$key} = $value;
        }

        // Add meta data as separate object parameters
        foreach ($metaData as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Get  posts
     * @param  integer        $count       Number of posts to get
     * @param  array          $metaQuery   Meta query
     * @param  string         $postType    Post type
     * @param  array|string   $postStatus  Post status
     * @return array                       Found posts
     */
    public static function get($count, $metaQuery, $postType, $postStatus = array('publish', 'draft'))
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type'      => $postType,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        $args['post_status'] = (array)$postStatus;

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }


    public function filterMeta($meta)
    {
        $meta = array_filter($meta, array($this, 'removeEmpty'));
        return $meta;
    }

    /**
     * Remove all values that are empty, except the value 0
     * @param  $metaValue
     * @return $metaValue
     */
    public function removeEmpty($metaValue)
    {
        if (is_array($metaValue)) {
            return $metaValue;
        }

        return ($metaValue !== null && $metaValue !== false && $metaValue !== '');
    }

    /**
     * Get all values that are empty
     * @param  $metaValue
     * @return $metaValue
     */
    public function getEmptyValues($metaValue)
    {
        if (is_array($metaValue) && empty($metaValue)) {
            return $metaValue;
        }

        return ($metaValue == null || $metaValue == false || $metaValue == '');
    }

    /**
     * Delete meta data from db if it's deleted from API
     * @param  array $meta     empty meta data fields
     * @param  int   $post_id  post object id
     * @return void
     */
    public function removeEmptyMeta($meta, $post_id)
    {
        foreach ($meta as $key => $value) {
            $meta = get_post_meta($post_id, $key, true);
            if ($meta) {
                delete_post_meta($post_id, $key);
            }
        }
    }

    /**
     * Saves the event and it's data
     * @return integer The inserted/updated post id
     */
    public function save()
    {
        $this->beforeSave();

        // Arrays for holding save data
        $post = array();
        $meta = array();
        $post['post_status'] = $this->post_status;

        // Get the default class variables and set it's keys to forbiddenKeys
        $defaultData = get_class_vars(get_class($this));
        $forbiddenKeys = array_keys($defaultData);

        $data = array_filter(get_object_vars($this), function ($item) use ($forbiddenKeys) {
            return !in_array($item, $forbiddenKeys);
        }, ARRAY_FILTER_USE_KEY);

        // If data key is allowed post field add to $post else add to $meta
        foreach ($data as $key => $value) {
            if (in_array($key, $this->allowedPostFields)) {
                $post[$key] = $value;
                continue;
            }

            $meta[$key] = $value;
        }

        // Save empty meta to array
        $empty_meta_values = array_filter($meta, array($this, 'getEmptyValues'));

        // Do not include null values in meta
        $meta = array_filter($meta, array($this, 'removeEmpty'));

        $post['post_type'] = $this->post_type;
        $post['meta_input'] = $meta;

        // Check if duplicate by matching "_event_manager_id" meta value
        if (isset($meta['_event_manager_id'])) {
            $duplicate = self::get(
                1,
                array(
                    'relation' => 'AND',
                    array(
                        'key' => '_event_manager_id',
                        'value' => $meta['_event_manager_id'],
                        'compare' => '='
                    )
                ),
                $this->post_type
            );
        }

        // Update if duplicate
        if (isset($duplicate->ID)) {
            //Check if event needs to be updated
            if (get_post_meta($duplicate->ID, 'last_update', true) != $meta['last_update']) {
                $post['ID'] = $duplicate->ID;
                $this->ID = wp_update_post($post);
                $isDuplicate = true;
            }
        } else {
            // Create if not duplicate
            $this->ID = wp_insert_post($post, true);
        }

        // Remove empty meta values from db
        $this->removeEmptyMeta($empty_meta_values, $this->ID);

        return $this->afterSave();
    }


    /**
     * Uploads an image from a specified url and sets it as the current post's featured image
     * @param string $url Image url
     * @param bool   $featured True if $url is featured image, false if not.
     */
    public function setFeaturedImageFromUrl($url, $featured)
    {
        if (!isset($this->ID)) {
            return false;
        }

        if (!isset($url) || strlen($url) === 0 || !$this->isUrl($url)) {
            return false;
        }

        // Upload paths
        $uploadDir = wp_upload_dir();
        $uploadDirUrl = $uploadDir['baseurl'];
        $uploadDir = $uploadDir['basedir'];
        $uploadDir = $uploadDir . '/events';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0776)) {
                return new WP_Error('event', __('Could not create folder', 'event-integration').' "' . $uploadDir . '", '. __('please go ahead and create it manually and rerun the import.', 'event-integration'));
            }
        }

        $filename = basename($url);
        if (stripos(basename($url), '.aspx')) {
            $filename = md5($filename) . '.jpg';
        }

        // Bail if image already exists in library
        if ($attachmentId = $this->attachmentExists($uploadDir . '/' . basename($filename))) {
            set_post_thumbnail($this->ID, $attachmentId);
            return;
        }

        // Save file to server
        $contents = file_get_contents($url);
        $save = fopen($uploadDir . '/' . $filename, 'w');
        fwrite($save, $contents);
        fclose($save);

        // Detect filetype
        $filetype = wp_check_filetype($filename, null);

        // Insert the file to media library
        $attachmentId = wp_insert_attachment(array(
            'guid' => $uploadDir . '/' . basename($filename),
            'post_mime_type' => $filetype['type'],
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $this->ID
        ), $uploadDir . '/' . $filename, $this->ID);

        // Generate attachment meta
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachData = wp_generate_attachment_metadata($attachmentId, $uploadDir . '/' . $filename);
        wp_update_attachment_metadata($attachmentId, $attachData);

        // Set image as featured image or add to gallery
        if ($featured) {
            set_post_thumbnail($this->ID, $attachmentId);
        } else {
            $gallery_meta = get_post_meta($this->ID, 'event_gallery', true);
            if (empty($gallery_meta)) {
                add_post_meta($this->ID, 'event_gallery', array($attachmentId));
            } else {
                $gallery_meta[] .= $attachmentId;
                update_post_meta($this->ID, 'event_gallery', array_unique($gallery_meta));
            }
        }
    }

    /**
     * Validates url
     * @param  string  $url Url to validate
     * @return boolean
     */
    private function isUrl($url)
    {
        if (is_string($url) && preg_match('/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a attachment src already exists in media library
     * @param  string $src Media url
     * @return mixed
     */
    private function attachmentExists($src)
    {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid = '$src'";
        $id = $wpdb->get_var($query);

        if (!empty($id) && $id > 0) {
            return $id;
        }

        return false;
    }
}
