<?php
/**
 * LaraClassified - Geo Classified Ads CMS
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

use PulkitJalan\GeoIP\GeoIP;

use App\Helpers\Ip;
/**
 * Check if a Model has translation fields
 *
 * @param $model
 * @return bool
 */
function isTranlatableModel($model)
{
	$isTranslatable = false;
	
	try {
		if (!($model instanceof \Illuminate\Database\Eloquent\Model)) {
			return $isTranslatable;
		}
		
		if (
			property_exists($model, 'translatable')
			&& (
				isset($model->translatable)
				&& is_array($model->translatable)
				&& !empty($model->translatable)
			)
		) {
			$isTranslatable = true;
		}
	} catch (\Exception $e) {
		return false;
	}
	
	return $isTranslatable;
}

/**
 * Hide part of the Email addresses
 *
 * @param $value
 * @param bool $hideAllUsernameCharacters
 * @return string
 */
function hidePartOfEmail($value, $hideAllUsernameCharacters = false)
{
    $tmp = explode('@', $value);
    if (isset($tmp[0]) && isset($tmp[1])) {
        $emailUsername = $tmp[0];
        $emailDomain = $tmp[1];
        
        if ($hideAllUsernameCharacters) {
			$hideUsername = str_pad('', strlen($emailUsername), "x");
		} else {
			$hideStr = str_pad('', strlen($emailUsername) - 2, "x");
			$hideUsername = substr($emailUsername, 0, 1) . $hideStr . substr($emailUsername, -1);
		}
		
        $value = $hideUsername . '@' . $emailDomain;
    }
    
    return $value;
}

/**
 * Default translator (e.g. en/global.php)
 *
 * @param $string
 * @param array $replace
 * @param string $file
 * @param null $locale
 * @return string|\Symfony\Component\Translation\TranslatorInterface
 */
function t($string, $replace = [], $file = 'global', $locale = null)
{
    if (is_null($locale)) {
        $locale = config('app.locale');
    }
    
    return trans($file . '.' . $string, $replace, $locale);
}

/**
 * Get default max file upload size (from PHP.ini)
 *
 * @return mixed
 */
function maxUploadSize()
{
    $maxUpload = (int)(ini_get('upload_max_filesize'));
    $maxPost = (int)(ini_get('post_max_size'));
    
    return min($maxUpload, $maxPost);
}

/**
 * Get max file upload size
 *
 * @return int|mixed
 */
function maxApplyFileUploadSize()
{
    $size = maxUploadSize();
    if ($size >= 5) {
        return 5;
    }
    
    return $size;
}

/**
 * Escape JSON string
 *
 * Escape this:
 * \b  Backspace (ascii code 08)
 * \f  Form feed (ascii code 0C)
 * \n  New line
 * \r  Carriage return
 * \t  Tab
 * \"  Double quote
 * \\  Backslash caracter
 *
 * @param $value
 * @return mixed
 */
function escapeJsonString($value)
{
    // list from www.json.org: (\b backspace, \f formfeed)
    $escapers = ["\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"];
    $replacements = ["\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"];
    $value = str_replace($escapers, $replacements, trim($value));
    
    return $value;
}

/**
 * @param bool $canGetLocalIp
 * @param string $defaultIp
 * @return string
 */
function getIp($canGetLocalIp = true, $defaultIp = '')
{
    return \App\Helpers\Ip::get($canGetLocalIp, $defaultIp);
}

/**
 * @return string
 */
function getScheme()
{
    if (isset($_SERVER['HTTPS']) and in_array($_SERVER['HTTPS'], ['on', 1])) {
        $protocol = 'https://';
    } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $protocol = 'https://';
    } else if (stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }
    
    return $protocol;
}


/**
 * Get host (domain with sub-domain)
 *
 * @param null $url
 * @return array|mixed|string
 */
function getHost($url = null)
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = (trim(request()->server('HTTP_HOST')) != '') ? request()->server('HTTP_HOST') : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	}
    
    if ($host == '') {
		$host = parse_url(url()->current(), PHP_URL_HOST);
    }
    
    return $host;
}

/**
 * Get domain (host without sub-domain)
 *
 * @param null $url
 * @return string
 */
function getDomain($url = null)
{
	if (!empty($url)) {
		$host = parse_url($url, PHP_URL_HOST);
	} else {
		$host = getHost();
	}
	
    $tmp = explode('.', $host);
	if (count($tmp) > 2) {
		$itemsToKeep = count($tmp) - 2;
		$tlds = config('tlds');
		if (isset($tmp[$itemsToKeep]) && isset($tlds[$tmp[$itemsToKeep]])) {
			$itemsToKeep = $itemsToKeep - 1;
		}
		for ($i=0; $i < $itemsToKeep; $i++) {
			array_forget($tmp, $i);
		}
		$domain = implode('.', $tmp);
	} else {
		$domain = @implode('.', $tmp);
	}
    
    return $domain;
}

/**
 * Get sub-domain name
 *
 * @return string
 */
function getSubDomainName()
{
    $host = getHost();
    $name = (substr_count($host, '.') > 1) ? trim(current(explode('.', $host))) : '';
    
    return $name;
}

/**
 * Generate a querystring url for the application.
 *
 * Assumes that you want a URL with a querystring rather than route params
 * (which is what the default url() helper does)
 *
 * @param null $path
 * @param array $inputArray
 * @param null $secure
 * @param bool $localized
 * @return mixed|string
 */
function qsurl($path = null, $inputArray = [], $secure = null, $localized = true)
{
    if ($localized) {
        $url = lurl($path);
    } else {
        $url = app('url')->to($path, $secure);
    }
    
    if (!empty($inputArray)) {
        $url = $url . '?' . httpBuildQuery($inputArray);
    }
    
    return $url;
}

/**
 * @param $array
 * @return mixed|string
 */
function httpBuildQuery($array)
{
	if (!is_array($array) && !is_object($array)) {
		return $array;
	}
	
    $queryString = http_build_query($array);
    $queryString = str_replace(['%5B', '%5D'], ['[', ']'], $queryString);
    
    return $queryString;
}

/**
 * Localized URL
 *
 * @param null $path
 * @param array $attributes
 * @param null $locale
 * @return bool|\Illuminate\Contracts\Routing\UrlGenerator|mixed|null|string
 */
function lurl($path = null, $attributes = [], $locale = null)
{
    if (empty($locale)) {
        $locale = config('app.locale');
    }
    
    if (request()->segment(1) == admin_uri()) {
        return url($locale . '/' . $path);
    }
    
    return \Larapen\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($locale, $path, $attributes);
}

