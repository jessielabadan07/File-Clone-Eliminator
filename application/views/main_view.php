<div id="container" class="main_container" style="padding:10px;">
<a href="<?php echo site_url(); ?>" style="margin-left:10px; font-size:12pt;">Home</a>
<!--<input type="button" class="button-link" id="close_app" name="close_app" value="Close Application" style="float:right;" onclick="return whereAreYouGoing()">-->
<h1>CLONE FILE ELIMINATOR</h1>
<div id="selectDiv">
	<form id="file_handler" action="" method="post">
		<div class="textDiv">
			<input type="text" name="location" id="location" placeholder="Enter drive or directory folder ( e.g. C, D, C:/user, F:/folder1/folder2 )." />
		</div>
		<div class="driveOptions">
			<select id="drive_options" name="drive_options">
				<option>Select drive</option>
				<?php # get all available drives ?>
				<?php foreach($drives as $drive): ?>	<?php #loop through available drives ?>
						<option value="<?php echo $drive; ?>:/"><?php echo $drive; ?></option>	<?php # get drive value and name ?>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="fileOPtions">
			<select id="file_options" name="file_options">		<?php // get file types ?>
				<option value="1">All File Types</option>					
				<option value="2">Music</option>
				<option value="3">Video</option>
				<option value="4">Docs</option>
				<option value="5">Xls</option>
				<option value="6">Pdf</option>
				<option value="7">Img</option>
				<?php foreach($extensions as $extension): ?>
					<option value="<?php echo $extension->extension_name; ?>"><?php echo $extension->extension_name; ?></option>	
				<?php endforeach; ?>
			</select>
			<div class="newType"><a href="#" id="addType">Add File Type</a></div>
		</div>
		<div class="upload">			
			<input type="submit" value="Submit" class="btn btn-default">
		</div>
		<?php
			if(isset($_GET['error']) && $_GET['error'] == true) {								# If error is set and true
				$msg = "";																		# set message to empty
				if( strlen($_GET['message']) > 0) { 											# if the length of the error message is greater than 0
					$msg = ': '.urldecode($_GET['message']); 									# display the message in html format
				}
				echo '<div class="error2">The system cannot find the path specified<br/>'.$msg;	# display the msg error
				echo '</div>';
			}
		?>
		<div class="error">Please enter a drive or directory folder..</div>		<?php //It will display the error if the form submit and no entry in the box field. ?>
	</form>	
	<div class="dirTree"></div>		<?php //This is where to display the folder treeview ?>
</div>
<div class="footerCopy">Clone File Eliminator &copy; <?php echo date("Y"); ?> </div>
</div>
<div class="messagepop pop">
    <form method="post" id="new_message" action="">
        <p><label for="email">Enter new file type extension name:</label><input type="text" size="30" name="extName" id="extName" /> <br/><span class="errExt">Please enter extension file type.</span></p>
		<p><input type="submit" value="Add" name="commit" id="message_submit"/> or <a class="close" href="<?php echo base_url();?>">Cancel</a></p>
    </form>
</div>
<script type="text/javascript">
$(document).ready(function() {							// load jquery document
	$( "#drive_options" ).change(function(e) {			// If select drive has changed
		$('#location').val( $(this).val() );			// then get the value of the drive selected option ex..c:/,d:/,etc...					
		$('.dirTree').fileTree({root: $(this).val(), script: 'connectors/jqueryFileTree.php'}, function(file) {			// display the treeview folders
			// more codes here if return success....
		});
	});
	$('#file_handler').submit(function(e) {		// if sumbit form 
		if($('#location').val().length < 1) {	// get the length value of the box, and if it is less than 1 or empty
			$('.error').show();					// then show the erros
			//e.preventDefault();					// and prevent the form from submitting.
		}
	});
	
	$('#message_submit').click(function(e) {			// If submit the form
		//e.preventDefault();								// stop the action
		if($('#extName').val().length == 0 || $.trim($('#extName').val()) == "") {		// if extension name is empty
			$('.errExt').show();														// show error message
		} else {																		// else save to database ajax
			$.ajax({										// call ajax function
				type: "POST",								
				data: { fileType : $('#extName').val() },	// get the value
				url: 'connectors/newtype.php',				// call the file
				success: function(data) {
					location.reload();						// then reload the page
				}
			});	
		}
	});

	/*$('#close_app').click(function() {		
		var r = confirm("Are you sure you want to close the applicat?");
		if(r) {
			$.ajax({										// call ajax function
				type: "POST",								
				data: { emptyTable : 'true' },	// get the value
				url: 'connectors/emptytable.php',				// call the file
				success: function(data) {
					window.close();				
					//location.reload();						// then reload the page
				}
			});
		} else {
			location.reload();	
		}
	});  */
	
	$("#addType").on('click', function() {				// if add type of new extension file
		$(".pop").slideFadeToggle(function() { 
			$("#extName").focus();
		});
        return false;
    });

    $(".close").on('click', function() {				// cancel button or close
        deselect();										// deselect window
        return false;
    });
	
});

$.fn.slideFadeToggle = function(easing, callback) {				// call function slideFadeToggle to display the popup
    return this.animate({ opacity: 'toggle', height: 'toggle' }, "fast", easing, callback);
};
</script>
