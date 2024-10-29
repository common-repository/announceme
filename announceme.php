<?php
/*
Plugin Name: AnnounceME
Plugin URI: http://wordpress.org/extend/plugins/announceme/
Description: AnnounceME is a simple plugin, coded to help you publishing important Announcements, which can be read by every user of your Blog. AnnounceME uses the same design as Wordpress in backend, to make it easier to handle with it.
Author: Bernhard Bücherl
Version: 0.3.3
Author URI: http://profiles.wordpress.org/users/Berni1337/
License: GPL2
*/

$version = "0.3.3"; //Version Tag
$url = "http://wordpress.org/extend/plugins/announceme/"; //Plugin URL
$aurl = "http://profiles.wordpress.org/users/Berni1337/"; //Author URL 
$copyright = "<hr /><center><small>&copy; 2011 <a href='".$url."' target='_blank'>AnnounceME</a> - Version ".$version." | designed & coded by <a href='".$aurl."' target='_blank'>Bernhard B&uuml;cherl</a> | Icons of <a href='http://omercetin.deviantart.com' target='_blank'>omercetin</a>s free icon set <a href='http://omercetin.deviantart.com/art/PixeloPhilia-32PX-Icon-Set-157612627' target='_blank'>PixeloPhilia</a></small></center>";