/**
 * Get URL related to the given country (or country code)
 *
 * @param $country
 * @param string $path
 * @param bool $forceCountry
 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
 */
function localUrl($country, $path = '/', $forceCountry = false)
{
	if (empty($path)) {
		$path = '/';
	}
	
	// If given country value is a string & having 2 characters (like country code),
	// Get the country collection by the country code.
	if (is_string($country)) {
		if (strlen($country) == 2) {
			$country = \App\Helpers\Localization\Country::getCountryInfo($country);
			if ($country->isEmpty() || !$country->has('code')) {
				return url($path);
			}
		} else {
			return url($path);
		}
	}
	
	// Country collection is required to continue
	if (!($country instanceof \Illuminate\Support\Collection)) {
		return url($path);
	}
	
	// Country collection code is required to continue
	if (!$country->has('code')) {
		return url($path);
	}
	
	// Clear the path
	$path = ltrim($path, '/');
	
	// Get the country main language path
	$langPath = '';
	if ($country->has('lang') && $country->get('lang')->has('abbr')) {
		$langPath = '/' . $country->get('lang')->get('abbr');
	} else {
		if ($country->has('languages')) {
			$countryLang = \App\Helpers\Localization\Country::getLangFromCountry($country->get('languages'));
			if ($countryLang->has('abbr')) {
				$langPath = '/' . $countryLang->get('abbr');
			}
		} else {
			// From XML Sitemaps
			if ($country->has('locale')) {
				$langPath = '/' . $country->get('locale');
			}
		}
	}
	
	// Get the country domain data from the Domain Mapping plugin,
	// And get a new URL related to domain, country language & given path
	$domain = collect((array)config('domains'))->firstWhere('country_code', $country->get('code'));
	if (isset($domain['url']) && !empty($domain['url'])) {
		$path = preg_replace('#' . $country->get('code') . '/#ui', '', $path, 1);
		
		$url = rtrim($domain['url'], '/') . $langPath;
		$url = $url . ((!empty($path)) ? '/' . $path : '');
	} else {
		$url = rtrim(env('APP_URL', ''), '/') . $langPath;
		$url = $url . ((!empty($path)) ? '/' . $path : '');
		if ($forceCountry) {
			$url = $url . ('?d=' . $country->get('code'));
		}
	}
	
	return $url;
}

/**
 * If the Domain Mapping plugin is installed, apply its configs.
 * NOTE: Don't apply them if the session is shared.
 *
 * @param $countryCode
 */
function applyDomainMappingConfig($countryCode)
{
	if (empty($countryCode)) {
		return;
	}
	
	if (config('plugins.domainmapping.installed')) {
		/*
		 * When the session is shared, the domains name and logo columns are disabled.
		 * The dashboard per country feature is also disabled.
		 * So, it is recommended to access to the Admin panel through the main URL from the /.env file (i.e. APP_URL/admin)
		 */
		if (!config('settings.domain_mapping.share_session')) {
			$domain = collect((array)config('domains'))->firstWhere('country_code', $countryCode);
			if (!empty($domain)) {
				if (isset($domain['url']) && !empty($domain['url'])) {
					//\URL::forceRootUrl($domain['url']);
				}
			}
		}
	}
}

/**
 * Check if user is using the API
 *
 * @return bool
 */
function isFromApi()
{
	$isFromApi = false;
	if (
		request()->segment(1) == 'api'
		&& str_contains(\Route::currentRouteAction(), plugin_namespace('api'))
	) {
		// Check the API Plugin
		$isFromApi = config('plugins.api.installed');
	}
	
	return $isFromApi;
}

/**
 * Check if user is located in the Admin panel
 * NOTE: Please see the provider of the package: lab404/laravel-impersonate
 *
 * @return bool
 */
function isFromAdminPanel()
{
	$isFromAdmin = false;
	if (
		request()->segment(1) == admin_uri() ||
		request()->segment(1) == 'impersonate' ||
		str_contains(\Route::currentRouteAction(), '\Admin\\')
	) {
		$isFromAdmin = true;
	}
	
	return $isFromAdmin;
}

/**
 * Check the demo website domain
 *
 * @return bool
 */
function isDemoDomain()
{
	return getDomain() == config('larapen.core.demo.domain') || in_array(getHost(), (array)config('larapen.core.demo.hosts'));
}

/**
 * @return bool
 */
function isDemo()
{
	if (isDemoDomain()) {
		if (auth()->check()) {
			if (isFromAdminPanel()) {
				if (auth()->user()->can(\App\Models\Permission::getStaffPermissions()) && auth()->user()->id == 1) {
					return false;
				}
				
				return true;
			} else {
				if (!in_array(auth()->user()->id, [2, 3])) {
					return false;
				}
				if (
					!str_contains(\Route::currentRouteAction(), 'Post\CreateController') &&
					!str_contains(\Route::currentRouteAction(), 'Post\EditController') &&
					!str_contains(\Route::currentRouteAction(), 'Post\PhotoController') &&
					!str_contains(\Route::currentRouteAction(), 'Post\PaymentController') &&
					!str_contains(\Route::currentRouteAction(), 'Post\DetailsController') &&
					!str_contains(\Route::currentRouteAction(), 'Post\ReportController') &&
					!str_contains(\Route::currentRouteAction(), 'Ajax\PostController') &&
					!str_contains(\Route::currentRouteAction(), 'Account\ConversationsController') &&
					!str_contains(\Route::currentRouteAction(), 'Auth\LoginController')
				) {
					return true;
				}
			}
		}
	}
	
	return false;
}

/**
 * File Size Format
 *
 * @param $bytes
 * @return string
 */
function fileSizeFormat($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    
    return $bytes;
}

/**
 * Check if the current Locale should be hidden in the URL
 *
 * @param null $locale
 * @return bool
 */
function currentLocaleShouldBeHiddenInUrl($locale = null)
{
	if (empty($locale)) {
		$locale = config('app.locale');
	}
	
	if (config('laravellocalization.hideDefaultLocaleInURL') == true && $locale == config('appLang.abbr')) {
		return true;
	}
	
	return false;
}

/**
 * @param $index
 * @param null $default
 * @return mixed
 */
function getSegment($index, $default = null)
{
    $index = (int)$index;
    
    // Default checking
    $segment = request()->segment($index, $default);
    
    // Checking with Default Language parameters
    if (!ends_with(request()->url(), '.xml')) {
        if (!currentLocaleShouldBeHiddenInUrl()) {
            $segment = request()->segment(($index + 1), $default);
        }
    }
    
    return $segment;
}

/**
 * Check if file is uploaded
 *
 * @param $value
 * @return bool
 */
