<div id="container" class="resultContainer">
<a href="<?php echo site_url(); ?>" style="margin-left:10px; font-size:12pt;">Home</a>
<h1>List of Temporary deleted files: <?php echo count($results); ?></h1>		<?php // count the number of temporary delete files ?>
<div class="options_div">
<form id="submit_options" method="POST" action="/filelist/main/check">

<div class="left_div">
<a href="/filelist/main/view/<?php echo $id; ?>">< Back</a> &nbsp; | &nbsp; Scanned Date: <?php if(count($results) > 0) { echo $results[0]->dateadded; } ?>	<?php // if results is greater than 0 then display date added in temporary deleted file ?>
</div>
<div class="right_div">
	<input type="submit" class="button-link confirm_delete" id="submit_option" name="delete_forever" value="DELETE" onclick="delButton();" />		<?php // delete option button to temporary delete the file(s) ?>
	<input type="submit" class="button-link" id="submit_option" name="restore_option" value="RESTORE" />	<?php // restore option button to temporary restore the temporary deleted file(s) ?>
</div>

</div>
<div id="selectDiv" class="resultDiv">
<table id="tbl_results">
<tr>
	<th class="fileName">Filename</th>
	<th class="fileSize">File size</th>
	<th class="dateMod">Date Modified</th>
	<th class="thOption">&nbsp;</th>
</tr>
<?php
$counter = 0;		// set counter to 0
foreach($results as $rst) {		// extract the results array
	$style = ($counter %2 == 0) ? '' : 'darkStyle';		// if counter is divisible by 0 then style = empty style : darkstyle class
?>
<tr class="trTempView">
<td class="rstFile trTempView <?php echo $style; ?>"><?php echo $rst->file_path; ?></td>	<?php // display the file path ?>
<td class="rstSize trTempView <?php echo $style; ?>"><?php echo sizeFilter($rst->file_size); ?></td>	<?php // display the file size ?>
<td class="rstDate trTempView <?php echo $style; ?>"><?php echo $rst->date_modified; ?></td>		<?php // display date modified ?>
<td class="rstCheck trTempView <?php echo $style; ?>"><input type="checkbox" class="items" id="item" value="<?php echo $rst->id; ?>" name="items[]"></td>		<?php // checkbox item and get the id ?>
</tr>
<?php
	$counter++;	// increment counter
}
# function sizeFilter
function sizeFilter( $bytes ) {
    $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );	# store the file array
    for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );		# do the calculations by file size
    return( round( $bytes, 2 ) . " " . $label[$i] );	# and return the result with label
}
?>
</table>
</div>
</form>
<div class="footerCopy" style="padding-left:10px;">Clone File Eliminator &copy; <?php echo date("Y"); ?> </div>
</div>
<script type="text/javascript">
var counterCheck = 0;							// set counterCheck to 0
var strMsg = "";								// set string Message to empty string
$(document).ready(function() {					// load jquery document or window load
	$('#submit_options').submit(function(e) {	// if submit form options
		$('.items').each(function() {			// get all the checkbox items
			if($(this).is(':checked')) {		// On each items that is checked		
				counterCheck++;					// increment the counterCheck
			}			
		});			
		if( $('.confirm_delete').hasClass('setDel') )							// if that delete button has a class setDel
			strMsg = "Are you sure you want to permanently delete the file(s)?";	// then set this message
		else																	// if class setDel is not added
			strMsg = "Are you sure you want to restore the file(s)?";			// then set this message 
		if(counterCheck > 0) {													// if counterCheck is greater than 0
			var r=confirm( strMsg );											// then assign strMsg confirm
			if (r==true) {														// if it is true or Yes
			  // redirect to where???											// then redirect the form
			}
			else {
			  e.preventDefault();												// If it is No then prevent the form from submitting
			}
		}
	});	
});
function delButton() {		// if it is delete permanent button option
	$('.confirm_delete').addClass('setDel');	// then add class on it to avoid conflict with restore button message.
}
</script>