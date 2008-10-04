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
    'python'=>'Python',
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
		
	}
	
  /**
	* Add fields to the publish page for snippets
	*
	* @param FormUI $form The publish form
	* @param Post $post 
	* @return array 
	*/
	public function action_form_publish( $form, $post )
	{
		if( $form->content_type->value == Post::type( 'snippet' ) ) {
			$postfields = $form->publish_controls->append( 'fieldset', 'snippet', _t( 'Snippet Options') );
			
			$snippet_language = $postfields->append( 'select', 'snippet_language', 'null:null', _t('Language') );
			$snippet_language->options = $this->langSelect();
			$snippet_language->value = strlen( $post->info->snippet_language ) ? $post->info->snippet_language : '' ;
			$snippet_language->template = 'tabcontrol_select';
		}
	}
	
	public function action_publish_post($post, $form)
	{
	  if( $post->content_type == Post::type( 'snippet' ) ) {
	    if( strlen( $form->snippet->snippet_language->value ) ) {
				$post->info->snippet_language = $form->snippet->snippet_language->value;
			}
			else {
				$post->info->__unset( 'snippet_language' );
			}
    }
  }
  
  public function action_handler_display_snippets( $handler_vars ) {
    Utils::debug($handler_vars);
    exit();
  }
  
  public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule( array(
			'name' => 'display_snippet',
			'parse_regex' => '%^snippet/(?P<snippet_language>)/(?P<slug>)/?$%i',
			'build_str' => 'snippet/{$slug}',
			'handler' => 'UserThemeHandler',
			'action' => 'display_snippets',
			'priority' => 7,
			'is_active' => 1,
			'description' => 'Displays snippets of language type',
		));

		return $rules;
	}
  	
}
?>