function fileIsUploaded($value)
{
	$isUploaded = false;
	
	if (
		(is_string($value) && starts_with($value, 'data:image'))
		|| ($value instanceof \Illuminate\Http\UploadedFile)
	) {
		$isUploaded = true;
	}
	
	return $isUploaded;
}

/**
 * Get the uploaded file extension
 *
 * @param $value
 * @return mixed|null|string
 */
function getUploadedFileExtension($value)
{
	$extension = null;
	
	if (!is_string($value)) {
		if ($value instanceof \Illuminate\Http\UploadedFile) {
			$extension = $value->getClientOriginalExtension();
		}
	} else {
		if (starts_with($value, 'data:image')) {
			$matches = [];
			preg_match('#data:image/([^;]+);base64#', $value, $matches);
			$extension = (isset($matches[1]) && !empty($matches[1])) ? $matches[1] : 'png';
		} else {
			$extension = getExtension($value);
		}
	}
	
	return $extension;
}

/**
 * Get file extension
 *
 * @param $filename
 * @return mixed
 */
function getExtension($filename)
{
    $tmp = explode('?', $filename);
    $tmp = explode('.', current($tmp));
    $ext = end($tmp);
    
    return $ext;
}

/**
 * Transform Description column before displaying it
 *
 * @param $string
 * @return mixed|string
 */
function transformDescription($string)
{
	if (config('settings.single.simditor_wysiwyg') || config('settings.single.ckeditor_wysiwyg')) {
		
		try {
			$string = \Mews\Purifier\Facades\Purifier::clean($string);
		} catch (\Exception $e) {
			// Nothing.
		}
		$string = createAutoLink($string);
		
	} else {
		$string = nl2br(createAutoLink(strCleaner($string)));
	}
	
	return $string;
}

/**
 * String strip
 *
 * @param $string
 * @return string
 */
function str_strip($string)
{
    $string = trim(preg_replace('/\s\s+/u', ' ', $string));
    
    return $string;
}

/**
 * String cleaner
 *
 * @param $string
 * @return mixed|string
 */
function strCleaner($string)
{
    $string = strip_tags($string, '<br><br/>');
    $string = str_replace(['<br>', '<br/>', '<br />'], "\n", $string);
    $string = preg_replace("/[\r\n]+/", "\n", $string);
    /*
    Remove 4(+)-byte characters from a UTF-8 string
    It seems like MySQL does not support characters with more than 3 bytes in its default UTF-8 charset.
    NOTE: you should not just strip, but replace with replacement character U+FFFD to avoid unicode attacks, mostly XSS:
    http://unicode.org/reports/tr36/#Deletion_of_Noncharacters
    */
    $string = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $string);
    $string = mb_ucfirst(trim($string));
    
    return $string;
}

/**
 * String cleaner (Lite version)
 *
 * @param $string
 * @return mixed|string
 */
function strCleanerLite($string)
{
	$string = strip_tags($string);
	$string = html_entity_decode($string);
	$string = strip_tags($string);
	$string = preg_replace('/[\'"]*(<|>)[\'"]*/us', '', $string);
	$string = trim($string);
	
	return $string;
}

/**
 * Only numeric string cleaner
 *
 * @param $string
 * @return null
 */
function onlyNumCleaner($string)
{
	$tmpString = preg_replace('/\d/u', '', $string);
	if ($tmpString == '') {
		$string = null;
	}
	
	return $string;
}

/**
 * Extract emails from string, and get the first
 *
 * @param $string
 * @return string
 */
function extractEmailAddress($string)
{
    $tmp = [];
    preg_match_all('|([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b)|i', $string, $tmp);
    $emails = (isset($tmp[1])) ? $tmp[1] : [];
    $email = head($emails);
    if ($email == '') {
        $tmp = [];
        preg_match("|[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})|i", $string, $tmp);
        $email = (isset($tmp[0])) ? trim($tmp[0]) : '';
        if ($email == '') {
            $tmp = [];
            preg_match("|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b|i", $string, $tmp);
            $email = (isset($tmp[0])) ? trim($tmp[0]) : '';
        }
    }
    
    return strtolower($email);
}

/**
 * Check if language code is available
 *
 * @param $abbr
 * @return bool
 */
function isAvailableLang($abbr)
{
    $cacheExpiration = (int)config('settings.other.cache_expiration', 60);
    $lang = \Illuminate\Support\Facades\Cache::remember('language.' . $abbr, $cacheExpiration, function () use ($abbr) {
        $lang = \App\Models\Language::where('abbr', $abbr)->first();
        return $lang;
    });
    
    if (!empty($lang)) {
        return true;
    } else {
        return false;
    }
}

function getHostByUrl($url)
{
    // in case scheme relative URI is passed, e.g., //www.google.com/
    $url = trim($url, '/');
    
    // If scheme not included, prepend it
    if (!preg_match('#^http(s)?://#', $url)) {
        $url = 'http://' . $url;
    }
    
    $urlParts = parse_url($url);
    
    // remove www
    $domain = preg_replace('/^www\./', '', $urlParts['host']);
    
    return $domain;
}

/**
 * Add rel=”nofollow” to links
 *
 * @param $html
 * @param null $skip
 * @return mixed
 */
function noFollow($html, $skip = null)
{
    return preg_replace_callback(
        "#(<a[^>]+?)>#is", function ($mach) use ($skip) {
        return (
            !($skip && strpos($mach[1], $skip) !== false) &&
            strpos($mach[1], 'rel=') === false
        ) ? $mach[1] . ' rel="nofollow">' : $mach[0];
    },
        $html
    );
}

/**
 * Create auto-links for URLs in string
 *
 * @param $str
 * @param array $attributes
 * @return mixed|string
 */
function createAutoLink($str, $attributes = [])
{
    // Transform URL to HTML link
    $attrs = '';
    foreach ($attributes as $attribute => $value) {
        $attrs .= " {$attribute}=\"{$value}\"";
    }
    
    $str = ' ' . $str;
    $str = preg_replace('`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i', '$1<a rel="nofollow" href="$2"' . $attrs . ' target="_blank">$2</a>', $str);
    $str = substr($str, 1);
    
    // Add rel=”nofollow” to links
    $parse = parse_url('http://' . $_SERVER['HTTP_HOST']);
    $str = noFollow($str, $parse['host']);
    
    // Find and attach target="_blank" to all href links from text
	$str = openLinksInNewWindow($str);
    
    return $str;
}

/**
 * Find and attach target="_blank" to all href links from text
 *
 * @param $content
 * @return mixed
 */
