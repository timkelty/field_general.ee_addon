<?php

/**
*
* @package Field General
* @version 1.0
* @author Tim Kelty <http://fusionary.com>
* @copyright Copyright (c) 2010 Tim Kelty
* @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
* 
* Many thanks to Stephen Lewis (Experience Internet) for settings form reference from many of his free addons
*/

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

if ( ! defined('F_GEN_version'))
{
	define('F_GEN_version', '1.0.0');
	define('F_GEN_docs_url', '');
	define("F_GEN_name", "Field General");
}

class Field_general_ext
{
  /**
	* Extension settings
	* @var array
	*/
  var $settings;

  /**
	* Debug
	* @var string
	*/
  var $debug  = 'n';

	/**
	* Extension name
	* @var string
	*/
	var $name	 = F_GEN_name;

  /**
   * The URL path to the assets directory.
   *
   * @access  private
   * @var     string
   */
	var $assets_url = '';
  
	/**
	* Extension version
	* @var string
	*/
	var $version	 = F_GEN_version;

  /**
   * Extension class name.
   *
   * @access  private
   * @var     string
   */
  var $class_name;

	/**
	* Extension description
	* @var string
	*/
	var $description   = 'Assign and order multiple field groups to weblogs.';

	/**
	* If $settings_exist = 'y' then a settings page will be shown in the ExpressionEngine admin
	* @var string
	*/  
	var $settings_exist  = 'y';

	/**
	* Link to extension documentation
	* @var string
	*/
	var $docs_url   = F_GEN_docs_url;
	
	/**
	 * The URL of the settings page for this extension.
	 *
	 * @access  private
	 * @var     string
	 */
	 
	var $settings_url = '';
	
	/**
	 * PHP4 constructor.
	 * @see __construct
	 */
	function Field_general_ext($settings='')
	{
		$this->__construct($settings);
	}
  
  /**
   * PHP5 constructor
   * @param array|string $settings Extension settings; associative array or empty string.
   */
  function __construct($settings='')
  {
    global $PREFS;
    // Initialise the class name.
		$this->class_name = strtolower(get_class($this));
		$this->assets_url = $PREFS->ini('theme_folder_url') . $this->class_name . '/assets/';
    $this->ajax_url = str_replace('&amp;', '&', $this->settings_url);
    $this->site_id = $PREFS->ini('site_id');
    
		// Retrieve the settings from the database.
    $this->_refresh_settings();
    
		
    /**
		 * ----------------------------------------------------
		 * Now for the conditional initialisation stuff.
		 * ----------------------------------------------------
		 */
		 
		if (defined('BASE') && defined('AMP'))
		{
		  // Initialise the settings URL.
  		$this->settings_url = BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=extension_settings' . AMP . 'name=' . $this->class_name;
		}
		 
		if (isset($LANG))
		{
  		// Need to explicitly set the Language file.
  		$LANG->fetch_language_file($this->class_name);
		}
		
  }
	
