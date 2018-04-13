<?php

function dfga_getaverage_time_onsite( ) {
  
	$from 		= '30daysAgo';
	$to 		= 'today';
	$metrics 	= 'ga:avgSessionDuration';
	$options 	= array();
	$results = dfga_getResults( $from, $to, $metrics, $options );

	if (count($results->getRows()) > 0) {

		// Get the profile name.
		$profileName = $results->getProfileInfo()->getProfileName();

		// Get the entry for the first entry in the first row.
		$return['status'] 	= 1;
		$rows 				= $results->getRows();
		$data = $rows[0][0];
		$max_value = 50;
		$pending = ( $max_value < $data )? ($max_value - $data ) : 1;
		$return['rows'] 	= $rows;
		$return['chart']	= '<canvas id="averageTimeOnSite" width="400" height="400"></canvas>';
		
		$return['chart']	.= '<script type="text/javascript">
			$("document").ready(function(){
				var data = {
					labels: [
						"Average Time Onite",""
					],
					datasets: [
						{
							data: ['.$data.','.$pending.'],
							backgroundColor: [
								"#FF6384",
								"#FF0000"
							],
							hoverBackgroundColor: [
								"#FF6384",
								"#FF0000"
							]
						}]
				};

				var ctx = $("#averageTimeOnSite");
				var myDoughnutChart = new Chart(ctx, {
					type: \'doughnut\',
					data: data,
					options: {
						rotation: 1 * Math.PI,
						circumference: 1 * Math.PI
					}
				});
			});
		</script>
		';
		
	} else {
		$return['status'] 	= 0;
	}
	
	return $return;
}