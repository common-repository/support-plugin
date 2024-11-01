<style>

div.ticket {
	padding:15px;
	-moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -khtml-border-radius: 10px;
    border-radius: 10px;
    background-color: #E8E8E8;
    border:1px solid #ccc;
	margin-top:20px;
	padding-bottom: 30px;
	display: table;
	width:500px;
	max-width:500px;
}

div.ticket table{
	padding-bottom: 10px;
	width:500px;
}

div.ticket table td{

	font-size: 15px;
	line-height: 27px;
	max-width: 150px;
}

div.ticket table td:first-child{

	width: 80px;
}

pre {
	font-family: Helvetica;
	color: #666;
	width: 400px;
	white-space: pre-wrap;       /* css-3 */
 	white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
 	white-space: -pre-wrap;      /* Opera 4-6 */
 	white-space: -o-pre-wrap;    /* Opera 7 */
	word-wrap: break-word;   
}

</style>
<?php

// Option page


include("xmlrpc/lib/xmlrpc.inc");
// Credentials
$username = get_option('username');
$apikey = get_option('apikey');
$c = new xmlrpc_client(get_option('server_url'));

if($_GET['id'] == "")	
{
// Client
			
			
		if($_POST['oscimp_hidden'] == 'Y') {
			//Form data sent
			$subject = $_POST['subject'];
			$message = $_POST['message'];
			$priority = $_POST['priority'];
			$date = $_POST['date'];
			
			if($subject!="" && $message != "")
			{
			$val = $subject."/".$priority."/".$date;

			// Method
			$m = new xmlrpcmsg('examples.write_ticket');
			
			// Params
			$m->addParam(new xmlrpcval($username,"string"));
			$m->addParam(new xmlrpcval(serialize(sha1($apikey)),"string"));
			$m->addParam(new xmlrpcval($val,"string"));
			$m->addParam(new xmlrpcval($message,"string"));
			// Send request to server
			$r =& $c->send($m);
	
			?>
			<div class="updated"><p><strong><?php _e('Uw ticket is aangevraagd.' ); ?></strong></p></div>
			<?php
			}	
		} 


			
	// Method
	$m = new xmlrpcmsg('examples.tickets');
	
	// Params
	$m->addParam(new xmlrpcval($username,"string"));
	$m->addParam(new xmlrpcval(serialize(sha1($apikey)),"string"));
	
	// Send request to server
	$r =& $c->send($m);
	
	


?>

<div class="wrap">  
    <?php    echo "<h2>" . __( 'Welcome on the support page', 'cedricve_trdom' ) . "</h2>"; ?>  
    
    
<?php

	if($r->faultCode())
	{

		display_error($r);

	}
	else
	{
	
?>

  	<p style='text-align:justify;width:500px;'>
  		<pre><?php $support_text = get_option('support_text'); if($support_text == "")
				echo "If you have a question or problem, please submit a ticket. Our support team will take care of your ticket and provide a solution as fast as possible.";
				else echo $support_text; ?></pre>
  	</p>
    <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <input type="hidden" name="oscimp_hidden" value="Y">  
        <table>
        <tr><td><?php _e("Subject: " ); ?></td><td><input type="text" name="subject" value="" size="20"></td></tr>  
        <tr>
        	<td><?php _e("Priority: " ); ?></td>
        	<td>
        		<select name="priority">
        			<option value="1">Low</option>
        			<option value="2">Medium</option>
        			<option value="3">High</option>
        			<option value="4">Emergency</option>
        		</select>
        	</td>
        </tr>  
  		<tr><td style='vertical-align:top; margin-top:0; '><?php _e("Message: " ); ?></td><td><textarea rows='10' cols='50' name="message"></textarea></td></tr>  
        <tr><td class="submit" colspan="2">  
        <input type="submit" name="Submit" value="<?php _e('Confirm ticket', 'cedricve_trdom' ) ?>" />  
        </td></tr> 
        </table>
        <input type='hidden' value='<?=time();?>' name='date'/>
    </form>  


<h2>Tickets</h2>

<?php		 
		 $xmlstring = (htmlentities($r->serialize()));
		
		 $v=$r->value(); 
		 $max=$v->arraysize();  
		 if($max == 0)
		 	echo "No tickets";
		 else
		 {
		?>
		<table class="widefat fixed comments">
		<thead>
		<th width="80px">Actions</th><th>Date</th><th>Priority</th><th>Status</th><th>Subject</th><th># Messages</th>
		</thead>
		<tbody>
		<?php
		 
		 for($i=0; $i<$max; $i++) {    
		 	$rec=$v->arraymem($i);
		 	$id=getValue("id",$rec);
		 	$date=getValue("date",$rec);
		 	$subject=getValue("subject",$rec);
		 	$message=getValue("message",$rec);
		 	$numrows=getValue("numrows",$rec);
		 	$priority=getValue("priority",$rec);
		 	
		 	$status=getValue("status",$rec);
		 	
		 	switch($status)
		 	{
		 		case 0:$status = "<span style='color:#F0001C'>Closed</span>";break;
		 		case 1:$status = "<span style='color:#009105'>Open</span>";break;
		 		case "-1": $status = "<span style='color:#0084F0'>Solved</span>";break;
			}
					
		 		
		 	switch($priority)
		 	{
		 		case "1": $priority = "<span style='color:#0084F0'>Low</span>"; break;
		 		case "2": $priority = "<span style='color:#009105'>Medium</span>"; break;
		 		case "3": $priority = "<span style='color:#F08000'>High</span>"; break;
		 		case "4": $priority = "<span style='color:#F0001C'>Emergency</span>"; break;
		 	}
		 	
		 	echo "<tr><td><a href='admin.php?page=ticket_support&id=$id'><img src='".WP_PLUGIN_URL."/support-plugin/view.png'/></a></td><td>" . date("d F Y (H:i)",$date) . "</td><td>" . $priority . "</td><td>" . $status . "</td><td><a href='admin.php?page=ticket_support&id=$id'>". $subject . "</a></td><td>" . $numrows . "</td><tr>";
		 } 
		 
		 
		 ?>
		 
		</tbody>
		</table>
		 
		 <?php
		}
	}

?>

</div>  

<?php

}
else {

		if($_POST['oscimp_hidden'] == 'Y') {
			
			//Form data sent
			$message = $_POST['message'];
			$val = $_POST['date']."/".$_GET['id'];

			// Method
			$m = new xmlrpcmsg('examples.write_message');
			
			// Params
			$m->addParam(new xmlrpcval($username,"string"));
			$m->addParam(new xmlrpcval(serialize(sha1($apikey)),"string"));
			$m->addParam(new xmlrpcval($val,"string"));
			$m->addParam(new xmlrpcval($message,"string"));
			// Send request to server
			$r =& $c->send($m);
	
			?>
			<div class="updated"><p><strong><?php _e('Uw bericht is verstuurd.' ); ?></strong></p></div>
			<?php
		} 
		
	echo '<div class="wrap">';
	echo "<h2><a style='text-decoration:none;' href='admin.php?page=ticket_support'>Support</a> «« Ticket ($_GET[id])</h2>";
	
		// Method
	$m = new xmlrpcmsg('examples.get_ticket');
	
	// Params
	$m->addParam(new xmlrpcval($username,"string"));
	$m->addParam(new xmlrpcval(serialize(sha1($apikey)),"string"));
	$m->addParam(new xmlrpcval($_GET['id'],"string"));
	
	// Send request to server
	$r =& $c->send($m);

	if($r->faultCode())
	{

		display_error($r);

	}
	else
	{	
		$xmlstring = (htmlentities($r->serialize()));
		
		 $v=$r->value(); 
		 $max=$v->arraysize(); 
		 if($max == 0)
		 	echo "No ticket found with this id ($_GET[id])";
		 else
		 {
		 
		 for($i=0; $i<$max; $i++) {    
		 	$rec=$v->arraymem($i);
		 	$date=getValue("date",$rec);
		 	$writer=getValue("writer",$rec);
		 	$message=getValue("message",$rec);
		 	$subject=getValue("subject",$rec);
		 	$priority=getValue("priority",$rec);
		 	
		 	if($writer == "client")
		 		$writer = "You";
		 	else
		 		$writer = "<span style='color:#DE961B;'>Admin</span>";
		 	
		 	$status=getValue("status",$rec);

		 	if($i==0)
		 	$cur_status = $status;
		 	
		 	switch($status)
		 	{
		 		case 0:$status = "<span style='color:#F0001C'>Closed</span>";break;
		 		case 1:$status = "<span style='color:#009105'>Open</span>";break;
		 		case "-1": $status = "<span style='color:#0084F0'>Solved</span>";break;
			}
					
		 	switch($priority)
		 	{
		 		case "1": $priority = "<span style='color:#0084F0'>Low</span>"; break;
		 		case "2": $priority = "<span style='color:#009105'>Medium</span>"; break;
		 		case "3": $priority = "<span style='color:#F08000'>High</span>"; break;
		 		case "4": $priority = "<span style='color:#F0001C'>Emergency</span>"; break;
		 	}
		 	
		 	if($i%2==0)
		 		$bg_color = "#E8E8E8";
		 	else
		 		$bg_color = "#FFF";
		 		
		 	
		 	if($i==0)
		 	{
		 	echo "<div class='ticket'>
		 	<h2>".date("d F Y (H:i)",$date)."</h2>
		 		<table style='padding-top:30px;'>
		 			<tr><td>Subject:</td><td><span style='color:#666'>$subject</span></td></tr>
		 			<tr><td>Status:</td><td>$status</td></tr>
		 			<tr><td>Priority:</td><td>$priority</td></tr>
		 			<tr><td>&nbsp;</td></tr>
		 			<tr><td style='vertical-align:top; margin-top:0; '>Message:</td><td><pre>$message</pre></td></tr>
		 		</table>
		 	</div>
		 	";
	 	   echo "<h2 style='margin-top:20px;'>" . __( 'Comments', 'cedricve_trdom' ) . "</h2>"; 
	 	   if($max == 1)
		 	echo "Geen reacties";
		 	}
		 	else {
		 	
		 	echo "<div class='ticket' style='background-color:$bg_color'>
		 	<h2>$writer wrote at <br><span style='font-size:16px'>".date("d F Y (H:i)",$date)."</span></h2>
		 		<table style='padding-top:30px;'>
		 			<tr><td><pre>$message</pre></td></tr>
		 		</table>
		 	</div>
		 	";
		 	}
		 } 
		 
		 
	     }
	     
	}	
	
	if($cur_status != "-1")
	{

   echo "<h2 style='margin-top:20px;'>" . __( 'Answer', 'cedricve_trdom' ) . "</h2>"; ?>  
   <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <input type="hidden" name="oscimp_hidden" value="Y">  
        <table>
  		<tr><td><textarea rows='10' cols='50' name="message"></textarea></td></tr>  
        <tr><td class="submit" colspan="2">  
        <input type="submit" name="Submit" value="<?php _e('Write message', 'cedricve_trdom' ) ?>" />  
        </td></tr> 
        </table>
        <input type='hidden' value='<?=time();?>' name='date'/>
    </form>  
    
    <?php
    
    }
echo '</div>';
}

?>