<?php /*
* Plugin Name: 1_todo-list
* Plugin URl: http://www.RobFrancoZone.com
* Description: allows user to create, display, rename and delete his own to-do lists as well ass add, display, edit and reorder its entries
* Author: Robert Frank
* Author URl: http://www.RobFrancoZone.com
* Version: 0.1
*
*
*/

ob_start();

/**
* class rfztd contains all methods for this plugin excepting filters and actions
*
* - create Admin menu page with the functionality to create new submenu pages representing a new list
* - within list its elements can be added, deleted and ordered differently
* - list data stored in database table
*/
class rfztd {
	protected static $dbc;
	protected static $query;
	protected static $r;
	protected static $row; //array
	protected static $i;
	protected static $q_array; //array
	protected static $q_array_rename; //array
	protected static $q_array_delete; //array
	protected static $url;
	protected static $content;
	protected static $value;
	protected static $array_key;
	
	protected static $lists_count = 1;
	protected static $list_names; //array
	protected static $list_entries; //array
	protected static $order;
	protected static $new_lname;
	
	
	function reload() {
		if(isset($_GET['reload']) && $_GET['reload'] == 1) {
			header('location: '
		}
	}
	
	/**
	* connects with database
	*
	* - uses DB CONSTANTS from wp-config.php
	*/
	function connectDatabase() {
		self::dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}

	
	/*
	* called when user tries to add a List intitially to list all lists
	*
	* 
	*/
	function createTable() {
		if(empty(self::database)){
			self::connectDatabase();
		}
		
		self::query = "CREATE TABLE rfztd_lists (
id INT UNSIGNED NOT NULL AUTO_INCREMENT,
list VARCHAR(10000) NOT NULL,
order VARCHAR(10000) NOT NULL DEFAULT 'date_modified DESC',
date_modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)
) CHARACTER SET utf8";
		
		if (mysqli_query(self::dbc, self::query)) {
			print '<p>The table has been created!</p>';
		} else {
			print '<p style="color: red;">Could not create the table because:<br>' . mysqli_error(self::dbc). 
			'.</p><p>The query being run was: ' . self::query . '</p>';			
		}
		
