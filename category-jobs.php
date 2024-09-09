<?php
get_header();

// Debug incoming GET parameters to see what is being passed from the form
error_log('Incoming GET parameters: ' . print_r($_GET, true));

// Fetch filter values from query parameters
$job_type = isset($_GET['job_type']) ? array_map('sanitize_text_field', $_GET['job_type']) : [];
$resident_permit = isset($_GET['resident_permit']) ? array_map('sanitize_text_field', $_GET['resident_permit']) : [];
$salary = isset($_GET['salary']) ? array_map('sanitize_text_field', $_GET['salary']) : [];
$salary_with_discussion = isset($_GET['salary_with_discussion']) ? array_map('sanitize_text_field', $_GET['salary_with_discussion']) : [];

// Debug filter values to check what is being captured
error_log('Job Type Filter Values: ' . print_r($job_type, true));
error_log('Resident Permit Filter Values: ' . print_r($resident_permit, true));
error_log('Salary Filter Values: ' . print_r($salary, true));
error_log('Salary with Discussion Filter Values: ' . print_r($salary_with_discussion, true));

// WP_Query Arguments for Posts with Category Taxonomy
$args = [
    'post_type' => 'post', // Targets only the default 'post' type
    'posts_per_page' => 10,
    'tax_query' => [
        [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => get_queried_object()->slug, // Ensures the query is limited to the current category
        ],
    ],
    'meta_query' => [
        'relation' => 'OR', // Change to 'OR' to match any of the filter conditions
    ],
];

// Add meta queries based on filter selection and log each one
if (!empty($job_type)) {
    $args['meta_query'][] = [
        'key'     => 'job_type',
        'value'   => $job_type,
        'compare' => 'IN',
    ];
    error_log('Adding Job Type to Meta Query: ' . print_r($args['meta_query'], true));
}

if (!empty($resident_permit)) {
    $args['meta_query'][] = [
        'key'     => 'resident_permit',
        'value'   => $resident_permit,
        'compare' => 'IN',
    ];
    error_log('Adding Resident Permit to Meta Query: ' . print_r($args['meta_query'], true));
}

if (!empty($salary)) {
    $args['meta_query'][] = [
        'key'     => 'salary',
        'value'   => $salary,
        'compare' => 'IN',
    ];
    error_log('Adding Salary to Meta Query: ' . print_r($args['meta_query'], true));
}

// Add new filter for "salary_with_discussion"
if (!empty($salary_with_discussion)) {
    $args['meta_query'][] = [
        'key'     => 'salary_with_discussion',
        'value'   => $salary_with_discussion,
        'compare' => 'IN',
    ];
    error_log('Adding Salary with Discussion to Meta Query: ' . print_r($args['meta_query'], true));
}

// Log the full query arguments before execution
error_log('Final WP_Query Arguments: ' . print_r($args, true));

// Run the query
$query = new WP_Query($args);

// Debug: Check if the query found any posts
if ($query->have_posts()) {
    error_log('Query returned posts.');
} else {
    error_log('Query returned no posts.');
}
?>

<!-- Title Section -->
<div class="title-section">
    <h1><?php single_term_title(); ?></h1>
    <p><?php echo category_description(); // Displays the category description ?></p>
</div>

<div class="archive-container">
    <!-- Filter Section -->
    <div class="filter-form">
        <form id="filter-form" method="GET" action="">
            <!-- Job Type Filter -->
            <div class="filter-group">
                <h4>চাকুরীর ধরণ</h4>
                <?php
                $job_types = get_meta_field_values('job_type', 'post');
                foreach ($job_types as $type) :
                    $checked = in_array($type, $job_type) ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="job_type[]" value="<?php echo esc_attr($type); ?>" <?php echo $checked; ?> onchange="this.form.submit()">
                        <?php echo esc_html($type); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Resident Permit Filter -->
            <div class="filter-group">
                <h4>রেসিডেন্ট পারমিট</h4>
                <?php
                $resident_permits = get_meta_field_values('resident_permit', 'post');
                foreach ($resident_permits as $permit) :
                    $checked = in_array($permit, $resident_permit) ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="resident_permit[]" value="<?php echo esc_attr($permit); ?>" <?php echo $checked; ?> onchange="this.form.submit()">
                        <?php echo esc_html($permit); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Salary Filter -->
            <div class="filter-group">
                <h4>বেতন</h4>
                <?php
                $salaries = get_meta_field_values('salary', 'post');
                foreach ($salaries as $salary_value) :
                    $checked = in_array($salary_value, $salary) ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="salary[]" value="<?php echo esc_attr($salary_value); ?>" <?php echo $checked; ?> onchange="this.form.submit()">
                        <?php echo esc_html($salary_value); ?>
                    </label>
                <?php endforeach; ?>
				
				<!-- Salary with Discussion Filter -->
				<?php
				$salary_with_discussions = get_meta_field_values('salary_with_discussion', 'post');
                foreach ($salary_with_discussions as $discussion) :
                    $checked = in_array($discussion, $salary_with_discussion) ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="salary_with_discussion[]" value="<?php echo esc_attr($discussion); ?>" <?php echo $checked; ?> onchange="this.form.submit()">
                        <?php echo esc_html($discussion); ?>
                    </label>
                <?php endforeach; ?>
				
            </div>

        </form>
    </div>

    <!-- Posts Section -->
    <div class="posts-list">
        <div class="posts-grid">
            <?php if ($query->have_posts()) : ?>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="post-item">
                        <div class="post-thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php endif; ?>
                        </div>
                        <div class="post-info">
                            <h2 class="post-title"><?php the_title(); ?></h2>

                            <?php 
                            $job_type = get_post_meta(get_the_ID(), 'job_type', true);
                            if ($job_type) : ?>
                                <p class="post-meta"><strong>চাকুরীর ধরণ:</strong> <?php echo esc_html($job_type); ?></p>
                            <?php endif; ?>

                            <?php 
                            $resident_permit = get_post_meta(get_the_ID(), 'resident_permit', true);
                            if ($resident_permit) : ?>
                                <p class="post-meta"><strong>রেসিডেন্ট পারমিট:</strong> <?php echo esc_html($resident_permit); ?></p>
                            <?php endif; ?>

                            <?php 
                            $salary = get_post_meta(get_the_ID(), 'salary', true);
                            if ($salary) : ?>
                                <p class="post-meta"><strong>বেতন:</strong> <?php echo esc_html($salary); ?></p>
                            <?php endif; ?>

                            <?php 
                            $salary_with_discussion = get_post_meta(get_the_ID(), 'salary_with_discussion', true);
                            if ($salary_with_discussion) : ?>
                                <p class="post-meta"><strong>বেতন:</strong> <?php echo esc_html($salary_with_discussion); ?></p>
                            <?php endif; ?>

                            <a class="read-more" href="<?php the_permalink(); ?>">বিস্তারিত</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p>No posts found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
wp_reset_postdata();
get_footer();

/**
 * Retrieve unique values of a meta field from posts.
 *
 * @param string $meta_key Meta key to search for.
 * @param string $post_type Post type to filter.
 * @return array Unique meta values.
 */
function get_meta_field_values($meta_key, $post_type = 'post')
{
    global $wpdb;
    $values = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = %s
        AND p.post_type = %s
        AND p.post_status = 'publish'
    ", $meta_key, $post_type));

    return array_filter($values); // Remove empty values
}
?>
