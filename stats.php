<?php

// Set options
c::set('stats.roles.ignore','admin');
c::set('stats.days',30);
c::set('stats.session',false);
c::set('stats.date.format','F d');
c::set('stats.format','absolute');

// Register extensions
$kirby->set('snippet',   'stats', __DIR__ . DS . 'snippets' . DS . 'stats.php');
$kirby->set('widget',    'stats', __DIR__ . DS . 'widgets' . DS . 'stats');

/*
Check if kirby language config is enabled.
If it is, we need special rules to omit the language code later */

// Activated
if ($this->site()->multilang()) {
	foreach (c::get('languages') as $language) {
		// remove leading slash
		$url = substr($language['url'], 1);
		$this->options['routes'][] = array(
				'pattern' => $url,
				'action'  => function() use ($language) {
					$home = c::get('home', 'home');
					$s = site()->visit($home, $language['code']);
					snippet('stats', array('param' => $home));
					return $s;
				}
				);
		$this->options['routes'][] = array(
				'pattern' => $url . '/(:all)',
				'action'  => function($param) use ($language) {
					$s = site()->visit($param, $language['code']);
					snippet('stats', array('param' => $param));
					return $s;
				}
				);
	}
}

// Deactivated
else {
	$this->options['routes'][] = array(
			'pattern' => '/(:all?)',
			'action'  => function($param) {
				$s = site()->visit($param);
				snippet('stats', array('param' => $param));
				return $s;
			}
			);
	$this->options['routes'][] = array(
			'pattern' => '(:any)',
			'action'  => function($param) {
				$s = site()->visit($param);
				snippet('stats', array('param' => $param));
				return $s;
			}
			);
	$this->options['routes'][] = array(
			'pattern' => '(:any)/(:all)',
			'action'  => function($param, $param2) {
				$param = $param . "/" . $param2;
				$s = site()->visit($param);
				snippet('stats', array('param' => $param));
				return $s;
			}
			);
}
