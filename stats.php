<?php

// Set options
c::set('stats.roles.ignore', 'admin');
c::set('stats.days', 14);
c::set('stats.session', true);
c::set('stats.date.format', 'D M d');
c::set('stats.format', 'absolute');

// Register extensions
$kirby->set('widget',    'stats', __DIR__ . DS . 'widgets' . DS . 'stats');


// Set $page and $site variables

function getCurrentPage() {
	// Get the full request path
	$path = kirby()->request()->path();

	// Strip language prefix from path
	foreach (c::get('languages', []) as $l) {
		$prefix = substr($l['url'], 1);
		if ($prefix and strpos($path, $prefix) === 0) {
			$path = substr($path, str::length($prefix));
			continue;
		}
	}

	// Add homepage slug to path if it's blank
	if ($path == '') $path = c::get('home', '/');

	// Build page object
	$page = page($path);

	// If page doesn't exist, return false
	if (!$page) return false;

	// Return the page object
	return $page;
}
$site = site();
$page = getCurrentPage();
if ($page) {
	$uri = $page->uri();

	/* Check if data should be logged for the current user */

	// Roles for which nothing is logged 
	$ignore = c::get('stats.roles.ignore', "");
	// Number of days to keep per-day totals for. Ensure that this is positive...
	$days = c::get('stats.days', 5);
	$days = ($days < 0) ? 5 : $days;
	// Date format
	$date_format = c::get('stats.date.format', 'd.m.y');

	// Check whether to ignore the current user
	if ($user = $site->user()) {
		// Ignore everybody
		if ($ignore == "_all") {
			return;
		}

		// Multiple rules to be ignored, test each of them
		if (is_array($ignore)) {
			foreach ($ignore as $role) {
				if($user->hasRole($role)) {
					return;
				}
			}
		}

		// Only one rule or empty string if ignoring nobody
		if ($user->hasRole($ignore)) {
			return;
		}
	}

	/* Session mode */

	if (c::get('stats.session', false)) {
		s::start();
		// Get the already visited pages
		$urls = s::get('stats', array());
		// User has visited this page already in this session. Do nothing.
		if (in_array($uri, $urls)) {
			return;
		}
		// User has never been here. Add the url and put back in the session storage
		$urls[] = $uri;
		s::set('stats', $urls);
	}

	// Get or create the kirbystats page
	$stats = page('kirbystats');
	if (!$stats) {
		try {
			$stats = $site->pages()->create('kirbystats', 'stats');
		} catch (Exception $e) {
			echo $e->getMessage();
			exit;
		}
	}

	// Get data
	$data = $stats->pages()->yaml();
	$dates = $stats->dates()->yaml();
	$date = date($date_format);

	if ($data == null) $data = array();
	if ($dates == null) $dates = array();

	// calculate new values
	$val = (array_key_exists($uri, $data)) ? (int) $data[$uri]['count'] + 1 : 1;
	$today = (array_key_exists($date, $dates)) ? (int) $dates[$date]['count'] + 1 : 1;
	$total = (!$stats->total_stats_count()->isEmpty()) ? $stats->total_stats_count()->int() + 1 : 1;

	// update arrays
	$data[$uri] = array('count' => $val);
	$dates[$date] = array('count' => $today);

	try {
		$fields = [
			'pages' => yaml::encode($data),
			'dates' => yaml::encode($dates),
			'total_stats_count' => $total
		];
		if ($site->defaultLanguage() !== null) {
			$stats->update($fields, $site->defaultLanguage()->code());
		} else {
			$stats->update($fields);	
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}
}