function openLinksInNewWindow($content)
{
	// Find all links
	preg_match_all('/<a ((?!target)[^>])+?>/ui', $content, $hrefMatches);
	
	// Loop only first array to modify links
	if (is_array($hrefMatches) && isset($hrefMatches[0])) {
		foreach ($hrefMatches[0] as $key => $value) {
			// Take orig link
			$origLink = $value;
			
			// Does it have target="_blank"
			if (!preg_match('/target="_blank"/ui', $origLink)) {
				// Add target = "_blank"
				$newLink = preg_replace("/<a(.*?)>/ui", "<a$1 target=\"_blank\">", $origLink);
				
				// Replace old link in content with new link
				$content = str_replace($origLink, $newLink, $content);
			}
		}
	}
	
	return $content;
}

/**
 * Add target=_blank to outside links
 *
 * @param $content
 * @return null|string|string[]
 */
function openOutsideLinksInNewWindow($content)
{
	// Remove existing "target" attribute
	$content = preg_replace('# target=[\'"]?[^\'"]*[\'"]?#ui', '', $content);
	
	// Add target=_blank to outside links
	$pattern = '#(<a\\b[^<>]*href=[\'"]?http[^<>]+)>#ui';
	$replace = '$1 target="_blank">';
	$content = preg_replace($pattern, $replace, $content);
	
	return $content;
}

/**
 * Check tld is a valid tld
 *
 * @param $url
 * @return bool|int
 */
function checkTld($url)
{
    $parsed_url = parse_url($url);
    if ($parsed_url === false) {
        return false;
    }
    
    $tlds = config('tlds');
    $patten = implode('|', array_keys($tlds));
    
    return preg_match('/\.(' . $patten . ')$/i', $parsed_url['host']);
}

/**
 * Function to convert hex value to rgb array
 *
 * @param $colour
 * @return array|bool
 *
 * @todo: improve this function
 */
function hex2rgb($colour)
{
    if ($colour[0] == '#') {
        $colour = substr($colour, 1);
    }
    if (strlen($colour) == 6) {
        list($r, $g, $b) = [$colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]];
    } elseif (strlen($colour) == 3) {
        list($r, $g, $b) = [$colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]];
    } else {
        return false;
    }
    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);
    
    return ['r' => $r, 'g' => $g, 'b' => $b];
}

/**
 * Convert hexdec color string to rgb(a) string
 *
 * @param $color
 * @param bool $opacity
 * @return string
 *
 * @todo: improve this function
 */
function hex2rgba($color, $opacity = false)
{
    $default = 'rgb(0,0,0)';
    
    //Return default if no color provided
    if (empty($color)) {
        return $default;
    }
    
    //Sanitize $color if "#" is provided
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }
    
    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
    } elseif (strlen($color) == 3) {
        $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
    } else {
        return $default;
    }
    
    //Convert hexadec to rgb
    $rgb = array_map('hexdec', $hex);
    
    //Check if opacity is set(rgba or rgb)
    if ($opacity) {
        if (abs($opacity) > 1) {
            $opacity = 1.0;
        }
        $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(",", $rgb) . ')';
    }
    
    // Return rgb(a) color string
    return $output;
}

/**
 * ucfirst() function for multibyte character encodings
 *
 * @param $string
 * @param string $encoding
 * @return string
 */
function mb_ucfirst($string, $encoding = 'utf-8')
{
	if (empty($string) || !is_string($string)) {
		return null;
	}
	
    $strLen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strLen - 1, $encoding);
    
    return mb_strtoupper($firstChar, $encoding) . $then;
}

/**
 * ucwords() function for multibyte character encodings
 *
 * @param $string
 * @param string $encoding
 * @return null|string
 */
function mb_ucwords($string, $encoding = 'utf-8')
{
	if (empty($string) || !is_string($string)) {
		return null;
	}
	
	$tab = [];
	
	// Split the phrase by any number of space characters, which include " ", \r, \t, \n and \f
	$words = preg_split('/[\s]+/ui', $string);
	if (!empty($words)) {
		foreach ($words as $key => $word) {
			$tab[$key] = mb_ucfirst($word, $encoding);
		}
	}
	
	$string = (!empty($tab)) ? implode(' ', $tab) : null;
	
	return $string;
}

/**
 * parse_url() function for multibyte character encodings
 *
 * @param $url
 * @return mixed
 */
function mb_parse_url($url)
{
    $enc_url = preg_replace_callback('%[^:/@?&=#]+%usD', function ($matches) {
        return urlencode($matches[0]);
    }, $url);
    
    $parts = parse_url($enc_url);
    
    if ($parts === false) {
        throw new \InvalidArgumentException('Malformed URL: ' . $url);
    }
    
    foreach ($parts as $name => $value) {
        $parts[$name] = urldecode($value);
    }
    
    return $parts;
}

/**
 * Friendly UTF-8 URL for all languages
 *
 * @param $string
 * @param string $separator
 * @return mixed|string
 */
function slugify($string, $separator = '-')
{
    // Remove accents using WordPress API method.
    $string = remove_accents($string);
    
    // Slug
    $string = mb_strtolower($string);
    $string = @trim($string);
    $replace = "/(\\s|\\" . $separator . ")+/mu";
    $subst = $separator;
    $string = preg_replace($replace, $subst, $string);
    
    // Remove unwanted punctuation, convert some to '-'
    $puncTable = [
        // remove
        "'"  => '',
        '"'  => '',
        '`'  => '',
        '='  => '',
        '+'  => '',
        '*'  => '',
        '&'  => '',
        '^'  => '',
        ''   => '',
        '%'  => '',
        '$'  => '',
        '#'  => '',
        '@'  => '',
        '!'  => '',
        '<'  => '',
        '>'  => '',
        '?'  => '',
        // convert to minus
        '['  => '-',
        ']'  => '-',
        '{'  => '-',
        '}'  => '-',
        '('  => '-',
        ')'  => '-',
        ' '  => '-',
        ','  => '-',
        ';'  => '-',
        ':'  => '-',
        '/'  => '-',
        '|'  => '-',
        '\\' => '-',
    ];
    $string = str_replace(array_keys($puncTable), array_values($puncTable), $string);
    
    // Clean up multiple '-' characters
    $string = preg_replace('/-{2,}/', '-', $string);
    
    // Remove trailing '-' character if string not just '-'
    if ($string != '-') {
        $string = rtrim($string, '-');
    }
    
    return $string;
}

/**
 * @return mixed|string
 */
function detectLocale()
{
    $lang = detectLanguage();
    $locale = (isset($lang) and !$lang->isEmpty()) ? $lang->get('locale') : 'en_US';
    
    return $locale;
}

/**
 * @return \Illuminate\Support\Collection|static
 */
function detectLanguage()
{
    $obj = new App\Helpers\Localization\Language();
    $lang = $obj->find();
    
    return $lang;
}

