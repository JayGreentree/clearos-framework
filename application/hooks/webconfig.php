<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2010 ClearFoundation
//
//////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

/**
 * ClearOS webconfig session handling.
 *
 * The session handling is done through a CodeIngiter hook.
 *
 * @package Framework
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// FIXME - consider moving boostrap and dependencies since sessions only need
// to load one time at the start of the process
clearos_load_library('base/Webconfig');

///////////////////////////////////////////////////////////////////////////////
// T H E M E  L O A D E R
///////////////////////////////////////////////////////////////////////////////

function webconfig_theme_loader()
{
	ClearOsLogger::Profile(__METHOD__, __LINE__);

	$framework =& get_instance();

	$theme_file = ClearOsConfig::GetThemePath($framework->session->userdata('theme')) . '/widgets/theme.php';

	if (file_exists($theme_file)) {
		require_once($theme_file);
	} else {
		// FIXME
		echo "The theme file is missing";
	}
}

///////////////////////////////////////////////////////////////////////////////
// S E S S I O N
///////////////////////////////////////////////////////////////////////////////

function webconfig_session()
{
	ClearOsLogger::Profile(__METHOD__, __LINE__);

	$CI =& get_instance();

	if ($CI->session->userdata('session_started'))
		return;

	$webconfig = new Webconfig();

	// Hostname
	//---------

	$session['hostname'] = '';

	if (file_exists(COMMON_CORE_DIR . "/api/Hostname.php")) {
		require_once(COMMON_CORE_DIR . "/api/Hostname.php");

		try {
			$hostname = new Hostname();
			$session['hostname'] = $hostname->Get();
		} catch (Exception $e) {
			// Use default
		}
	}

	// Check registration
	//-------------------

	$session['registered'] = FALSE;

	if (file_exists(COMMON_CORE_DIR . "/api/Register.php")) {
		require_once(COMMON_CORE_DIR . "/api/Register.php");

		try {
			$register = new Register();
			$session['registered'] = $register->GetStatus();
		} catch (Exception $e) {
			// Use default
		}
	}

	// Language
	//---------

	$session['locale'] = 'en_US';
	$session['charset'] = 'utf-8';
	$session['textdir'] = 'LTR';

	if (file_exists(COMMON_CORE_DIR . "/api/Locale.php")) {
		require_once(COMMON_CORE_DIR . "/api/Locale.php");

		try {
			$locale = new Locale();
			$session['locale'] = $locale->GetLanguageCode();
			$session['charset'] = $locale->GetCharacterSet();
			$session['textdir'] = $locale->GetTextDirection();
		} catch (Exception $e) {
			// Use default
		}
	}

	setlocale(LC_ALL, $session['locale']);

	// Product Info
	//-------------

	$session['osname'] = 'Linux';
	$session['osversion'] = '2.6';
	$session['redirect'] = '';

	if (file_exists(COMMON_CORE_DIR . "/api/Product.php")) {
		require_once(COMMON_CORE_DIR . "/api/Product.php");

		try {
			$product = new Product();
			$session['osname'] = $product->GetName();
			$session['osversion'] = $product->GetVersion();
			$session['redirect'] = $product->GetRedirectUrl() . "/" . preg_replace("/ /", "_", $osname) . "/" . $osversion;
		} catch (Exception $e) {
			// Use default
		}
	} else if (file_exists(COMMON_CORE_DIR . "/api/Os.php")) {
		require_once(COMMON_CORE_DIR . "/api/Os.php");

		try {
			$os = new Os();
			$osname = $os->GetName();
			$osversion = $os->GetVersion();
		} catch (Exception $e) {
			// Use default
		}
	}

	// Hostkey
	//--------

	// FIXME: avoid this
	$session['hostkey'] = "hostkey";

	if (file_exists(COMMON_CORE_DIR . "/api/Suva.php")) {
		require_once(COMMON_CORE_DIR . "/api/Suva.php");

		try {
			$suva = new Suva();
			$session['hostkey'] = $suva->GetHostkey();
		} catch (Exception $e) {
			// Use default
		}
	}

	// Theme
	//------

	$session['theme'] = "clearos6x";
	$session['theme_mode'] = 'normal';

	if (file_exists(COMMON_CORE_DIR . "/api/Webconfig.php")) {
		require_once(COMMON_CORE_DIR . "/api/Webconfig.php");

		try {
			$session['theme'] = $webconfig->GetTemplate();
			$session['theme_mode'] = 'normal';
		} catch (Exception $e) {
			// Use default
		}
	}

	// Other
	//------

	// FIXME - messy?
	$session['sdn_redirect'] = 'https://secure.clearcenter.com/redirect';
	$session['online_help'] = 'https://secure.clearcenter.com/redirect/userguide';
	$session['session_started'] = TRUE;

	// Set the session
	//----------------

	$CI =& get_instance();
	$CI->session->set_userdata($session);
}