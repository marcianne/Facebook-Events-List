<div class="single_event_container"><!-- div containing each single event -->	
<figure class="event_pic"><a href="<?php echo $event_url; ?>"> <!-- event pic container -->
<img src="<?php echo $pic_sm; ?>"/></a>
</figure> <!-- // event pic container -->

<h3 class="event_name"><a href="<?php echo $event_url; ?>"><?php echo($name); ?></a></h3>  <!-- event name with url link to fb page -->
<h4 class="event_start"><?php echo $start_date ?></h4>  <!-- event start date-->
<h4 class="event_times"><?php echo $event_times ?></h4>   <!-- event start times --> 
<div class="event_location"><?php echo $location; ?></div>  	 <!-- event location -->

<div class="event_desc">  <!-- event decsription -->
<?php echo $description_excerpt; ?>
</div> <!-- // div event decsription -->
</div> <!-- // div containing each single event -->