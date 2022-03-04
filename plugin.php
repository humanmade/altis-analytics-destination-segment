<?php
/**
 * Plugin Name: Altis Analytics Integration for Segment
 * Description: Integration for Altis Analtysics and Segment service.
 * Version: 1.0
 * Author: Human Made Limited
 * Author URI: https://humanmade.com/
 *
 * @package altis-analytics-integration-segment
 */

namespace Altis\Analytics\Integration\Segment;

const ROOT_DIR = __DIR__;
const ROOT_FILE = __FILE__;

// Check if this is installed as a self contained built version.
if ( file_exists( ROOT_DIR . '/vendor/autoload.php' ) ) {
	require_once ROOT_DIR . '/vendor/autoload.php';
}

require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/export/namespace.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
