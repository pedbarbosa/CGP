<?php

require_once 'conf/common.inc.php';
require_once 'inc/html.inc.php';
require_once 'inc/collectd.inc.php';

header("Content-Type: text/html");

$host = GET('h');
$plugin = GET('p');
$seconds = GET('s');

$selected_plugins = !$plugin ? $CONFIG['overview'] : array($plugin);

html_start();

printf("<fieldset id=\"%s\">", htmlentities($host));
printf("<legend>%s</legend>", htmlentities($host));

		echo <<<EOT
<input type="checkbox" id="navicon" class="navicon" />
<label for="navicon"></label>

EOT;

if (!strlen($host ?? '') || !$plugins = collectd_plugins($host)) {
	echo "Unknown host\n";
	return false;
}

plugins_list($host, $selected_plugins, $seconds);

echo '<div class="graphs">';
$time_range_shown = false;
foreach ($selected_plugins as $selected_plugin) {
	if (in_array($selected_plugin, $plugins)) {
		plugin_header($host, $selected_plugin);
		# one time range for all graphs on the page, under the first header
		# as on the detail page; without one, each plugin uses its default
		# from $CONFIG['time_range']
		if (!$time_range_shown) {
			time_range_list('host.php', array('h' => $host, 'p' => $plugin),
				$seconds ?? ($plugin ? plugin_time_range($plugin) : $CONFIG['time_range']['default']));
			$time_range_shown = true;
		}
		graphs_from_plugin($host, $selected_plugin, empty($plugin), $seconds);
	}
}
echo '</div>';
printf("</fieldset>");

html_end();
