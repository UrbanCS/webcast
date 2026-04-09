<?php
/**
 * Shortcode WordPress pour afficher un événement Lifstories Broadcast via iframe.
 * Usage: [lifstories_broadcast slug="hommage-paris"]
 */
function lifstories_broadcast_shortcode($atts) {
    $atts = shortcode_atts([
        'slug' => '',
        'base_url' => 'https://example.com/lifstories-broadcast',
        'height' => '860',
    ], $atts, 'lifstories_broadcast');

    if (empty($atts['slug'])) {
        return '';
    }

    $src = trailingslashit($atts['base_url']) . 'event.php?slug=' . rawurlencode($atts['slug']) . '&embed=1';

    return sprintf(
        '<div style="position:relative;padding-top:56.25%%;"><iframe src="%s" loading="lazy" style="position:absolute;inset:0;width:100%%;height:100%%;border:0;border-radius:18px;" allowfullscreen></iframe></div>',
        esc_url($src)
    );
}
add_shortcode('lifstories_broadcast', 'lifstories_broadcast_shortcode');
