<?php
class Snippet extends Plugin {
  
  function langSelect() {
   
   $output = array(
    'actionscript'=>'Actionscript',
    'bash'=>'Bash',
    'c'=>'C',
    'csharp'=>'C#',
    'cpp'=>'C++',
    'cobol'=>'COBOL',
    'coldfusion'=>'ColdFusion',
    'css'=>'CSS',
    'd'=>'D',
    'html'=>'HTML',
    'java'=>'Java',
    'javascript'=>'Javascript',
    'php'=>'PHP',
    'python'=>'Python'
   );
   
   return $output; 
    
  }
  
  
  function info() {
		return array(
			'name' => 'Snippet',
			'version' => '1.0',
			'url' => 'http://digitalspaghetti.me.uk/',
			'author' => 'Tane Piper',
			'authorurl' => 'http://digitalspaghetti.me.uk',
			'license' => 'Apache Licence 2.0',
			'description' => 'A plugin to share code snippets.'
		);
	}
	
	function action_plugin_activation( $plugin_file )
	{
		if( Plugins::id_from_file(__FILE__) == Plugins::id_from_file($plugin_file) ) {
			Post::add_new_type('snippet');
		}
	}
	
	public function action_plugin_deactivation( $file )
  {
    if ( realpath( $file ) == __FILE__ ) {
      Post::deactivate_post_type('snippet');
    }
  }
	
	public function action_init() {		
		$this->add_template('snippet.single', dirname(__FILE__) . '/snippet.single.php');
	}
  
  public function action_add_template_vars($theme, $vars) {
		if(isset($vars['slug'])) {
			$theme->snippet = Snippet::get($vars['slug']);
			$theme->snippet_out = Snippet::out($vars['slug']);
		}
	}
	
	public function filter_rewrite_rules( $rules ) {
		$rules[] = new RewriteRule(array(
			'name' => 'show_snippet',
			'parse_regex' => '%snippet/(?P<slug>.*)[\/]?$%i',
			'build_str' =>  'snippet/{$slug}',
			'handler' => 'UserThemeHandler',
			'action' => 'show_snippet',
			'priority' => 6,
			'is_active' => 1,
		));
		
		return $rules;
	}
	
	public function action_handler_display_event($vars) {		
		$post = Post::get(array('slug' => $vars['slug']));
		
		$this->theme->assign( 'post', $post);
		$this->theme->display( 'snippet.single' );
		
		exit;
	}
	
		/**
	* Add fields to the publish page for podcasts
	*
	* @param FormUI $form The publish form
	* @param Post $post 
	* @return array 
	*/
	public function action_form_publish( $form, $post )
	{
		if( $form->content_type->value == Post::type( 'snippet' ) ) {
			$postfields = $form->publish_controls->append( 'fieldset', 'language', _t( 'Language') );
			
			$snippet_language = $postfields->append( 'select', 'snippet_language', 'null:null', _t('Language') );
			$snippet_language->options = $this->langSelect();
			$snippet_language->value = strlen( $post->info->snippet_language ) ? $post->info->snippet_language : '' ;
			$snippet_language->template = 'tabcontrol_select';
		}
	}
	
	public function get($slug) {
		$post = Post::get(array('slug' => $slug));
		
		
		if($post->content_type == Post::type('snippet')) {
			$return = $post;
			$return->snippet_language = $post->snippet_language;
      
			return $return;
		} else {
			return FALSE;
		}
		
	}
	
	public function out($slug) {
		if($event = $this->get($slug)) {
			return $this->html($event);
		} else {
			return FALSE;
		}
	}
	
	public function html($snippet) { ?>
	  <div class="snippet">
	    <h2><?php echo $snippet->title?></h2>
	    <div class="output">
	      
	    </div>
	    <p>
	      Language: <?php echo $snippet->snippet_language; ?>
	    </p>
	  </div>
	<?php }
	
	public function filter_publish_controls ($controls, $post) {
		$vars = Controller::get_handler_vars();
				
		if($vars['content_type'] == Post::type('snippet')) {
			$output = '';
			
			$output .= '<div class="text container"><p class="column span-5"><label for="snippet_language">Language:</label></p><p class="column span-14 last"><input type="text" id="snippet_language" name="snippet_language" value="';
			if(strlen($post->info->snippet_language) > 0) {
				$output .= $post->info->snippet_language;
			}
			$output .= '" /></p></div>';		
			$controls['Details'] = $output;
		}
		
		return $controls;
	}
	
	public function action_post_update_status( $post, $new_status ) {
		$vars = Controller::get_handler_vars();
		
		if($post->content_type == Post::type('snippet')) {
			Snippet::set($post);
		}
	}
	
	function set($post) {
		$vars = Controller::get_handler_vars();
		
		if(strlen($vars['snippet_language']) > 0) {
			$post->info->event_location = $vars['snippet_language'];
		}
		
	}
}
?>