		//insert a row containing user selected order to list lists
		self::query = "INSERT INTO rfztd_lists (list, date_modified) VALUES ('name ASC', NOW())";
			if (mysqli_query(self::dbc, self::query)) {
				print '<p>The entry of the order has successfully been inserted!</p>';
			} else {
				print '<p style="color: red;">Could not insert the entry because:<br>' . mysqli_error(self::dbc) .
				'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
	}
	/**
	* creates both new table and adds table name to rfztd_lists
	*/
	function addList(/*$new_lname*/) {
		
		if(empty(self::database)){
			self::connectDatabase();
		}
		
	
		self::query = "CREATE TABLE rfztd_{$_GET['list']} (
id INT UNSIGNED NOT NULL AUTO_INCREMENT,
done TINYINT(1) NOT NULL DEFAULT 0,
element VARCHAR(10000) NOT NULL,
priority VARCHAR(30) NOT NULL DEFAULT 'B',
date_modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)
) CHARACTER SET utf8";
		
		if (mysqli_query(self::dbc, self::query)) {
			print '<p>The table has been created!</p>';
			
			//insert new list name into the list of lists
				self::query = "INSERT INTO rfztd_lists (list, "
				if(isset($_POST['order']) {return ($_POST['order'] != NULL) ? "order, " : ""}.
				"date_modified) VALUES ('{$_POST['name']}', "
				if(isset($_POST['order']) {return ($_POST['order'] != NULL) ? "'{$_POST['order']}', " : ""}.
				"NOW())";
				
			if (mysqli_query(self::dbc, self::query)) {
				print '<p>The entry has successfully been inserted into the list of lists!</p>';
			} else {
				print '<p style="color: red;">Could not insert the entry because:<br>' . mysqli_error(self::dbc) .
				'.</p><p>The query being run was: ' . self::query . '</p>';	
			}
		} else {
			print '<p style="color: red;">Could not create the table because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
		
		
	}
	
	
	
	/**
	* outputs every list element in custom order from a respective MySql Table and provides edit and delete link
	*
	* outputs edit and delete link created with NONCE functions
	*/
	function retrieveElements() {
		if(empty(self::database)){
			self::connectDatabase();
		}
	
		//save statically in which order to output the list elements by checking list of lists
		self::query = "SELECT order FROM rfztd_lists WHERE list=rfztd_{$_GET['page']}";
		
		if (self::r = mysqli_query(self::dbc, self::query)) {
			self::order(self::row = mysqli_fetch_array(self:r)) ? self::row['order'] : NULL;
			
		} else {
			print '<p style="color: red;">Could not create the table because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
		
		
		
		//retrieve + output actual list elements in previously retrieved order
		self::query = "SELECT id, done, element, priority, date_modified FROM rfztd_{$_GET['page']}" .
		(return(self::order != NULL) ? "ORDER BY self::order" : "");
		
		if (self::r = mysqli_query(self::dbc, self::query)) {
			while(self::row = mysqli_fetch_array(self::r)) {
				//creates edit link to revise elements content AND MAKES IT SECURE BY NONCE in URL
				self::q_array =[
					'action' => 'rfz_edit',
					'element_id' => NULL,
					'nonce' => wp_create_nonce('rfz_edit')
				];
				self::q_array['element_id'] = self::row['id'];
				self::url = add_query_arg(self::q_array, admin_url('admin.php?page=' . $_GET['page']));
				
				// POST is sending done, priority, element and id from row of the clicked submit button
				print '<form action="' . esc_url(self::url) . '" method="POST" enctype="multipart/form-data">
				<p><input type="checkbox" name="done" '.
				(return(self::row['done']==1) ?
				'"checked"' : '') . 
				' onChange="this.form.submit()"><select name="priority">
					<option value="'. self::row['priority'] . '">' . self::row['priority'] . '</option>
					<option value="A">A</option>
					<option value="B">B</option>
					<option value="C">C</option>
					<option value="D">D</option>
					<option value="E">E</option>
				</select> <textarea name="element" rows="2" cols="35">' . esc_html(self::row['element']) . '</textarea>
				<select name="order">
					<option value="date modified">date_modified DESC</option>
					<option value="alphabetical">element ASC</option>
					<option value="priority">priority ASC</option>
				</select>
<!--<input type="hidden" name="id" value="' . self::row['id'] . '">-->
<!--<input type="hidden" name="list" value="' . $_GET['page'] . '">-->
				<input type="submit" name="edit" value="save element">';
				
				
				
				//create delete link to delete element
				self::content = 'delete';

				self::q_array =[
					'action' => 'rfz_delete',
					'element_id' => NULL,
					'nonce' => wp_create_nonce('rfz_delete')
				];
				self::q_array['element_id'] = self::row['id'];
				self::url = add_query_arg(self::q_array, admin_url('admin.php?page='. $_GET['page']));
				echo '    <a href="' . esc_url(self::url) . '">' . esc_html__(self::content) . '</a></p><hr></form>';
		
				
				
			}
			print '<hr>';
		} else {
			print '<p style="color: red;">Could not retrieve the entry because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
		
		// add a new element
				self::q_array =[
					'action' => 'rfz_add',
					'nonce' => wp_create_nonce('rfz_add')
				];
				self::url = add_query_arg(self::q_array, admin_url('admin.php?page=' . $_GET['page']));
		
		print '<p><h3><b>Add a new element: </b><h3></p>
			<form action="' . esc_url(self::url) . '" method="POST" enctype="multipart/form-data">
				<p><input type="checkbox" name="done"><select name="priority">
					<option value="B">B</option>
					<option value="A">A</option>
					<option value="B">B</option>
					<option value="C">C</option>
					<option value="D">D</option>
					<option value="E">E</option>
				</select> <textarea name="element" rows="2" cols="35"></textarea>
				<select name="order">
					<option value="date modified">date_modified DESC</option>
					<option value="alphabetical">element ASC</option>
					<option value="priority">priority ASC</option>
				</select>
<!--<input type="hidden" name="id" value="' . self::row['id'] . '">-->
<!--<input type="hidden" name="list" value="' . $_GET['page'] . '">-->
				<input type="submit" name="submit" value="save element"></form>';
	}
	
	/**
	* adds new element (row) to specific list
	*/
	function addElement() {
		if(empty(self::database)){
			self::connectDatabase();
		}
		
		self::query = "INSERT INTO rfztd_{$_GET['page']} (" .
			if(isset($_POST['done'])) {($_POST['done']) ? return "done, " : return ""} .
			"element, " .
			if(isset($_POST['priority'])) {($_POST['priority']) ? return "priority, " : return ""} .
"date_modified
) VALUES (" .
			if(isset($_POST['done'])) {($_POST['done']) ? return "1, " : return ""} .
			"'{$_POST['element']}', " .
			if(isset($_POST['priority'])) {($_POST['priority']) ? return "'{$_POST['priority']}', " : return ""} .
			"NOW())";
		
		if (mysqli_query(self::dbc, self::query)) {
			print '<p>The entry (element) has been successfully inserted!</p>';
		} else {
			print '<p style="color: red;">Could not create the table because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
	}
	
	
	/**
	* change existing List elements(entries)
	*/
	function updateList() {
		if(empty(self::database)){
			self::connectDatabase();
		}
		
			self::query = "UPDATE rfztd_{$_GET['page']} SET " .
			if(isset($_POST['done'])) {($_POST['done']) ? return "done=1," : return ""} .
			"priority='{$_POST['priority']},
element='{$_POST['element']}',
date_modified=NOW()
WHERE id={$_GET['element_id']}";
			
		self::r=mysqli_query(self::dbc, self::query);
		if(mysqli_affected_rows(self::dbc) == 1){
			echo '<p style="color: green; font-weight: bold;">You successfully updated the element: ' . $_POST['element'] . '</p>';
		}else {
			print '<p style="color: red;">Could not update the entry because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
	}
	
	/**
	* rename List itself
	*/
	function renameList(){
		if(empty(self::database)){
			self::connectDatabase();
		}
		
		self::query = "UPDATE rfztd_lists SET " . if(isset($_GET['list'])) {($_GET['list']) ? return "list=1," : return ""} .
	}
	
	/*
	* put List elements in custom order
	*/
	function reorderList() {
		
	}
	
	/**
	* delete database entry
	*/
	function deleteElement() {
		if(empty(self::database)){
			self::connectDatabase();
		}
		
		self::query = "DELETE FROM rfztd_{$_GET['page']} WHERE id={$_GET['element_id']}";
			
		self::r=mysqli_query(self::dbc, self::query);
		if(mysqli_affected_rows(self::dbc) == 1){
			echo '<p style="color: green; font-weight: bold;">You successfully deleted the element.</p>';
		}else {
			print '<p style="color: red;">Could not delete the entry because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
	}
	
	
	
/**************************************************************************************************************** */
	
	function addMenu() {
		add_menu_page("rfz To-do List", "create/rename/delete Lists", 4, "rfz-todo", "self::mainMenu");
		
		if(empty(self::database)){
			self::connectDatabase();
		}
		self::query = "SELECT list FROM rfz_lists ORDER BY date_modified DESC";
		if(self::r = mysqli_query(self::dbc, self::query)) {
			for(self::i = 0; self::row = mysqli_fetch_array(self::r); self::i ++) {
				if (self::i > 0) {
					add_submenu_page(
					"rfz-todo", 
					return (isset(self::row['list'])) ? esc_html(self::row['list']) : 'List '. self::i,
					return (isset(self::row['list'])) ? esc_html(self::row['list']) : 'List '. self::i,
					4,
					self::row['list'], //assigns list name to page variable in URL 
					"self::submenu"
					);
				}
			}	
		} else {
			print '<p style="color: red;">Could not retrieve the entry because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
	}

	function mainMenu()
	{
		if(empty(self::database)){
			self::connectDatabase();
		}
		
		//check if list shall already be added and in case call addList()
		if((isset($_GET['action']) && 
		isset($_GET['list']) &&
		isset($_GET['nonce']) && 
		isset($_GET['element_id'], $_GET['page'])) && 
		($_GET['action'] === 'rfz_add_list') && 
		wp_verify_nonce($_GET['nonce'],'rfz_add_list')) {
			self::addList();
		}
		
		//check if list shall be renamed and in case call renameList()
		if((isset($_GET['action']) && 
		isset($_GET['list']) &&
		isset($_GET['nonce']) && 
		isset($_GET['element_id'], $_GET['page'])) && 
		($_GET['action'] === 'rfz_rename') && 
		wp_verify_nonce($_GET['nonce'],'rfz_rename')) {
			self::renameList();
		}
		
		//check if list shall be deleted and in case call deleteList()
		if((isset($_GET['action']) && 
		isset($_GET['list']) &&
		isset($_GET['nonce']) && 
		isset($_GET['element_id'], $_GET['page'])) && 
		($_GET['action'] === 'rfz_delete') && 
		wp_verify_nonce($_GET['nonce'],'rfz_delete')) {
			self::deleteList();
		}
		
		
		// add a new list
		self::q_array =[
			'page' => 'rfz-todo',
			'action' => 'rfz_add_list',
			'nonce' => wp_create_nonce('rfz_add_list')
		];
		
		//self::url = add_query_arg(self::q_array, admin_url('admin.php?page=rfz-todo'));
		self::url = 'admin.php';
		
		// Text box and Button to create a new list at beginning of page
		echo '<h2>Create a new List:</h2><br>
		<form action="'. self::url .'" method="get">';
		foreach(self::q_array as self::array_key => self::value){
			echo '<input type="hidden" name="'. self::array_key .'" value="'. self::value .'">';
		}
		echo '<input type="text" name="list" placeholder="list name (max. 90 characters)" size="90" maxlength="90" required> 
		<input type="submit" value="<h2><b>Add this list</h2></b>" 
			style="padding:5px 15px; 
				background:#ccc; 
				border:0 none;
				cursor:pointer;
				-webkit-border-radius: 5px;
				border-radius: 5px;">
		</form><br><hr><br>';
		
		// Retrieve and display existing lists and give user options to modify
		/*
					+------------------+ +--------+ +--------+
			- view	| |my list 1       | | rename | | delete |  
					+------------------+ +--------+ +--------+
		*/
		echo '<h2> view/rename/delete existing Lists:</h2><p>';
		self::query = "SELECT list FROM rfztd_lists LIMIT 1, 18446744073709551615";
		
		if (self::r = mysqli_query(self::dbc, self::query)) {
			self::q_array_rename =[
			'page' => 'rfz-todo',
			'action' => 'rfz_rename',
			'nonce' => wp_create_nonce('rfz_rename')
			];
			self::q_array_delete =[
			'page' => 'rfz-todo',
			'action' => 'rfz_delete',
			'nonce' => wp_create_nonce('rfz_delete')
			];
			echo '<ul>';
			for(self::row = mysqli_fetch_array(self::r)) {
				echo '<li><form action="admin.php" method="get">';
				
				foreach(self::q_array_rename as self::array_key => self::value){ //ensures action name (for wp_verify_nonce) to be transmitted through URL
					echo '<input type="hidden" name="'. self::array_key .'" value="'. self::value .'">';
				}
				echo '<a href="admin.php?page='. self::row['list'] .'">view</a>
				<input type="text" name="list" value="'. self::row['list'] .'" size="45">
				<input type="submit" name="submit" value="rename"></form>
				<form action="admin.php" method="get">';
				
				foreach(self::q_array_delete as self::array_key => self::value){ //ensures action name (for wp_verify_nonce) to be transmitted through URL
					echo '<input type="hidden" name="'. self::array_key .'" value="'. self::value .'">';
				}
				echo '<input type="submit" name="submit" value="delete"></form></li>';
			}
			echo '</ul>';
		} else {
			print '<p style="color: red;">Could not retrieve the entry because:<br>' . mysqli_error(self::dbc) .
			'.</p><p>The query being run was: ' . self::query . '</p>';	
		}
}
	
	/**
	* automatically called when user opens a specific submenu
	*/	
	function submenu()
	{
		echo "<h2><b>Elements of list: ". $_GET['page'] .":</h2></b>";
		
		//check after changes to be done and in case call updateList()
		if((isset($_GET['action']) && 
		isset($_POST['done'], $_POST['priority'], $_POST['element']) &&
		isset($_GET['nonce']) && 
		isset($_GET['element_id'], $_GET['page'])) && 
		($_GET['action'] === 'rfz_edit') && 
		wp_verify_nonce($_GET['nonce'],'rfz_edit')) {
			self::updateList();
		}
		
		//check if entry shall be deleted and in case call deleteElement()
		if((isset($_GET['action']) && 
		isset($_POST['done'], $_POST['priority'], $_POST['element']) &&
		isset($_GET['nonce']) && 
		isset($_GET['element_id'], $_GET['page'])) && 
		($_GET['action'] === 'rfz_delete') && 
		wp_verify_nonce($_GET['nonce'],'rfz_edit')) {
			self::deleteElement();
		}
		
		//check if entry shall be added and in case call addElement()
		if((isset($_GET['action']) && 
		isset($_POST['done'], $_POST['priority'], $_POST['element']) &&
		isset($_GET['nonce']) && 
		isset($_GET['page'])) && 
		($_GET['action'] === 'rfz_add') && 
		wp_verify_nonce($_GET['nonce'],'rfz_edit')) {
			self::addElement();
		}
		
		//call retrieveElements()
		self::retrieveElements();
	}
}

add_action("admin_menu","rfztd::addMenu");


/* ************************************ REMOVE WIDGET and more advanced stuff ************************************* */

function rfztd_remove_dashboard_widget() {
	remove_meta_box('dashboard_primary', 'dashboard','post_container_1');
	//do sth
}
add_action( 'wp_dashboard_setup', 'rfztd_remove_dashboard_widget');

function rfzg_add_google_link() {
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'id' 	=> 'google_analytics',
		'title' => 'Google Analytics',
		'href' 	=> 'http://google.com/analytics'
	));
}
add_action( 'wp_before_admin_bar_render', 'rfzg_add_google_link');

/* function rfztd_filter_title($title)
{
    return 'The ' . $title . ' was filtered';
}
add_filter('the_title', 'rfztd_filter_title'); */

function rfzsp_settings_page_html()
{
    ?>
    Foo: <input id="foo" name="foo" type="text">
    Bar: <input id="bar" name="bar" type="text">
    <?php
}
do_action('rfzsp_after_settings_page_html');

function rfzas_add_settings()
{
    ?>
    New 1: <input id="new_setting" name="new_settings" type="text">
    <?php
}
add_action('rfztd_after_settings_page_html', 'rfztd_add_settings');

ob_end_flush();