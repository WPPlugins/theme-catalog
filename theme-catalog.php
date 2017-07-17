<?php
/*
Plugin Name: Theme Catalog
Plugin URI: http://tapy.com
Description: Attractive front-end theme switcher/preview allowing demo of all themes, with menu and widget handling.
Version: 0.02
Author: tapy.com
Author URI: http://tapy.com
License: GPLv2 or later
*/
class ThemeCatalog {
	protected $template;
	protected $stylesheet;
	protected $old_template;
	protected $old_stylesheet;
	protected $variable_prefix;
	protected $static_prefix = 'theme_catalog';
	protected $encountered_menus = array();
	protected $options;
	public function __construct(){
		$this->options = get_option($this->static_prefix . "_options");
		if(isset($this->options['variable_prefix'])){
			$this->variable_prefix = $this->options['variable_prefix'];
		} else {
			$this->variable_prefix = $this->static_prefix;
		}
		if(!is_admin() || 
			(
				isset($this->options['apply_in_admin']) &&
				self::destring($this->options['apply_in_admin'])
			)
		){
			if( !session_id() )session_start();
			$this->template = $this->variable('template');
			$this->stylesheet = $this->variable('stylesheet');
			$this->old_template = explode("/",get_template_directory());
			$this->old_template = $this->old_template[count($this->old_template)-1];
			$this->old_stylesheet = explode("/",get_stylesheet_directory());
			$this->old_stylesheet = $this->old_stylesheet[count($this->old_stylesheet)-1];
			if($this->stylesheet && $this->stylesheet !== $this->old_stylesheet){
				if($this->template && !$this->stylesheet)$this->stylesheet = $this->template;
				if($this->template && file_exists(get_theme_root() . '/' . $this->template)){
					add_filter('option_template',array($this,'use_template'));
					if(file_exists(get_theme_root() . '/' . $this->stylesheet)){
						add_filter('option_stylesheet',array($this,'use_stylesheet'));
					} else {
						add_filter('option_stylesheet',array($this,'use_template'));
					}
				}
				add_filter('wp_nav_menu_args',array($this,'menu_args'));
			}
			add_shortcode($this->variable_prefix . "_selector",array($this,'selector'));
			add_action( 'wp_enqueue_scripts',array($this,'register_scripts'));
		}
		add_action('admin_menu',array($this,'admin_setup'));
		add_action('admin_init',array($this,'admin_page_sections'));
	}
	public function admin_setup(){
		add_options_page('Theme Catalog','Theme Catalog','administrator',$this->static_prefix . "_options",array($this,'admin_page'));
	}
	public function admin_page(){
		echo '<div class="wrap">
			<h2>Theme Catalog Settings</h2>
			<form method="post" action="options.php">';
				// This prints out all hidden setting fields
				settings_fields($this->static_prefix . "_options");
				do_settings_sections($this->static_prefix . "_options");
				submit_button();
			echo '</form>
		</div>';
	}
	public function admin_page_sections(){
		register_setting(
			$this->static_prefix . "_options",
			$this->static_prefix . "_options",
			array($this,'sanitize')
		);
		add_settings_section(
			"header1",
			"",
			function(){},
			$this->static_prefix . "_options"
		);
		add_settings_field(
			'apply_in_admin',
			'Apply in admin',
			array($this,'apply_in_admin_select'),
			$this->static_prefix . "_options",
			"header1"          
		);
		add_settings_field(
			'variable_prefix',
			'Plugin prefix <span style="font-weight:normal">(will be used for shortcode, GET/Session Variables, etc)</span>',
			array($this,'variable_prefix_input'),
			$this->static_prefix . "_options",
			"header1"          
		);
		add_settings_field(
			'lazyload_in_header',
			'Lazyload in header <span style="font-weight:normal">(fixes problem with themes that don\'t properly call wp_footer())</span>',
			array($this,'lazyload_in_header_select'),
			$this->static_prefix . "_options",
			"header1"          
		);
	}
	public function sanitize( $input )
    {
        $new_input = array();
        if(isset($input['apply_in_admin']) && ($input['apply_in_admin'] === 'true' || $input['apply_in_admin'] === 'false'))
            $new_input['apply_in_admin'] = $input['apply_in_admin'];

        if(isset($input['lazyload_in_header']) && ($input['lazyload_in_header'] === 'true' || $input['lazyload_in_header'] === 'false'))
            $new_input['lazyload_in_header'] = $input['lazyload_in_header'];

		if( isset( $input['variable_prefix'] ) && strlen($input['variable_prefix']) > 1)
            $new_input['variable_prefix'] = sanitize_text_field( $input['variable_prefix'] );
        return $new_input;
    }
	public function lazyload_in_header_select(){
		if(isset($this->options['lazyload_in_header'])){
			$header = $this->options['lazyload_in_header'];
		} else {
			$header = 'false';
		}
		echo $this->select_box(
			'lazyload_in_header',
			$this->static_prefix . "_options[lazyload_in_header]",
			array('true'=>'yes','false'=>'no'),
			$header
		);
	}
	public function apply_in_admin_select(){
		if(isset($this->options['apply_in_admin'])){
			$apply = $this->options['apply_in_admin'];
		} else {
			$apply = 'false';
		}
		echo $this->select_box(
			'apply_in_admin',
			$this->static_prefix . "_options[apply_in_admin]",
			array('true'=>'yes','false'=>'no'),
			$apply
		);
	}
	public function variable_prefix_input(){
		if(isset($this->options['variable_prefix'])){
			$prefix = $this->options['variable_prefix'];
		} else {
			$prefix = $this->static_prefix;
		}
		echo '<input type="text" id="variable_prefix" name="' . $this->static_prefix . '_options[variable_prefix]" value="' . $prefix . '" />';
	}
	public function select_box($id,$name,$keyed_array,$selected_key){
		$output = "<select id='$id' name='$name'>";
		foreach($keyed_array as $key=>$value){
			$output .= "<option value='$key'";
			if($key == $selected_key){
				$output .= " selected";
			}
			$output .= ">$value</option>";
		}
		$output .= "</select>";
		return $output;
	}
	public function use_template($old_template){
		return $this->template;
	}
	public function use_stylesheet($old_stylesheet){
		return $this->stylesheet;
	}
	public function menu_args($args){
		$locations = get_nav_menu_locations();
		if(isset($locations[$args['theme_location']])){
			$args['menu'] = $locations[$args['theme_location']];
			return $args;
		}
		$position = array_search($args['theme_location'],$this->encountered_menus);
		if(!$position){
			$position = array_push($this->encountered_menus,$args['theme_location']) - 1;
		}
		$menu_name = $this->variable_prefix . '_menu_' . ($position+1);
		if($menu = wp_get_nav_menu_object($menu_name)){
			$args['menu'] = $menu_name;
		}
		return $args;
	}
	public static function destring($string){
		$test = strtolower($string);
		if($test === 'true' || $test === 'false'){
			return $test === 'true';
		} elseif($test === 'null'){
			return null;
		}
		return $string;
	}
	public static function destring_array(&$array){
		foreach($array as $key=>$value){
			$array[$key] = self::destring($value);
		}
		return $array;
	}
	public function register_scripts(){
		if(isset($this->options['lazyload_in_header']) && self::destring($this->options['lazyload_in_header'])){
			wp_enqueue_script('jquery-lazyload',plugins_url('/lib/jquery.lazyload.min.js',__FILE__),array('jquery'),'1.9.5', false);
		} else {
			wp_register_script('jquery-lazyload',plugins_url('/lib/jquery.lazyload.min.js',__FILE__),array('jquery'),'1.9.5', true);
		}
	}
	public function selector($atts){
		$atts = shortcode_atts(
			array(
				'errors'=>false, //Show themes that have errors
								//(true,false,null)
								//https://codex.wordpress.org/Function_Reference/wp_get_themes
				'allowed'=>true, //Show themes allowed for this site
								//(true,false,'site','network',null)
								//https://codex.wordpress.org/Function_Reference/wp_get_themes
				'screenshots'=>true, //Show themes that have screenshots
									//(true, false, null)
									//true = only show themes with screenshots
									//false = only show themes without screenshots
									//null = show all themes
				'lazyload'=>20, //whether to use lazyload or not
								//(true,false,INT)
								//true = always use lazyload
								//false = never use lazyload
								//INT = specifies how many results to require before using lazyload
				'display_screenshot'=>true, //whether to display screenshot
				'display_name'=>true, //whether to display name
				'display_author'=>true, //whether to display author in results
				'display_version'=>true, //whether to display version,
				'display_author'=>true, //whether to display author
				'display_description'=>true, //whether to display description
			),$atts,$this->variable_prefix
		);
		self::destring_array($atts);

		$themes = wp_get_themes(array('errors'=>$atts['errors'],'allowed'=>$atts['allowed']));
		$get_clone = $_GET;
		unset($get_clone[$this->variable_prefix . '_template']);
		unset($get_clone[$this->variable_prefix . '_stylesheet']);
		$base_url = explode("?",$_SERVER['REQUEST_URI']);
		$new_url = $base_url[0];
		if(count($get_clone) > 0){
			$new_url .= '?' . http_build_query($get_clone) . '&';
		} else {
			$new_url .= '?';
		}
		$output = "<script>
		function {$this->variable_prefix}_go(template,stylesheet){
			location.href = '$new_url' +
			'{$this->variable_prefix}_template=' + encodeURIComponent(template) + '&' +
			'{$this->variable_prefix}_stylesheet=' + encodeURIComponent(stylesheet) + '#' +
			encodeURIComponent(template) + '_' + encodeURIComponent(stylesheet);
		}";
		if($atts['lazyload']===true || ($atts['lazyload'] !== false && $atts['lazyload'] <= count($themes))) {
			wp_enqueue_script('jquery-lazyload');
			$atts['lazyload'] = true;
			$output .= " jQuery(function(){jQuery('.{$this->variable_prefix}_selector img').lazyload({threshold : 1000});}); ";
		}
		$output .= "</script>
		<ul class='{$this->variable_prefix}_selector'>
		<style type='text/css' scoped>
		.{$this->variable_prefix}_selector{
		}
		.{$this->variable_prefix}_selector img{
			margin:0 10px 10px 0;
			float:left;
			width:300px;
		}
		.{$this->variable_prefix}_selector name{
			color:#222;
			float:left;
			font-weight:bold;
			display:block;
		}
		.{$this->variable_prefix}_selector author{
			clear:both;
			color:#666;
			font-size:.75rem;
			display:block;
		}
		.{$this->variable_prefix}_selector version{
			float:right;
			color:#666;
			font-size:.75rem;
			display:block;
			clear:right;
		}
		.{$this->variable_prefix}_selector description{
			color:#222;
			display:block;
		}
		.{$this->variable_prefix}_selector .page {
			overflow: auto;
			list-style-type: none;
			cursor:pointer;
			border:1px solid #CCC;
			border-radius:20px;
			margin:5px;
			padding:10px;
			background-color:#F3F3F3;
			background: linear-gradient(to bottom, #ffffff 0%,#f1f1f1 47%,#e1e1e1 53%,#f6f6f6 100%);
		}
		.{$this->variable_prefix}_selector .inner_wrapper {
			overflow: auto;
		}
		.{$this->variable_prefix}_selector .page:BEFORE {
			content:initial;
		}
		.{$this->variable_prefix}_selector .page:HOVER {
			background-color:#E6E6E6;
			background: linear-gradient(to bottom, #ffffff 0%,#eaeef8 47%,#eaeef8 47%,#d1dbf1 53%,#f1f4fb 100%);
		}
		</style>";
		foreach($themes as $key=>$theme){
			$template = $theme->get_template();
			$stylesheet = $theme->get_stylesheet();
			$screenshot = $theme->get_screenshot();
			if(!$screenshot && $atts['screenshots'] === true)continue;
			if($screenshot && $atts['screenshots'] === false)continue;
			$output .= "<li class='page' onClick='{$this->variable_prefix}_go(\"$template\",\"$stylesheet\")'>
			<a name='{$template}_{$stylesheet}'></a>";
			if($atts['display_screenshot'] && $screenshot){
				if($screenshot){
					if($atts['lazyload']){
						$output .= "<img data-original='$screenshot' />";
					} else {
						$output .= "<img src='$screenshot' />";
					}
				}
			}
			$output .= "<div class='inner_wrapper'>";
			if($atts['display_name'] && $theme->Name)$output .= "<name>{$theme->Name}</name>";
			if($atts['display_version'] && $theme->Version)$output .= "<version>{$theme->Version}</version>";
			if($atts['display_author'] && $theme->Author)$output .= "<author>{$theme->Author}</author>";
			$output .= "</div>";
			if($atts['display_description'] && $theme->Description)$output .= "<description>{$theme->Description}</description>";
			$output .= "</li>";
		}
		$output .= "</ul>";
		return $output;
	}
	private function variable($name){
		$internal_name = $this->variable_prefix . '_' . $name;
		if(isset($_GET[$internal_name])){
			$_SESSION[$internal_name] = $_GET[$internal_name];
			return $_GET[$internal_name];
		} elseif(isset($_POST[$internal_name])){
			$_SESSION[$internal_name] = $_POST[$internal_name];
			return $_POST[$internal_name];
		} elseif(isset($_SESSION[$internal_name])){
			return $_SESSION[$internal_name];
		} else {
			return false;
		}
	}
}
$themeSet = new ThemeCatalog();