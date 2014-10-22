<?php

/*
Change the content of function "showMemUsage"
*/

class tools_monitor {

	function showMemUsage () {
		global $app;

		/* fetch the Data from the DB */
		$record = $app->db->queryOneRecord("SELECT data, state FROM monitor_data WHERE type = 'mem_usage' and server_id = " . $_SESSION['monitor']['server_id'] . " order by created desc");

		if(isset($record['data'])) {
			$data = unserialize($record['data']);

			/*
            Format the data
            */
			$html = <<< EOF

	<script>
		$(function() {
			$(".meter > span").each(function() {
				$(this)
					.data("origWidth", $(this).width())
					.width(0)
					.animate({
						width: $(this).data("origWidth")
					}, 1200);
			});
		});
	</script>
	
	<style>
		.meter { 
			height: 20px;
			position: relative;
			margin: 5px 0 5px 0;
			background: #555;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px;
			padding: 10px;
			-webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
			-moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
			box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
		}
		.meter > span {
			display: block;
			height: 15px;
			   -webkit-border-top-right-radius: 5px;
			-webkit-border-bottom-right-radius: 5px;
			       -moz-border-radius-topright: 5px;
			    -moz-border-radius-bottomright: 5px;
			           border-top-right-radius: 5px;
			        border-bottom-right-radius: 5px;
			    -webkit-border-top-left-radius: 5px;
			 -webkit-border-bottom-left-radius: 5px;
			        -moz-border-radius-topleft: 5px;
			     -moz-border-radius-bottomleft: 5px;
			            border-top-left-radius: 5px;
			         border-bottom-left-radius: 5px;
			background-color: rgb(43,194,83);
			background-image: -webkit-gradient(
			  linear,
			  left bottom,
			  left top,
			  color-stop(0, rgb(43,194,83)),
			  color-stop(1, rgb(84,240,84))
			 );
			background-image: -moz-linear-gradient(
			  center bottom,
			  rgb(43,194,83) 37%,
			  rgb(84,240,84) 69%
			 );
			-webkit-box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			-moz-box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			box-shadow: 
			  inset 0 2px 9px  rgba(255,255,255,0.3),
			  inset 0 -2px 6px rgba(0,0,0,0.4);
			position: relative;
			overflow: hidden;
			top: -8px;
			left: -5px;
		}
		
		.orange > span {
			background-color: #f1a165;
			background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
			background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, #f36d0a));
			background-image: -webkit-linear-gradient(#f1a165, #f36d0a); 
		}
		
		.red > span {
			background-color: #f0a3a3;
			background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
			background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, #f42323));
			background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
		}
		
		.nostripes > span > span, .nostripes > span:after {
			-webkit-animation: none;
			background-image: none;
		}
	</style>
EOF;
    
			$suffixes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
			$precision = 4;
			$pagesize = 1024;
			$html .= '<div class="systemmonitor-state state-'.$record['state'].'">';
			$html .= '<div class="systemmonitor-content icons32 ico-'.$record['state'].'">';
			$html .= '<table style="width: 100%;">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th style="width: 100px;">#</th>';
			$html .= '<th style="width: 110px;">Values</th>';
			$html .= '<th></th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tfoot></tfoot>';
			$html .= '<tbody>';
			$trc = count($data);
			$tr_last=false;
			/*
		missing burst (/proc/user_beancounters -- "privvmpages")
		if (/privvmpages\s+(\d+)\s+(\d+)\s+(\d+)/ && $3 < 1024*1024*1024*1024) {
			$memburst = $3 * $pagesize / 1024;
			last;
		} 
		$m{'cached'} > $memtotal -|- MemFree
			*/
			if(isset($data['MemTotal']) && isset($data['MemFree']) && isset($data['Buffers']) && isset($data['Cached']) && isset($data['SwapTotal']) && isset($data['SwapFree'])){
				//* This is the closest i can get to what webmin reports, also based on the calculations webmin does
				$baseMemTotal = log($data['MemTotal']) / log($pagesize);
				$convMemTotal = round(pow($pagesize, $baseMemTotal - floor($baseMemTotal)), $precision) . " " . $suffixes[floor($baseMemTotal)];
				
				$baseMemFree = log($data['MemFree']+$data['Buffers']+$data['Cached']) / log($pagesize);
				$convMemFree = round(pow($pagesize, $baseMemFree - floor($baseMemFree)), $precision) . " " . $suffixes[floor($baseMemFree)];
				
				$baseMemCached = log($data['Buffers']+$data['Cached']) / log($pagesize);
				$convMemCached = round(pow($pagesize, $baseMemCached - floor($baseMemCached)), $precision) . " " . $suffixes[floor($baseMemCached)];
				
				$baseSwapTotal = log($data['SwapTotal']) / log($pagesize);
				$convSwapTotal = round(pow($pagesize, $baseSwapTotal - floor($baseSwapTotal)), $precision) . " " . $suffixes[floor($baseSwapTotal)];
				
				$baseSwapFree = log($data['SwapFree']) / log($pagesize);
				$convSwapFree = round(pow($pagesize, $baseSwapFree - floor($baseSwapFree)), $precision) . " " . $suffixes[floor($baseSwapFree)];
					
				$formmatedView = "";
				
				/*
				some fancy displays..!
				MemUsed: MemTotal-(MemFree+Buffers+Cached) -/- MemTotal
				*/
				$MemUsed = ($data['MemTotal'] - ($data['MemFree']+$data['Buffers']+$data['Cached'])) ;
				$baseMemUsed = log($MemUsed) / log($pagesize);
				$convMemUsed = round(pow($pagesize, $baseMemUsed - floor($baseMemUsed)), $precision) . " " . $suffixes[floor($baseMemUsed)];
				$percentage = ( ($MemUsed / $data['MemTotal'] ) * 100 );
				if($percentage<55)
					$color = "green";
				else if($percentage<85)
					$color = "orange";
				else
					$color = "red";
				$formmatedView .= "
				<strong>Memory</strong>: {$convMemTotal} total / {$convMemFree} free / {$convMemCached} cached<br>
				<div class='meter {$color} nostripes'>
					<div style='margin: 0px 0px 0px 40%; position: absolute; top: 4px; z-index: 5; color: rgb(255, 255, 255);'> {$convMemUsed} / {$convMemTotal}</div>
					<span style='width: {$percentage}%;'></span>
				</div>";
				//* like "free -m"
				/*
				$baseMemFree = log($data['MemFree']) / log($pagesize);
				$convMemFree = round(pow($pagesize, $baseMemFree - floor($baseMemFree)), $precision) . " " . $suffixes[floor($baseMemFree)];
				$percentage = ( ( ($data['MemTotal'] - $data['MemFree']) / $data['MemTotal'] ) * 100 );
				if($percentage<55)
					$color = "green";
				else if($percentage<85)
					$color = "orange";
				else
					$color = "red";
				$formmatedView .= "
				<div class='meter {$color} nostripes'>
					<div style='margin: 0px 0px 0px 40%; position: absolute; top: 4px; z-index: 5; color: rgb(255, 255, 255);'>{$convMemFree} / {$convMemTotal}</div>
					<span style='width: {$percentage}%;'></span>
				</div>";
				unset($convMemTotal, $percentage, $convMemFree, $baseMemFree, $color);
				*/
				//* SwapUsed: SwapTotal-SwapFree -/- SwapTotal
				
				$baseSwapUsed = log($data['SwapTotal']-$data['SwapFree']) / log($pagesize);
				$convSwapUsed = round(pow($pagesize, $baseSwapUsed - floor($baseSwapUsed)), $precision) . " " . $suffixes[floor($baseSwapUsed)];
				$percentage = ( ( ($data['SwapTotal']-$data['SwapFree']) / $data['SwapTotal'] ) * 100 );
				if($percentage<55)
					$color = "green";
				else if($percentage<85)
					$color = "orange";
				else
					$color = "red";
				$formmatedView .= "
				<strong>Swap space</strong>: {$convSwapTotal} total / {$convSwapFree} free<br>
				<div class='meter {$color} nostripes'>
					<div style='margin: 0px 0px 0px 40%; position: absolute; top: 4px; z-index: 5; color: rgb(255, 255, 255);'> {$convSwapUsed} / {$convSwapTotal}</div>
					<span style='width: {$percentage}%;'></span>
				</div>";
				unset($MemUsed, $convMemUsed, $convSwapTotal, $baseSwapUsed, $convSwapUsed, $percentage, $convMemFree, $baseMemTotal, $baseMemFree, $baseMemCached, $convMemCached, $baseSwapTotal, $baseSwapFree, $convSwapFree);
				
			}
			
			foreach($data as $key => $value) {
				if ($key != '') {
					$html .= "<tr><td>{$key}:</td>";
					$base = log($value) / log($pagesize);
					$convSize = round(pow($pagesize, $base - floor($base)), $precision) . " " . $suffixes[floor($base)];
					$html .= "<td>{$value} B<br>{$convSize}</td>";
					if(!$tr_last){
						$tr_last=true;
						$html .= "<td style='max-width: 700px; vertical-align: top;' rowspan={$trc}>#formmatedView#</td>";
					}
					$html .= "</tr>";
				}
			}
			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</div></div>';
			$html = str_replace("#formmatedView#", $formmatedView, $html);
		} else {
			$html = '<p>'.$app->lng("no_data_memusage_txt").'</p>';
		}

		return $html;
	}

}

?>
