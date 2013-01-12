<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library extends the CodeIgniter CI_Language class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Lang.php
 *
 * @copyright	Copyright (c) 2011 Wiredesignz
 * @version 	5.4
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Lang extends CI_Lang
{
	public function load($langfile, $lang = '', $return = FALSE, $_module = NULL)	{
		
		if (is_array($langfile)) return $this->load_many($langfile);
			
        // ClearFoundation 
        // use /etc/sysconfig/i18n which is cached in PHP format to keep things snappy.
        if (CI::$APP->session->userdata('lang_code')) {
            $idiom = CI::$APP->session->userdata('lang_code');
        } else if (file_exists(CLEAROS_TEMP_DIR . '/language_cache.php')) {
            include CLEAROS_TEMP_DIR . '/language_cache.php';
            $idiom = $language;
        } else {
            $deft_lang = CI::$APP->config->item('language');
            $idiom = ($lang == '') ? $deft_lang : $lang;
        }
	
		if (in_array($langfile.'_lang', $this->is_loaded, TRUE))
			return $this->language;
	
		$_module OR $_module = CI::$APP->router->fetch_module();

		// ClearFoundation
        // - fall back to en_US if translation is unavailable
        // - add helper for translators
        //
        // In devel mode, we tack on the en_US translations to $translations.
        // This is used in system/core/Lang.php to see if the translation exists.

        list($path, $_langfile) = Modules::find($langfile.'_lang', $_module, 'language/'.$idiom.'/');

		if ($path === FALSE) {
            list($path, $_langfile) = Modules::find($langfile.'_lang', $_module, 'language/en_US/');
        }

		if ($path === FALSE) {
			// if ($lang = parent::load($langfile, $lang, $return)) return $lang;
		} else {
			if($lang = Modules::load_file($_langfile, $path, 'lang')) {
                // KLUDGE: add special language mapping file
                if ($_langfile == 'base_lang')
                    include clearos_app_base('base') . '/language/en_US/base_framework_lang.php';

				if ($return) return $lang;

                // Developer mode -- add the "translated" info which holds state of all translated tags
                if (file_exists('/etc/clearos/devel.d/translator_mode')) {
                    $translated_file = '/var/clearos/base/translations/base/trunk/language/' . $idiom . '/translated.php';

                    if ($idiom == 'en_US') {
                        $this->language['is_en_us'] = TRUE;
                    } else if (file_exists($translated_file) && (!isset($this->language['is_translated']))) {
                        include $translated_file;
                        $this->language['is_translated'] = $translated;
                        $this->language['is_en_us'] = FALSE;
                    }
                }

				$this->language = array_merge($this->language, $lang);
				$this->is_loaded[] = $langfile.'_lang';
				unset($lang);
			}
		}
		
		return $this->language;
	}

	/** Load an array of language files **/
	private function load_many($languages) {
		foreach ($languages as $_langfile) $this->load($_langfile);	
	}
}