/**
 * Get file/folder permissions.
 *
 * @param $path
 * @return string
 */
function getPerms($path)
{
    return substr(sprintf('%o', fileperms($path)), -4);
}

/**
 * Get all countries from PHP array (umpirsky)
 *
 * @return array|null
 */
function getCountriesFromArray()
{
    $countries = new App\Helpers\Localization\Helpers\Country();
    $countries = $countries->all();
    
    if (empty($countries)) return null;
    
    $arr = [];
    foreach ($countries as $code => $value) {
        if (!file_exists(storage_path('database/geonames/countries/' . strtolower($code) . '.sql'))) {
            continue;
        }
        $row = ['value' => $code, 'text' => $value];
        $arr[] = $row;
    }
    
    return $arr;
}

/**
 * Get all countries from DB (Geonames) & Translate them
 *
 * @return array
 */
function getCountries()
{
	$arr = [];
	
	// Get installed countries list
	$countries = \App\Helpers\Localization\Country::getCountries();
	
	// Translate the countries list
	$countries = \App\Helpers\Localization\Helpers\Country::transAll($countries);
	
	// The countries list must be a Laravel Collection object
	if (!$countries instanceof \Illuminate\Support\Collection) {
		$countries = collect($countries);
	}
	
	if ($countries->count() > 0) {
		foreach ($countries as $code => $country) {
			// The country entry must be a Laravel Collection object
			if (!$country instanceof \Illuminate\Support\Collection) {
				$country = collect($country);
			}
			
			// Get the country data
			$code = ($country->has('code')) ? $country->get('code') : $code;
			$name = ($country->has('name')) ? $country->get('name') : '';
			$arr[$code] = $name;
		}
	}
	
	return $arr;
}

/**
 * Pluralization
 *
 * @param $number
 * @return int
 */
function getPlural($number)
{
    if (config('lang.russian_pluralization')) {
        // Russian pluralization rules
        $typeOfPlural = (($number % 10 == 1) && ($number % 100 != 11))
            ? 0
            : ((($number % 10 >= 2)
                && ($number % 10 <= 4)
                && (($number % 100 < 10)
                    || ($number % 100 >= 20)))
                ? 1
                : 2
            );
    } else {
        // No rule for other languages
        $typeOfPlural = $number;
    }
    
    return $typeOfPlural;
}

/**
 * Get URL of Page by page's type
 *
 * @param $type
 * @param null $locale
 * @return mixed|string
 */
function getUrlPageByType($type, $locale = null)
{
    if (is_null($locale)) {
        $locale = config('app.locale');
    }
    
    $cacheExpiration = (int)config('settings.other.cache_expiration', 60);
    $cacheId = 'page.' . $locale . '.type.' . $type;
    $page = \Illuminate\Support\Facades\Cache::remember($cacheId, $cacheExpiration, function () use ($type, $locale) {
        $page = \App\Models\Page::type($type)->transIn($locale)->first();
        return $page;
    });
	
	$linkTarget = '';
	$linkRel = '';
    if (!empty($page)) {
		if ($page->target_blank == 1) {
			$linkTarget = ' target="_blank"';
		}
		if (!empty($page->external_link)) {
			$linkRel = ' rel="nofollow"';
			$url = $page->external_link;
		} else {
			$attr = ['slug' => $page->slug];
			$url = lurl(trans('routes.v-page', $attr), $attr);
		}
    } else {
        $url = '#';
    }
    
    // Get attributes
	$attributes = 'href="'. $url . '"' . $linkRel . $linkTarget;
    
    return $attributes;
}

/**
 * @param string $uploadType
 * @param bool $jsFormat
 * @return array|mixed|string
 */