//Plugin-Install
function announceme_install () {
   global $wpdb;
   $table_name = $wpdb->prefix . "announceme";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  `announce` tinytext NOT NULL,
	  `cat` int NOT NULL,
	  `img` text NOT NULL,
	  `active` int(1) DEFAULT '0',
	  `author` int(9) NOT NULL,
	  `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
          `c1` varchar(6) NULL,
          `c2` varchar(6) NULL,
          `c3` varchar(6) NULL,
	  `dsa` int(1) DEFAULT '1' NOT NULL,
          `dsatxt` text NULL,
	  UNIQUE KEY id (id)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   }

}

register_activation_hook(__FILE__,'announceme_install');


//Admin-Actions
	//Add Admin Scripts
	add_action('admin_head','announcemeAdminHead');

	function announcemeAdminHead() {
		?><link rel="stylesheet" type="text/CSS" href="<?php bloginfo('url'); ?>/wp-content/plugins/announceme/admin.css" /><?php
	}

	//Adding Adminmenupoint
	add_action('admin_menu','announcemeAdminMenu');

	function announcemeAdminMenu() {
		add_menu_page('AnnounceME','AnnounceME','edit_dashboard','AnnounceME','announcemeAdmin');
		add_submenu_page('AnnounceME', 'AnnounceME › Announcements', 'Announcements', 'edit_dashboard', 'AnnounceME', 'announcemeAdmin');
		add_submenu_page('AnnounceME', 'AnnounceME › New Announcement', 'New Announcement', 'edit_dashboard', 'AnnounceME-new', 'announcemeAdminNew');
	}


//Admin-Page Mainpage
function announcemeAdmin() { global $wpdb, $version, $copyright;
if(isset($_GET['action'])) { //Actionpages
 switch($_GET['action']) {
  case "delete":
   if(isset($_GET['id'])) { 
    mysql_query("DELETE FROM ".$wpdb->prefix."announceme WHERE id = '".$_GET['id']."'");
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcement was successfully deleted';</script><?php
   } elseif(isset($_GET['announce-id'])) { 
    foreach($_GET['announce-id'] as $id) {
     mysql_query("DELETE FROM ".$wpdb->prefix."announceme WHERE id = '".$id."'");
    }
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcements were successfully deleted';</script><?php
   } else { ?><script>top.location.href = 'admin.php?page=AnnounceME';</script><?php  }
  break; case "publish":
   if(isset($_GET['id'])) { 
    mysql_query("UPDATE ".$wpdb->prefix."announceme SET active = '1' WHERE id = '".$_GET['id']."'");
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcement successfully published';</script><?php
   } elseif(isset($_GET['announce-id'])) { 
    foreach($_GET['announce-id'] as $id) {
     mysql_query("UPDATE ".$wpdb->prefix."announceme SET active = '1' WHERE id = '".$id."'");
    }
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcements successfully published';</script><?php
   } else { ?><script>top.location.href = 'admin.php?page=AnnounceME';</script><?php  }
  break; case "unpublish":
   if(isset($_GET['id'])) { 
    mysql_query("UPDATE ".$wpdb->prefix."announceme SET active = '0' WHERE id = '".$_GET['id']."'");
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcement successfully unpublished';</script><?php
   } elseif(isset($_GET['announce-id'])) { 
    foreach($_GET['announce-id'] as $id) {
     mysql_query("UPDATE ".$wpdb->prefix."announceme SET active = '0' WHERE id = '".$id."'");
    }
    ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcements successfully unpublished';</script><?php
   } else { ?><script>top.location.href = 'admin.php?page=AnnounceME';</script><?php  }
  break; default: ?><script>top.location.href = 'admin.php?page=AnnounceME';</script><?php break; 
 }
} else { //MainPage
if(!isset($_GET['type'])) { $_GET['type']="all"; }
$qa = mysql_query("SELECT * FROM ".$wpdb->prefix."announceme");
$qp = mysql_query("SELECT * FROM ".$wpdb->prefix."announceme WHERE `active` = '1'");
$all_count = mysql_num_rows($qa);
$pub_count = mysql_num_rows($qp);
//Order General
$fororder = "admin.php?page=AnnounceME&type=".$_GET['type']."&orderby=";
if(!isset($_GET['orderby'])) { $_GET['orderby']="date"; }
if(!isset($_GET['order'])) { $_GET['order']="desc"; }
//Order Category
if($_GET['orderby']=="cat") { if($_GET['order']=="desc") { $sortcat = "sorted desc"; } else { $sortcat = "sorted asc"; } } else { $sortcat = "sortable desc"; }
$sortcat .= '"><a href="'.$fororder.'cat&order=';
if($_GET['orderby']=="cat") { if($_GET['order']=="desc") { $sortcat .= "asc"; } else { $sortcat .= "desc"; } } else { $sortcat .= "asc"; }
//Order Author
if($_GET['orderby']=="author") { if($_GET['order']=="desc") { $sortauthor = "sorted desc"; } else { $sortauthor = "sorted asc"; } } else { $sortauthor = "sortable desc"; }
$sortauthor .= '"><a href="'.$fororder.'author&order=';
if($_GET['orderby']=="author") { if($_GET['order']=="desc") { $sortauthor .= "asc"; } else { $sortauthor .= "desc"; } } else { $sortauthor .= "asc"; }
//Order Date
if($_GET['orderby']=="date") { if($_GET['order']=="desc") { $sortdate = "sorted desc"; } else { $sortdate = "sorted asc"; } } else { $sortdate = "sortable desc"; }
$sortdate .= '"><a href="'.$fororder.'date&order=';
if($_GET['orderby']=="date") { if($_GET['order']=="desc") { $sortdate .= "asc"; } else { $sortdate .= "desc"; } } else { $sortdate .= "asc"; }
$th = '<tr>
     <th scope="row" id="cb" class="manage-column column-cb check-column"><input type="checkbox"></th>
     <th scope="row" id="announcement" class="manage-column column-announcement">Announcement</th>
     <th scope="row" id="cat" style="width: 85px;" class="manage-column column-cat '.$sortcat.'"><span>Category</span><span class="sorting-indicator"></span></a></th>
     <th scope="row" id="author" style="width: 80px;" class="manage-column column-author '.$sortauthor.'"><span>Author</span><span class="sorting-indicator"></span></a></th>
     <th scope="row" id="date" style="width: 100px;" class="manage-column column-date '.$sortdate.'"><span>Date</span><span class="sorting-indicator"></span></a></th>
    </tr>';
?>
<div class="wrap" id="announceme">
 <div id="icon-announceme-main" class="icon32"><br /></div> 
 <h2>AnnounceME › Announcements <a href="admin.php?page=AnnounceME-new" class="button add-new-h2">+ Add</a></h2>
<?php if(isset($_GET['msg'])) { ?>
 <div id="message" class="updated below-h2">
  <p><?php echo $_GET['msg']; ?></p>
 </div>
<?php } ?>
 <ul class="subsubsub">
  <li><a href="admin.php?page=AnnounceME" <?php if($_GET['type']=="all") { ?> class="current"<?php } ?>>All <span class="count">(<?php echo $all_count; ?>)</span></a> |</li>
  <li><a href="admin.php?page=AnnounceME&type=published" <?php if($_GET['type']=="published") { ?> class="current"<?php } ?>>Published <span class="count">(<?php echo $pub_count; ?>)</span></a></li>
 </ul>
 <form method="get" action="admin.php"><input type="hidden" name="page" value="AnnounceME" />
  <table cellspacing="0" cellpadding="0" class="wp-list-table widefat fixed">
   <thead>
    <?php echo $th; ?>
   </thead>
   <tfoot>
    <?php echo $th; ?>
   </tfoot>
   <tbody id="the-list">
<?php 
if($_GET['type']=="published") { $where = " WHERE `active` = '1'"; } else { $where = ""; }
$q = "SELECT * FROM ".$wpdb->prefix."announceme".$where." ORDER BY `".$_GET['orderby']."` ".strtoupper($_GET['order']);
$q1 = mysql_query($q);
if(mysql_num_rows($q1)==0) {  ?>
    <tr><th colspan="5">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No Announcements here.</th></tr>
<?php } else {
while($q2 = mysql_fetch_array($q1)) {
$id = $q2['id']; $announce = $q2['announce']; $cat = $q2['cat']; $img = $q2['img']; $act = $q2['active'];
$u = get_userdata($q2['author']); $user = $u->user_login; $date = $q2['date'];
?>
    <tr id="announce-<?php echo $id; ?>" class="alternate" valign="top"> 
     <th scope="row" class="check-column"><input type="checkbox" name="announce-id[]" value="<?php echo $id; ?>" /></th> 
     <td class="column-title"><div style="width: 100%; min-height: 25px;"><img src="../wp-content/plugins/announceme/act-<?php echo $act; ?>.png" alt="" title="<?php if($act=='1') { ?>Published<?php } else { ?>Inactive<?php } ?>" width="20" height="20" style="float: left" /><strong style="padding: 2px 5px; float: left;"><?php echo $announce; ?></strong></div><div class="row-actions"><span class="edit"><a href="admin.php?page=AnnounceME-new&edit=<?php echo $id; ?>">Edit</a> | </span><span class="trash"><a class="submitdelete" href="admin.php?page=AnnounceME&action=delete&id=<?php echo $id; ?>">Delete</a> | </span><span class="publish"><?php if($act==1) { ?><a href="admin.php?page=AnnounceME&action=unpublish&id=<?php echo $id; ?>">Unpublish</a><?php } else { ?><a href="admin.php?page=AnnounceME&action=publish&id=<?php echo $id; ?>">Publish</a><?php } ?></span></div></td>
     <td class="column-categories" style="width: 85px;"><img src="<?php echo $img; ?>" alt="" width="20" height="20" /></td> 
     <td class="column-author" style="width: 80px;"><?php echo $user; ?></td> 
     <td class="column-date" style="width: 100px;"><?php echo $date; ?></td>		
    </tr> 
<?php } } ?>
   </tbody>
  </table>
  <div class="tablenav bottom">
   <div class="alignleft actions">
    <select name='action'> 
        <option value='-1' selected='selected'>Choose Action</option> 
	<option value='publish'>Publish</option> 
	<option value='unpublish'>Unpublish</option>
	<option value='delete'>Delete</option> 
    </select> 
    <input type="submit" name="" id="doaction" class="button-secondary action" value="Apply"  />
   </div>
  </div>
 </form>
<?php } echo $copyright; }

//Admin-Page New Announcement-Page
function announcemeAdminNew() { global $wpdb, $version, $copyright;
if(isset($_GET['submit'])) { $do = false;
 if(isset($_POST['publish'])) { $do = true; $act = '1'; } 
 if(isset($_POST['draft'])) { $do = true; $act = '0'; }
 $img[1] = get_bloginfo('url')."/wp-content/plugins/announceme/lamp.png";
 $img[2] = get_bloginfo('url')."/wp-content/plugins/announceme/help.png";
 $img[3] = get_bloginfo('url')."/wp-content/plugins/announceme/alert.png";
 $img[4] = $_POST['image'];
 if($do) {
  if($_POST['id']=="new") {
   mysql_query("INSERT INTO ".$wpdb->prefix."announceme (`announce`,`cat`,`img`,`active`,`author`,`date`,`c1`,`c2`,`c3`,`dsa`,`dsatxt`) VALUES ('".$_POST['announce']."', '".$_POST['cat']."', '".$img[$_POST['cat']]."', '".$act."', '".get_current_user_id()."', NOW(), '".$_POST['c1']."', '".$_POST['c2']."', '".$_POST['c3']."','".$_POST['dsa']."', '".$_POST['dsatxt']."')") or die(mysql_error());
   ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcement was created successfully';</script><?php
  } else {
   mysql_query("UPDATE ".$wpdb->prefix."announceme SET `announce` = '".$_POST['announce']."',`cat` = '".$_POST['cat']."',`img` = '".$img[$_POST['cat']]."',`active` = '".$act."',`author` = '".get_current_user_id()."', `c1` = '".$_POST['c1']."', `c2` = '".$_POST['c2']."', `c3` = '".$_POST['c3']."', `dsa` = '".$_POST['dsa']."', `dsatxt` = '".$_POST['dsatxt']."' WHERE id = '".$_POST['id']."'");
   ?><script>top.location.href = 'admin.php?page=AnnounceME&msg=Announcement was successfully changed';</script><?php
  }
 } else {
  ?><script>top.location.href = 'admin.php?page=AnnounceME';</script><?php
 }
}
if(isset($_GET['edit'])) { 
 $title = "Edit Announcement";
 $id = $_GET['edit'];
 $q1 = mysql_fetch_array(mysql_query("SELECT * FROM ".$wpdb->prefix."announceme WHERE id = '".$id."'"));
 $t = $q1['announce'];
 $c = $q1['cat'];
 $d = $q1['dsa'];
 $dt = $q1['dsatxt'];
} else { 
 $title = "New Announcement";
 $id = "new";
 $t = "";
 $c = "1";
 $d = "1";
 $dt = "";
}
?>
<div class="wrap" id="announceme">
 <div id="icon-announceme-new" class="icon32"><br /></div> 
 <h2>AnnounceME › <?php echo $title; ?></a></h2>
 <form method="post" action="admin.php?page=AnnounceME-new&submit">
  <input type="text" name="announce" size="30" tabindex="1" <?php if($t=="") { ?>value="Announcement text goes here..."<?php } else { ?>value="<?php echo $t; ?>" style="color: #333; font: 1.7em Verdana; line-height: 32px;"<?php } ?> title="Announcement text goes here..." id="announce-title" autocomplete="off" onfocus="if(this.title==this.value) { this.value = ''; this.style.color = '#333333'; this.style.font = '1.7em Verdana';}" onblur="if(this.value=='') { this.value = this.title; this.style.color = '#888888'; this.style.font = 'italic normal normal 1.7em Times New Roman';}" /> 
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <div id="poststuff">
   <div class="postbox"> 
    <h3 class='hndle'><span>Choose Category</span></h3> 
    <div class="inside"> 
     <input type="radio" name="cat" value="1" id="cat-1" onclick="document.getElementById('extra-cat-4').style.display='none';"<?php if($c=="1") { ?> checked="checked"<?php } ?> /><label for="cat-1"><img src="../wp-content/plugins/announceme/lamp.png" width="15" height="15" /> Information</label><br />
     <input type="radio" name="cat" value="2" id="cat-2" onclick="document.getElementById('extra-cat-4').style.display='none';"<?php if($c=="2") { ?> checked="checked"<?php } ?> /><label for="cat-2"><img src="../wp-content/plugins/announceme/help.png" width="15" height="15" /> Help</label><br />
     <input type="radio" name="cat" value="3" id="cat-3" onclick="document.getElementById('extra-cat-4').style.display='none';"<?php if($c=="3") { ?> checked="checked"<?php } ?> /><label for="cat-3"><img src="../wp-content/plugins/announceme/alert.png" width="15" height="15" /> Alert</label><br />
     <input type="radio" name="cat" value="4" id="cat-4" onclick="document.getElementById('extra-cat-4').style.display='block';"<?php if($c=="4") { ?> checked="checked"<?php } ?> /><label for="cat-4">Create own Category...</label><br />
     <div id="extra-cat-4"<?php if($c!="4") { ?> style="display: none;"<?php } ?>>
      <label for="c1">Announcement-Backgroundcolor: #</label><input type="text" name="c1" id="c1" style="width: 50px;" /><br />
      <label for="c2">Announcement-Bordercolor: #</label><input type="text" name="c2" id="c2" style="width: 50px;" /><br />
      <label for="c3">Announcement-Textcolor: #</label><input type="text" name="c3" id="c3" style="width: 50px;" /><br />
      <label for="img">Announcement-Image: </label><input type="text" name="image" id="img" style="width: 200px;" />
     </div>
    </div>
   </div>
   <div class="postbox" style="margin: 0;"> 
    <h3 class='hndle'><span>Publish</span></h3> 
    <div class="inside"> 
     <input type="radio" name="dsa" value="1" id="dsa-1"<?php if($d=="1") { ?> checked="checked"<?php } ?> /><label for="dsa-1">User can click "Don't show this again":</label><br />
   &nbsp;&nbsp;&nbsp;&nbsp;"Don't show this again"-Message: <input type="text" name="dsatxt" value="<?php echo $dt; ?>" /><br />
     <input type="radio" name="dsa" value="0" id="dsa-0"<?php if($d=="0") { ?> checked="checked"<?php } ?> /><label for="dsa-0">User can't click "Don't show this again"</label><br />
     <input type="submit" name="publish" class="button-primary" value="Publish" />
     <input type="submit" name="draft" class="button" value="Save as Draft" />
    </div>
   </div>
  </div>
 </form>
<?php echo $copyright; }

//Fontend
	//Head
	add_action('wp_head','announcemeHead');

	function announcemeHead() { global $wpdb;
		?><style> #announceme {position: fixed; z-index: 100; top: 0; left: 0; right: 0;} .admin-bar #announceme {top: 28px;} .announceme {float: left; padding: 4px 0; width: 100%;} .announceme-cat-1 {background: #55ff55; border-bottom: 1px solid #00d200;} .announceme-cat-2 {background: lightYellow; border-bottom: 1px solid #E6DB55;} .announceme-cat-3 {background: #c64a4a; border-bottom: 1px solid #960000;} .announceme-cat {float: left; margin-left: 15px;} .announceme-msg {float: left; padding: 9px; color: #000; text-shadow: 0px 1px 1px #fff; font: 13px Arial; font-weight: bold;} .announceme-close {float: right; padding: 10px 8px 4px 8px; margin-top: 2px; font: 16px Courier; font-weight: bold; color: #000; cursor: pointer; margin-right: 5px;} .announceme-close:hover {text-shadow: 0px 0px 3px #000;} .announceme-del {float: right; padding: 10px; color: #000; font: 10px Verdana; cursor: pointer;}</style>
<script>
if(typeof jQuery == 'undefined') { 
 document.write('<scr'+'ipt src="wp-content/plugins/announceme/jquery.js" type="text/JavaScript"></scr'+'ipt><sc'+'ript src="wp-content/plugins/announceme/announceme.js" type="text/JavaScript"></scr'+'ipt>'); 
} else { 
 document.write('<scr'+'ipt src="wp-content/plugins/announceme/announceme.js" type="text/JavaScript"></scr'+'ipt>'); 
}
</script>
<?php	}

	//HTML
	add_action('wp_footer','announcemeHtml');
	
	function announcemeHtml() { global $wpdb; ?><div id="announceme"><?php
		$q1 = mysql_query("SELECT * FROM ".$wpdb->prefix."announceme WHERE active = '1'");
		while($q2 = mysql_fetch_array($q1)) { if(!isset($_COOKIE['announceme-'.$q2['id']])) {
?>
<div id="announceme-<?php echo $q2['id']; ?>" class="announceme announceme-cat-<?php echo $q2['cat']; ?>" <?php if($q2['cat']=="4") { ?> style="background: #<?php echo $q2['c1']; ?>; border-bottom: 1px solid #<?php echo $q2['c2']; ?>; color: #<?php echo $q2['c3']; ?>;"<?php } ?>>
 <div class="announceme-cat"><img src="<?php echo $q2['img']; ?>" alt="" width="32" height="32" /></div>
 <div class="announceme-msg"><?php echo $q2['announce']; ?></div>
 <div class="announceme-close"<?php if($q2['cat']=="4") { ?> style="color: #<?php echo $q2['c3']; ?>;"<?php } ?>>X</div>
<?php if($q2['dsa']=="1") { ?>
 <div class="announceme-del"><input type="checkbox" /><?php if($q2['dsatxt']=="") { ?> Don't show this again<?php } else { echo " ".$q2['dsatxt']; } ?></div>
<?php } ?>
</div>
	<?php } } ?></div><?php } ?>
