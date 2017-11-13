<?php
namespace Kinsta;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class CDNRewriter {
    var $https = false;
	var $site_url = null;
	var $cdn_url = null; 

	var $dirs = null;
	var $excludes = array(); 
	var $relative = false; 


	function __construct($site_url, $cdn_url, $dirs, array $excludes, $relative, $https) {
		$this->site_url = $site_url;
		$this->cdn_url = $cdn_url;
		$this->dirs	= $dirs;
		$this->excludes = $excludes;
		$this->relative	= $relative;
		$this->https = $https;
	}

	protected function exclude_asset(&$asset) {
		foreach ($this->excludes as $exclude) {
			if (!!$exclude && stristr($asset, $exclude) != false) {
				return true;
			}
		}
		return false;
	}


    protected function rewrite_url($asset) {
        if ($this->exclude_asset($asset[0])) {
            return $asset[0];
        }

        if ( is_admin_bar_showing()
                and array_key_exists('preview', $_GET)
                and $_GET['preview'] == 'true' )
        {
            return $asset[0];
        }

        $site_url = $this->relative_url($this->site_url);
        $subst_urls = [ 'http:'.$site_url ];

        if ($this->https) {
            $subst_urls = [
                'http:'.$site_url,
                'https:'.$site_url,
            ];
        }

        if (strpos($asset[0], '//') === 0) {
            return str_replace($site_url, $this->cdn_url, $asset[0]);
        }

        if (!$this->relative || strstr($asset[0], $site_url)) {
            return str_replace($subst_urls, $this->cdn_url, $asset[0]);
        }

        return $this->cdn_url . $asset[0];
    }


    protected function relative_url($url) {
        return substr($url, strpos($url, '//'));
    }

	protected function get_dir_scope() {
		$input = explode(',', $this->dirs);
		if ($this->dirs == '' || count($input) < 1) { return 'wp\-content|wp\-includes'; }
		return implode('|', array_map('quotemeta', array_map('trim', $input)));
	}


	public function rewrite($source_html) {
		if (!$this->https && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') { return $source_html; }

        $dirs = $this->get_dir_scope();
        $site_url = $this->https
            ? '(https?:|)'.$this->relative_url(quotemeta($this->site_url))
            : '(http:|)'.$this->relative_url(quotemeta($this->site_url));

        $regex_rule = '#(?<=[(\"\'])';

        if ($this->relative) {
            $regex_rule .= '(?:'.$site_url.')?';
        } else {
            $regex_rule .= $site_url;
        }

        $regex_rule .= '/(?:((?:'.$dirs.')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))(?=[\"\')])#';
		$return_html = preg_replace_callback($regex_rule, array(&$this, 'rewrite_url'), $source_html);
		return $return_html;
	}
}
