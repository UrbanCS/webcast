<?php
/**
 * Template Name: Lifstories Broadcast Event
 *
 * Ajustez le chemin absolu vers le module avant usage.
 */
get_header();

$_GET['slug'] = 'hommage-paris';
$_GET['embed'] = '1';

require '/home/your-cpanel-user/public_html/lifstories-broadcast/event.php';

get_footer();
