<?php
class Snippet extends Plugin {
  
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
	
	public function action_init() {		
		$this->add_template('snippet.single', dirname(__FILE__) . '/snippet.single.php');
		
		Post::add_new_type('snippet');
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
			'build_str' =>  'snipet/{$slug}',
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
	
	public function get($slug) {
		$post = Post::get(array('slug' => $slug));
		
		
		if($post->content_type == Post::type('event')) {
			$return = $post;
			$return->language = $post->language;
      
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
	  <div id="snippet" class="snippet"
		<div id="hcalendar-<?php echo $event->slug; ?>" class="vevent">
			<a href="<?php echo $event->permalink; ?>" class="url">
				<?php if(strlen($event->info->start) > 0) { ?><abbr title="<?php echo date("Ymd\THiO", $event->info->start); ?>" class="dtstart"><?php echo date("F jS, Y g:ia", $post->info->start); ?></abbr>, <?php } ?>
				<?php if(strlen($event->info->end) > 0) { ?><abbr title="<?php echo date("Ymd\THiO", $event->info->end); ?>" class="dtend"><?php echo date("F jS, Y g:ia", $post->info->end); ?></abbr><?php } ?>
				<span class="summary"><?php echo $event->title; ?></span>
				<?php if(strlen($event->info->location) > 0) { ?>– at <span class="location"><?php echo $event->info->location; ?></span><?php } ?>
			</a>
			<div class="description"><?php echo $event->content_out; ?></div>
			<div class="tags">Tags: <?php echo $event->tags_out; ?></div>
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