	/**
	 * Registers a new addon.
	 * @param			array 		$addons			The existing addons.
	 * @return 		array 		The new addons list.
	 */
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;
		
		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$addons = $EXT->last_call;
		}
		
		// Register a new addon.
		if ($this->settings['update_check'] == 'y')
		{
			$addons[$this->name] = $this->version;
		}
		
		return $addons;
	}
	
	/**
	 * Registers a new addon source.
	 * @param			array 		$sources		The existing sources.
	 * @return		array 		The new source list.
	 */
	function lg_addon_update_register_source($sources)
	{
		global $EXT;
		
		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$sources = $EXT->last_call;
		}
		
		// Register a new source.
		if ($this->settings[$this->site_id]['update_check'] == 'y')
		{
			$sources[] = 'http://github.com/timkelty/field_general.ee_addon/raw/master/versions.xml';
		}
		
		return $sources;
	}
	
	/**
	 * Builds the breadcrumbs part of the settings form.
	 * @access	private
	 * @return  string    The "Breadcrumbs" HTML.
	 */
	function _settings_form_breadcrumbs()
	{
		global $DSP, $LANG;
		
		$r = '';
		$r .= $DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'P=utilities', $LANG->line('utilities'));
		$r .= $DSP->crumb_item($DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=extensions_manager', $LANG->line('extensions_manager')));
		$r .= $DSP->crumb_item($LANG->line('extension_name'));
		
		$r .= $DSP->right_crumb(
			$LANG->line('disable_extension'),
			BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=toggle_extension' . AMP . 'which=disable' . AMP . 'name=' . strtolower(get_class($this))
		);
		
		return $r;
	}
	
	/**
	 * Builds the "Check for Updates" part of the settings form.
	 * @access	private
	 * @return  string    The "Check for Updates" HTML.
	 */	
	function _settings_form_updates()
	{
		global $DSP, $LANG, $PREFS;
		
		$r  = '';
		
		// Automatic updates.
		$r .= $DSP->table_open(
			array(
				'class' 	=> 'tableBorder',
				'border' 	=> '0',
				'style' 	=> 'width : 100%; ',
				)
			);
			
		$r .= $DSP->tr();
		$r .= $DSP->td('tableHeading', '', '2');
		$r .= $LANG->line('update_check_title');
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$r .= $DSP->tr();
		$r .= $DSP->td('', '', '2');
		$r .= "<div class='box' style='border-width : 0 0 1px 0; margin : 0; padding : 10px 5px'><p>" . $LANG->line('update_check_info') . "</p></div>";
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();	
		
		$r .= $DSP->tr();
		$r .= $DSP->td('tableCellOne', '40%');
		$r .= $DSP->qdiv('defaultBold', $LANG->line('update_check_label'));
		$r .= $DSP->qdiv('subtext', $LANG->line('update_check_subtext'));
		
		$r .= $DSP->td_c();
		
		$update_check = isset($this->settings[$this->site_id]['update_check']) ? $this->settings[$this->site_id]['update_check'] : 'y';
		
		$r .= $DSP->td('tableCellOne', '60%');
		
		$r .= '<label style="cursor:pointer; margin-right: 10px">' . $LANG->line('yes');
    $r .= $DSP->input_radio('update_check', 'y', ($update_check == 'y' ? 1 : 0));
    $r .= '</label><label style="cursor:pointer">' . $LANG->line('no');
    $r .= $DSP->input_radio('update_check', 'n', ($update_check == 'n' ? 1 : 0));
    $r .= '</label>';
    
		$r .= $DSP->td_c();
		
		$r .= $DSP->tr_c();
		$r .= $DSP->table_c();
		
		return $r;
	}
		
	/**
	 * Builds the "Save Settings" part of the settings form.
	 * @access  private
	 * @return  string    The "Save Settings" HTML.
	 */
	function _settings_form_save()
	{
	  global $DSP, $LANG;
	  
		return $DSP->qdiv('box itemWrapperTop', $DSP->input_submit($LANG->line('save_settings'), 'save_settings', 'id="save_settings"'));
	}
	
	/**
	 * Builds the custom page headers part of the UI.
	 *
	 * @access	private
	 */
  function _settings_form_headers()
  {   
    $r  = '';
    $r .= '<link rel="stylesheet" type="text/css" media="screen" href="' . $this->assets_url . 'styles/cp.css">';
    $r .= '<script type="text/javascript" src="' . $this->assets_url . 'scripts/cp.js"></script>';

    // All done.
    return $r;
  }
  
	
	/**
	 * Refreshes the settings. If this is the first time here, the settings are
	 * pulled from the database. If there is POST data to process, we do the
	 * necessary, and update the settings array accordingly.
	 *
	 * @access  private
   */
  function _refresh_settings()
  {
    global $DB, $REGX, $PREFS;
		
		$settings = FALSE;		// Assume no settings
		
		// Check if we've already retrieved the settings from the database. If not, do it.
		if (isset($this->settings) === FALSE)
		{
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '" . $this->class_name . "' LIMIT 1");
			
			// If we have settings, save them.
			if ($query->num_rows == 1 && $query->row['settings'] != '')
			{
				$this->settings = $REGX->array_stripslashes(unserialize($query->row['settings']));
			}
		}
		
		// Fill in the gaps in our settings array.
		if ( ! isset($this->settings[$this->site_id]['weblogs'])) $this->settings[$this->site_id]['weblogs'] = array();
	
    // Update our settings with any form submission data.				
		if (isset($_POST['update_check']) === TRUE) $this->settings[$this->site_id]['update_check'] = $_POST['update_check'];
		
    // Process weblog groups
		if (isset($_POST['weblogs']) === TRUE)
		{
      $this->settings[$this->site_id]['weblogs'] = $_POST['weblogs']; 		 
		}
		return $settings;
  }
	
	/**
	 * Saves the Extension settings.
	 *
	 * @access  public
	 */
	function save_settings()
	{
		global $DB;
		
		$this->_refresh_settings();
		
		// Serialise the settings, and save them to the database.
		$sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($this->settings)) . "' WHERE class = '" . $this->class_name . "'";
		$DB->query($sql);
	}
	
	function activate_extension()
	{
		global $DB;
		
		$settings = $this->_refresh_settings();
		
    // Delete old hooks
		$DB->query("DELETE FROM exp_extensions
		            WHERE class = '{$this->class_name}'");

    // hooks
		$hooks = array(
  		'publish_form_field_query'				=> '_get_fields',
  		'publish_form_weblog_preferences' => '_weblog_id',
  		'lg_addon_update_register_source' => 'lg_addon_update_register_source',
  		'lg_addon_update_register_addon'  => 'lg_addon_update_register_addon',
    );
		
		foreach ($hooks AS $hook => $method)
		{
			$sql[] = $DB->insert_string('exp_extensions', array(
					'class'        => get_class($this),
					'method'       => $method,
					'hook'         => $hook,
    			'settings'     => $settings ? addslashes(serialize($settings)) : '',
					'priority'     => 10,
					'version'      => $this->version,
					'enabled'      => 'y'
					));
		}
		
		// Run all the SQL queries.
		foreach ($sql AS $query)
		{
			$DB->query($query);
		}
	}

	/**
	 * Updates the extension.
	 * @param string $current Contains the current version if the extension is already installed, otherwise empty.
	 * @return bool FALSE if the extension is not installed, or is the current version.
	 */
	function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < $this->version)
		{
			$DB->query("UPDATE exp_extensions
				SET version = '" . $DB->escape_str($this->version) . "' 
				WHERE class = '" . get_class($this) . "'");
		}
	}

  /**
   * Disables the extension, and deletes settings from the database.
   */
  function disable_extension()
  {
  	global $DB;	
  	$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
  }

	/**
	 * Get Fields
	 *
	 * @param  array    $obj           the Publish class object
	 * @param  string   $field_group   the custom field group assigned to this weblog
	 * @return object                  the DB object
	 * @see    http://expressionengine.com/developers/extension_hooks/publish_form_field_query/
	 * @since  version 1.0.0
	 */
	function _get_fields($obj, $field_group)
	{
		global $DB, $DSP, $EXT, $LANG, $PREFS, $CURRENT_WEBLOG_ID;
				    
    $groups = $this->settings[$this->site_id]['weblogs'][$CURRENT_WEBLOG_ID]['field_groups'];
    $groups_filtered = array();
    
    uasort($groups, array($this, '_sort_groups'));

    // build string
    foreach($groups as $gk => $gv)
    {
      if ($gv['active'] == 'y')
      {
        $groups_filtered[$gk] = $gv;
      }
    }
    $groups_str = !empty($groups_filtered) ? implode(',', array_keys($groups_filtered)) : '\'\'';

		// Get fields
		if ($EXT->last_call !== FALSE)
		{
    	$field_query = $EXT->last_call;
    }
    else
    {
			$field_query = $DB->query("SELECT *
			                           FROM exp_weblog_fields
			                           WHERE group_id IN ($groups_str)
			                           ORDER BY field_order");
	  }

		$result = array();

		// order field by group order
		foreach($groups as $gk => $gv)
		{
      foreach($field_query->result as $field)
  		{
  		  if ($field['group_id'] == $gk)
  		  {
  		    $result[] = $field;
  		  }
  		}
		}		
		
		$field_query->result = $result;
		$field_query->num_rows = count($result);
		$field_query->row = $field_query->num_rows ? $result[0] : array();

		return $field_query;
  }
  
	/**
	 * Get Current Weblog ID
	 *
	 * @param  array    $row    Row of results from database for the weblog of this entry form
	 * @return array
	 * @see    http://expressionengine.com/developers/extension_hooks/publish_form_weblog_preferences/
	 * @since  version 1.4.1
	 *
	 * Thanks to Max Lazar (WiseUp Studio)
	 * Referenced from MX Custom Fields Access: http://devot-ee.com/add-ons/mx-custom-fields-access/
	 */

	function _weblog_id($row)
	{
		global $EXT, $CURRENT_WEBLOG_ID;
		$CURRENT_WEBLOG_ID = $row['weblog_id'];
		return ($EXT->last_call !== FALSE)	? $EXT->last_call	: $row;
	}

	/**
	 * The extension settings page
	 */
	function settings_form($current)
	{
		global $DB, $DSP, $LANG, $IN, $PREFS, $SESS;
		
		if($this->debug == 'y')
		{
			print '<pre>';
			print_r($current);
			print '</pre>';
		}
		
		// AJAX requests are dealt with separately, by show_full_control_panel_end.
		if ($IN->GBL('ajax_request') === 'y')
		{
			return FALSE;
		}
		
    $query_field_groups = $DB->query("SELECT exp_field_groups.group_id, exp_field_groups.group_name, exp_field_groups.site_id,
                                      COUNT(exp_weblog_fields.group_id) as count 
                                      FROM exp_field_groups
                                      LEFT JOIN exp_weblog_fields ON (exp_field_groups.group_id = exp_weblog_fields.group_id)
                                      GROUP BY exp_field_groups.group_id
                                      ORDER BY exp_field_groups.site_id, exp_field_groups.group_name");        

    $query_weblogs = $DB->query("SELECT weblog_id, blog_title, field_group
                                 FROM exp_weblogs 							  
                                 WHERE site_id = " . $this->site_id . "
                                 ORDER BY blog_title");
 		// get the list of installed sites
 		$query_sites = $DB->query("SELECT site_id, site_label FROM exp_sites");
    
 		// Only grab settings for the current site (if they exist)
    $current = (array_key_exists($this->site_id, $current)) ? $current[$this->site_id] : array();

		// Start building the page.
    $headers        = $this->_settings_form_headers();  // Additional CSS and JS headers.
		$breadcrumbs 		= $this->_settings_form_breadcrumbs();	// Breadcrumbs.
		$browser_title 	= $LANG->line('extension_settings');		// Browser title.
		
		// Body
		$body  = '';
    $body .= $DSP->heading($LANG->line('extension_name') . " <small>v{$this->version}</small>");   // Main title.

		// Open the form.
		$body .= $DSP->form_open(
			array(
				'action'	=> 'C=admin' . AMP . 'M=utilities' . AMP . 'P=save_extension_settings',
				'id'			=> 'form_save_settings',
				'name'		=> 'form_save_settings'
				),
			array(
				'name' 			=> $this->class_name,		// Must be lowercase.
				'action'		=> 'save_settings',
				)
			);
      
    foreach ($query_weblogs->result as $weblog)
    {
      $expanded = @$current['weblogs'][$weblog['weblog_id']]['expanded'] == 'y';
      $table_class  = 'tableBorder table-sortable';
      $table_class .= $expanded ? '' : ' collapsed'; 
      
      $body .= $DSP->table_open(array('class' => $table_class, 'style' => 'width: 100%;'));
  		
      // weblog headers
      $body .= '<thead>';
      $body .= '<tr class="weblog-head">';      
      $body .= $DSP->td('tableHeading', '', '3');
      $body .= $weblog['blog_title'];
      
      $body .= '<label class="expander" style="cursor:pointer;">';
      $body .= $LANG->line('toggle');
      $body .= $DSP->input_checkbox('weblogs[' . $weblog['weblog_id'] . '][expanded]', 'y', ($expanded) ? 1 : 0);      
      $body .= '</label>';
      
      $body .= $DSP->td_c();
      $body .= $DSP->tr_c();
      
      // field group headers
      $body .= '<tr class="groups-head">';      
            
      $body .= $DSP->td('tableHeadingAlt', '5%');
      $body .= $LANG->line('order');
      $body .= $DSP->td_c();
      
      $body .= $DSP->td('tableHeadingAlt', '','2');
      $body .= $LANG->line('field_group');
      $body .= $DSP->td_c();
            
      $body .= $DSP->tr_c();
      $body .= '</thead>';
      
      $body .= '<tbody>';
      
      $field_groups = $query_field_groups->result;
      $max = array();
      
      // merge in order and active from settings, find max order
      foreach ($field_groups as $k => $v)
      {
        $field_groups[$k]['order'] = @$current['weblogs'][$weblog['weblog_id']]['field_groups'][$v['group_id']]['order'];
        $field_groups[$k]['active'] = @$current['weblogs'][$weblog['weblog_id']]['field_groups'][$v['group_id']]['active'];
        $max[] = $field_groups[$k]['order'];
      }
      $max = max($max);
      
      // add max if there is none
      foreach ($field_groups as $k => $v)
      {
        if ( ! $v['order'])
        {
          $max++;
          $field_groups[$k]['order'] = $max;
        }
      }
      
      // sort by order
      uasort($field_groups, array($this, '_sort_groups'));
      
      // field group row
      foreach ($field_groups as $field_group) {
        $stripe_class = (@$stripe_class == 'tableCellOne') ? 'tableCellTwo': 'tableCellOne';
        
        $body .= $DSP->tr();
        
        $body .= $DSP->td($stripe_class, '5%');
        $body .= $DSP->input_text('weblogs[' . $weblog['weblog_id'] . '][field_groups][' . $field_group['group_id'] . '][order]', $field_group['order'], '4', '' , 'sequence', '30px');
        $body .= $DSP->td_c();
        
        $body .= $DSP->td($stripe_class);
        
        if ($query_sites->num_rows > 1)
        {
          foreach ($query_sites->result as $site)
          {
            if ($site['site_id'] == $field_group['site_id'])
            {
              $body .= $DSP->qdiv('default', $site['site_label']);
            }
          }
        }
        
        $body .= $DSP->qdiv('defaultBold', $field_group['group_name']);
        
        $body .= $DSP->td_c();
      
        $body .= $DSP->td($stripe_class);
        
        $body .= '<label style="cursor:pointer; margin-right: 10px">' . $LANG->line('on');
        $body .= $DSP->input_radio('weblogs[' . $weblog['weblog_id'] . '][field_groups][' . $field_group['group_id'] . '][active]', 'y', ($field_group['active'] == 'y') ? 1 : 0);
        $body .= '</label><label style="cursor:pointer">' . $LANG->line('off');
        $body .= $DSP->input_radio('weblogs[' . $weblog['weblog_id'] . '][field_groups][' . $field_group['group_id'] . '][active]', 'n', ($field_group['active'] != 'y') ? 1 : 0);
        $body .= '</label>';
        $body .= $DSP->td_c();

        $body .= $DSP->tr_c();
      }
      $body .= '</tbody>';
      $body .= $DSP->table_close();
    }
	  
    // Check for updates / save settings.
		$body .= $this->_settings_form_updates() . $this->_settings_form_save();
		
		// Close the form.
		$body .= $DSP->form_c();
		
		// Output everything.
    $DSP->extra_header  .= $headers;
		$DSP->title 				= $browser_title;
		$DSP->crumbline 		= TRUE;
		$DSP->crumb 				= $breadcrumbs;
		$DSP->body 					= $body;
	}
	
	// sort groups by order
  function _sort_groups($a, $b) {
    return ($a['order'] < $b['order']) ? -1 : 1;
  }
  	
}
?>