function getUploadFileTypes($uploadType = 'file', $jsFormat = false)
{
    if ($uploadType == 'image') {
        $types = config('settings.upload.image_types', 'jpg,jpeg,gif,png');
    } else {
        $types = config('settings.upload.file_types', 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png');
    }
    
    $separators = ['|', '-', ';', '.', '/', '_', ' '];
    $types = str_replace($separators, ',', $types);
    
    if ($jsFormat) {
        $types = explode(',', $types);
        $types = array_filter($types, function ($value) {
            return $value !== '';
        });
        $types = json_encode($types);
    }
    
    return $types;
}

/**
 * @param string $uploadType
 * @return array|mixed|string
 */
function showValidFileTypes($uploadType = 'file')
{
    $formats = getUploadFileTypes($uploadType);
    $formats = str_replace(',', ', ', $formats);
    
    return $formats;
}

/**
 * Json To Array
 * NOTE: Used for MySQL Json and Laravel array (casts) columns
 *
 * @param $string
 * @return array|mixed
 */
function jsonToArray($string)
{
	if (is_array($string)) {
		return $string;
	}
	
	if (is_object($string)) {
		return \App\Helpers\Arr::fromObject($string);
	}
	
	if (isValidJson($string)) {
		$array = json_decode($string, true);
	} else {
		$array = [];
	}
	
	return $array;
}

/**
 * Check if json string is valid
 *
 * @param $string
 * @return bool
 */
function isValidJson($string)
{
    try {
        json_decode($string);
    } catch (\Exception $e) {
        return false;
    }
    
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Check if exec() function is available
 *
 * @return boolean
 */
function exec_enabled()
{
    try {
        // make a small test
        exec("ls");
        
        return function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    } catch (\Exception $ex) {
        return false;
    }
}

/**
 * Check if function is enabled
 *
 * @param $name
 * @return bool
 */
function func_enabled($name)
{
	try {
		$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
		
		return !in_array($name, $disabled);
	} catch (\Exception $ex) {
		return false;
	}
}

/**
 * Run artisan config cache
 *
 * @return mixed
 */
function artisanConfigCache()
{
	// Artisan config:cache generate the following two files
	// Since config:cache runs in the background
	// to determine if it is done, we just check if the files modified time have been changed
	$files = ['bootstrap/cache/config.php', 'bootstrap/cache/services.php'];
	
	// get the last modified time of the files
	$last = 0;
	foreach ($files as $file) {
		$path = base_path($file);
		if (file_exists($path)) {
			if (filemtime($path) > $last) {
				$last = filemtime($path);
			}
		}
	}
	
	// Prepare to run (5 seconds for $timeout)
	$timeout = 5;
	$start = time();
	
	// Actually call the Artisan command
	$exitCode = \Artisan::call('config:cache');
	
	// Check if Artisan call is done
	while (true) {
		// Just finish if timeout
		if (time() - $start >= $timeout) {
			echo "Timeout\n";
			break;
		}
		
		// If any file is still missing, keep waiting
		// If any file is not updated, keep waiting
		// @todo: services.php file keeps unchanged after artisan config:cache
		foreach($files as $file) {
			$path = base_path($file);
			if (!file_exists($path)) {
				sleep(1);
				continue;
			} else {
				if (filemtime($path) == $last) {
					sleep(1);
					continue;
				}
			}
		}
		
		// Just wait another extra 3 seconds before finishing
		sleep(3);
		break;
	}
	
	return $exitCode;
}

/**
 * Run artisan migrate
 *
 * @return mixed
 */
function artisanMigrate()
{
	$exitCode = \Artisan::call('migrate', ["--force"=> true]);
	
	return $exitCode;
}

/**
 * Check if the PHP Exif component is enabled
 *
 * @return bool
 */
function exifExtIsEnabled()
{
	try {
		if (extension_loaded('exif') && function_exists('exif_read_data')) {
			return true;
		}
		
		return false;
	} catch (\Exception $e) {
		return false;
	}
}

/**
 * @param $pathFromDb
 * @param string $type
 * @return mixed
 */
function resize($pathFromDb, $type = 'big')
{
    // Check default picture
    if (str_contains($pathFromDb, config('larapen.core.picture.default'))) {
        return \Storage::url($pathFromDb) . getPictureVersion();
    }
    
    // Get size dimensions
    $size = config('larapen.core.picture.resize.' . $type, '816x460');
    
    $filename = last(explode('/', $pathFromDb));
    $filepath = str_replace($filename, '', $pathFromDb);
    
    // Thumb file name
    $thumbFilename = 'thumb-' . $size . '-' . $filename;
    
    // Check if thumb image exists
    if (\Storage::exists($filepath . $thumbFilename)) {
        return \Storage::url($filepath . $thumbFilename) . getPictureVersion();
    } else {
        // Create thumb image if it not exists
        try {
            // Get file extention
            $extension = (is_png(\Storage::get($pathFromDb))) ? 'png' : 'jpg';
            
            // Sizes
            list($width, $height) = explode('x', $size);
            
            // Make the image
            if (in_array($type, ['logo'])) {
                // Resize logo pictures
                $image = \Image::make(\Storage::get($pathFromDb))->resize($width, $height, function ($constraint) {
                    $constraint->upsize();
                })->encode($extension, config('larapen.core.picture.quality', 100));
            } else if (in_array($type, ['big'])) {
                // Resize big pictures
                $image = \Image::make(\Storage::get($pathFromDb))->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($extension, config('larapen.core.picture.quality', 100));
            } else {
                // Fit small pictures
                $image = \Image::make(\Storage::get($pathFromDb))->fit($width, $height)->encode($extension, config('larapen.core.picture.quality', 100));
            }
        } catch (\Exception $e) {
            return \Storage::url($pathFromDb) . getPictureVersion();
        }
        
        // Store the image on disk.
        \Storage::put($filepath . '/' . $thumbFilename, $image->stream());
        
        // Get the image URL
        return \Storage::url($filepath . $thumbFilename) . getPictureVersion();
    }
}

/**
 * Get pictures version
 * @return string
 */
function getPictureVersion()
{
	$pictureVersion = '';
	if (config('larapen.core.picture.versioned') && !empty(config('larapen.core.picture.version'))) {
		$pictureVersion = '?v=' . config('larapen.core.picture.version');
	}
	
    return $pictureVersion;
}

/**
 * @return int|string
 */
function vTime()
{
	$timeStamp = '?v=' . time();
	if (\App::environment(['staging', 'production'])) {
		$timeStamp = '';
	}
	
	return $timeStamp;
}

/**
 * @param $pathFromDb
 * @return string
 */
function filePath($pathFromDb)
{
    $path = \Storage::getDriver()->getAdapter()->getPathPrefix();
    
    return $path . $pathFromDb;
}

/**
 * Get image extension from base64 string
 *
 * @param $bufferImg
 * @param bool $recursive
 * @return bool
 */
function is_png($bufferImg, $recursive = true)
{
    $f = finfo_open();
    $result = finfo_buffer($f, $bufferImg, FILEINFO_MIME_TYPE);
    
    if (!str_contains($result, 'image') && $recursive) {
        // Plain Text
        return str_contains($bufferImg, 'image/png');
    }
    
    return $result == 'image/png';
}

/**
 * Get the login field
 *
 * @param $value
 * @return string
 */
function getLoginField($value)
{
    $field = 'username';
    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $field = 'email';
    } else if (preg_match('/^((\+|00)\d{1,3})?[\s\d]+$/', $value)) {
        $field = 'phone';
    }
    
    return $field;
}

/**
 * Get Phone's National Format
 *
 * @param $phone
 * @param null $countryCode
 * @param int $format
 * @return \libphonenumber\PhoneNumberUtil|mixed|string
 */
function phoneFormat($phone, $countryCode = null, $format = \libphonenumber\PhoneNumberFormat::NATIONAL)
{
	// Country Exception
	if ($countryCode == 'UK') {
		$countryCode = 'GB';
	}
	
    // Set the phone format
    try {
        $phone = phone($phone, $countryCode, $format);
    } catch (\Exception $e) {
        // Keep the default value
    }
    
    // Keep only numeric characters
    $phone = preg_replace('/[^0-9\+]/', '', $phone);
    
    return $phone;
}

/**
 * Get Phone's International Format
 *
 * @param $phone
 * @param null $countryCode
 * @param int $format
 * @return \libphonenumber\PhoneNumberUtil|mixed|string
 */
function phoneFormatInt($phone, $countryCode = null, $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL)
{
    return phoneFormat($phone, $countryCode, $format);
}

/**
 * @param $phone
 * @param null $provider
 * @return mixed|string
 */
function setPhoneSign($phone, $provider = null)
{
	if ($provider == 'nexmo') {
		// Nexmo doesn't support the sign '+'
		if (starts_with($phone, '+')) {
			$phone = str_replace('+', '', $phone);
		}
	}
	
	if ($provider == 'twilio') {
		// Twilio requires the sign '+'
		if (!starts_with($phone, '+')) {
			$phone = '+' . $phone;
		}
	}
	
	return $phone;
}

/**
 * @param $countryCode
 * @return string
 */
function getPhoneIcon($countryCode)
{
    if (file_exists(public_path() . '/images/flags/16/' . strtolower($countryCode) . '.png')) {
        $phoneIcon = '<img src="' . url('images/flags/16/' . strtolower($countryCode) . '.png') . getPictureVersion() . '" style="padding: 2px;">';
    } else {
        $phoneIcon = '<i class="icon-phone-1"></i>';
    }
    
    return $phoneIcon;
}

/**
 * @param $field
 * @return bool
 */
function isEnabledField($field)
{
    // Front Register Form
    if ($field == 'phone') {
        return !config('larapen.core.disable.phone');
    } else if ($field == 'email') {
        return !config('larapen.core.disable.email') or
            (config('larapen.core.disable.email') and config('larapen.core.disable.phone'));
    } else if ($field == 'username') {
        return !config('larapen.core.disable.username');
    } else {
        return true;
    }
}

function getLoginLabel()
{
    if (isEnabledField('email') && isEnabledField('phone')) {
        $loginLabel = t('Email or Phone');
    } else {
        if (isEnabledField('phone')) {
            $loginLabel = t('Phone');
        } else {
            $loginLabel = t('Email address');
        }
    }
    
    return $loginLabel;
}

function getTokenLabel()
{
    if (isEnabledField('email') && isEnabledField('phone')) {
        $loginLabel = t('Code received by SMS or Email');
    } else {
        if (isEnabledField('phone')) {
            $loginLabel = t('Code received by SMS');
        } else {
            $loginLabel = t('Code received by Email');
        }
    }
    
    return $loginLabel;
}

function getTokenMessage()
{
    if (isEnabledField('email') && isEnabledField('phone')) {
        $loginLabel = t('Enter the code you received by SMS or Email in the field below');
    } else {
        if (isEnabledField('phone')) {
            $loginLabel = t('Enter the code you received by SMS in the field below');
        } else {
            $loginLabel = t('Enter the code you received by Email in the field below');
        }
    }
    
    return $loginLabel;
}

/**
 * Get meta tag from settings
 *
 * @param $tag
 * @param $page
 * @return null|string
 */
function getMetaTag($tag, $page)
{
	$out = null;
	
	// Check if the Domain Mapping plugin is available
	if (config('plugins.domainmapping.installed')) {
		$out = \App\Plugins\domainmapping\Domainmapping::getMetaTag($tag, $page);
		if (!empty($out)) {
			return $out;
		}
	}
	
    // Get the current Language
    $languageCode = config('lang.abbr');
    // Seems like You do not have MaxMind paid account for the to Location service.
 	$ipAddr = $_SERVER['HTTP_X_REAL_IP'];
	// $geoip = new GeoIP();
	// $geoip->setIp($ipAddr);
	// $CurrentCity = $geoip->getCity();

 	// This approach is done by using free ip to city service. 
    $ipstack_response = @file_get_contents("https://freegeoip.app/json/$ipAddr");
    if(isset($_GET['dev'])){
    	print_r($_SERVER);
    	print_r($ipstack_response);
    	die();
    }
    $resp = json_decode($ipstack_response , true);
    //print_r($resp); dd();

    $CurrentCity = (isset($resp['city'])) ? $resp['city'] : '';

    if(!isset($resp['city'])){
    	$ipstack_response = @file_get_contents("https://freegeoip.io/json/$ipAddr");
    	$resp = json_decode($ipstack_response , true);
    	$CurrentCity = (isset($resp['city'])) ? $resp['city'] : '';
    }
    
	$Country = config('country.name');
	if(strlen($Country) > 15){
		$Country = config('country.code');

		$Country = ($Country === "AE") ? "UAE" : $Country;
		$Country = ($Country === "US") ? "USA" : $Country;

	}
	$Country = isset($resp['country_name'])?$resp['country_name']:'UAE';
    // Get the Page's MetaTag
	$metaTag = null;
    try {
        $cacheExpiration = (int)config('settings.other.cache_expiration', 60);
        $cacheId = 'metaTag.' . $languageCode . '.' . $page;
        $metaTag = \Cache::remember($cacheId, $cacheExpiration, function () use ($languageCode, $page) {
			$metaTag = \App\Models\MetaTag::transIn($languageCode)->where('page', $page)->first();
			
            return $metaTag;
        });
    } catch (\Exception $e) {}
    
    if (!empty($metaTag)) {
        if (isset($metaTag->$tag) && !empty($metaTag->$tag)) {
			$out = $metaTag->$tag;

			$out = str_replace(['{app_name}', '{country}' , '{city}'], [config('app.name'), $Country , $CurrentCity], $out);
	//print_r($out); dd();
            
            return $out;
        }
    }
	
	if (config('app.name') || config('settings.app.slogan')) {
		if (in_array($tag, ['title', 'description'])) {
			if (config('settings.app.slogan')) {
				$out = config('app.name') . ' - ' . config('settings.app.slogan');
			} else {
				$out = config('app.name') . ' - ' . config('country.name');
			}
		}
	}
    
    return $out;
}


/**
 * Redirect (Prevent Browser cache)
 *
 * @param $url
 * @param int $code (301 => Moved Permanently | 302 => Moved Temporarily)
 */
function headerLocation($url, $code = 301)
{
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: " . $url, true, $code);
    exit();
}

/**
 * Split a name into the first name and last name
 *
 * @param $input
 * @return array
 */
function splitName($input)
{
    $output = ['firstName' => '', 'lastName' => ''];
    $space = mb_strpos($input, ' ');
    if ($space !== false) {
        $output['firstName'] = mb_substr($input, 0, $space);
        $output['lastName'] = mb_substr($input, $space, strlen($input));
    } else {
        $output['lastName'] = $input;
    }
    
    return $output;
}

/**
 * Zero leading for numeric values
 *
 * @param $number
 * @param int $padLength
 * @return string
 */
function zeroLead($number, $padLength = 2)
{
    if (is_numeric($number)) {
        $number = str_pad($number, $padLength, '0', STR_PAD_LEFT);
    }
    
    return $number;
}

/**
 * @param $number
 * @param null $countryCode
 * @return int|string
 */
function lengthPrecision($number, $countryCode = null)
{
    if (empty($countryCode)) {
        $countryCode = config('country.code');
    }
    
    // Get mile use countries
    $mileUseCountries = (array)config('larapen.core.mileUseCountries');
    
    if (is_numeric($number)) {
        // Anglo-Saxon units of length
        if (in_array($countryCode, $mileUseCountries)) {
            // Convert Km to Miles
            $number = $number * 0.621;
        }
    }
    
    return $number;
}

/**
 * @param null $countryCode
 * @return string
 */
function unitOfLength($countryCode = null)
{
    if (empty($countryCode)) {
        $countryCode = config('country.code');
    }
    
    // Get mile use countries
    $mileUseCountries = (array)config('larapen.core.mileUseCountries');
    
    $unit = t('km');
    if (in_array($countryCode, $mileUseCountries)) {
        $unit = t('mi');
    }
    
    return $unit;
}

/**
 * Get the script possible URL base
 *
 * @return mixed
 */
function getRawBaseUrl()
{
	// Get Server Variables
	$httpHost = (trim(request()->server('HTTP_HOST')) != '') ? request()->server('HTTP_HOST') : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
	$requestUri = (trim(request()->server('REQUEST_URI')) != '') ? request()->server('REQUEST_URI') : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
	
	// Get the Current URL
	$currentUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://') . $httpHost . strtok($requestUri,'?');
	$currentUrl = head(explode('/' . admin_uri(), $currentUrl));
	
	// Get the Base URL
	$baseUrl = head(explode('/install', $currentUrl));
	$baseUrl = rtrim($baseUrl, '/');
	
	return $baseUrl;
}

/**
 * Get the current version value
 *
 * @return null|string
 */
function getCurrentVersion()
{
	// Get the Current Version
	$currentVersion = null;
	if (\Jackiedo\DotenvEditor\Facades\DotenvEditor::keyExists('APP_VERSION')) {
		try {
			$currentVersion = \Jackiedo\DotenvEditor\Facades\DotenvEditor::getValue('APP_VERSION');
		} catch (\Exception $e) {}
	}
	
	// Forget the subversion number
	if (!empty($currentVersion)) {
		$tmp = explode('.', $currentVersion);
		if (count($tmp) > 1) {
			if (count($tmp) >= 3) {
				$tmp = array_only($tmp, [0, 1]);
			}
			$currentVersion = implode('.', $tmp);
		}
	}
	
	return $currentVersion;
}

/**
 * Extract only digit characters and Convert the result in integer.
 *
 * @param $value
 * @return mixed
 */
function strToInt($value)
{
	$value = preg_replace('/[^0-9]/', '', $value);
	$value = (int)$value;
	
	return $value;
}

/**
 * Change whitespace (\n and \r) to simple space in string
 * PHP_EOL catches newlines that \n, \r\n, \r miss.
 *
 * @param $string
 * @return mixed
 */
function changeWhiteSpace($string)
{
	$string = str_replace(PHP_EOL, ' ', $string);
	
	return $string;
}

/**
 * PHP round() function that always return a float value in any language
 *
 * @param $val
 * @param int $precision
 * @param int $mode
 * @return string
 */
function round_val($val, $precision = 0, $mode = PHP_ROUND_HALF_UP)
{
	return number_format((float) round($val, $precision, $mode), $precision, '.', '');
}

/**
 * Print JavaScript code in HTML
 *
 * @param $code
 * @return mixed
 */
function printJs($code)
{
	// Get the External JS, and make for them a pattern
	$exRegex = '/<script([a-z0-9\-_ ]+)src=([^>]+)>(.*?)<\/script>/ius';
	$replace = '<#EXTERNALJS#$1src=$2>$3</#EXTERNALJS#>';
	$code = preg_replace($exRegex, $replace, $code);
	
	// Get the Inline JS, and make for them a pattern
	$inRegex = '/<script([^>]*)>(.*?)<\/script>/ius';
	$replace = '<#INLINEJS#$1>$2</#INLINEJS#>';
	while (preg_match($inRegex, $code)) {
		$code = preg_replace($inRegex, $replace, $code);
	}
	
	// Replace the patterns
	$code = str_replace(['#EXTERNALJS#', '#INLINEJS#'], 'script', $code);
	
	// The code doesn't contain a <script> tag
	if (!preg_match($inRegex, $code)) {
		$code = '<script type="text/javascript">' . "\n" . $code . "\n". '</script>';
	}
	
	return $code;
}

/**
 * Print CSS code in HTML
 *
 * @param $code
 * @return mixed
 */
function printCss($code)
{
	$code = preg_replace('/<[^>]+>/i', '', $code);
	$code = '<style type="text/css">' . "\n" . $code . "\n". '</style>';
	
	return $code;
}

/**
 * Get Front Skin
 *
 * @param null $skin
 * @return \Illuminate\Config\Repository|mixed|null|string
 */
function getFrontSkin($skin = null)
{
	if (!empty($skin)) {
		if (!file_exists(public_path() . '/assets/css/skins/' . $skin . '.css')) {
			$skin = 'skin-default';
		}
	} else {
		$skin = config('settings.style.app_skin', 'skin-default');
	}
	
	return $skin;
}

/**
 * Count the total number of line of a given file without loading the entire file.
 * This is effective for large file
 *
 * @param string file path
 * @return int line count
 */
function lineCount($path)
{
	$file = new \SplFileObject($path, 'r');
	$file->seek(PHP_INT_MAX);
	
	return $file->key() + 1;
}

/**
 * Escape characters with slashes like in C & Remove the double white spaces
 *
 * @param $string
 * @param string $quote
 * @return null|string|string[]
 */
function cleanAddSlashes($string, $quote = '"')
{
	$string = preg_replace("/\s+/ui", " ", addcslashes($string, $quote));
	
	return $string;
}

/**
 * Get the current request path by pattern
 *
 * @param null $pattern
 * @return string
 */
function getRequestPath($pattern = null)
{
	if (empty($pattern)) {
		return request()->path();
	}
	
	$pattern = '#(' . $pattern . ')#ui';
	
	$tmp = '';
	preg_match($pattern, request()->path(), $tmp);
	$path = (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] : request()->path();
	
	return $path;
}
function getCurrentCity(){
	$ipAddr = getenv('REMOTE_ADDR');
	// $geoip = new GeoIP();
	// $geoip->setIp($ipAddr);
	// $CurrentCity = $geoip->getCity();

 	// This approach is done by using free ip to city service. 
    $ipstack_response = @file_get_contents("https://freegeoip.app/json/$ipAddr");
    if(isset($_GET['dev'])){
    	print_r($_SERVER);
    	print_r($ipstack_response);
    	die();
    }
    $resp = json_decode($ipstack_response , true);
    //print_r($resp); dd();

    $CurrentCity = (isset($resp['city'])) ? $resp['city'] : '';

   return ($CurrentCity);
    
}
function getCurrentCountry(){
	$ipAddr = getenv('REMOTE_ADDR');
	// $geoip = new GeoIP();
	// $geoip->setIp($ipAddr);
	// $CurrentCity = $geoip->getCity();

 	// This approach is done by using free ip to city service. 
    $ipstack_response = @file_get_contents("https://freegeoip.app/json/$ipAddr");
    if(isset($_GET['dev'])){
    	print_r($_SERVER);
    	print_r($ipstack_response);
    	die();
    }
    $resp = json_decode($ipstack_response , true);
    //print_r($resp); dd();

    $CurrentCity = (isset($resp['country_name'])) ? $resp['country_name'] : '';

   return ($CurrentCity);
    
}