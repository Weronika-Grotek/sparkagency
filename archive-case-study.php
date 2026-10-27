<?php
/** Fallback public CPT archive; the editable page is linked from navigation. */
get_header();
echo '<main id="site-content" role="main">';
require get_theme_file_path( '/template-parts/blocks/case-studies-archive.php' );
echo '</main>';
get_footer();
