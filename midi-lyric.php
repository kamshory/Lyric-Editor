<?php
use Midi\MidiLyric;

require_once "inc/auth-with-login-form.php";

if(isset($song))
{
$midi = new MidiLyric();

$midi->importMid($song->getFilePathMidi());

$list = $midi->getLyric();

$lyricMidi = "A pa yang ter ja di
ke pa da &nbsp;";

?>

<div class="main-content"> <link rel="stylesheet" type="text/css" href="assets/css/midi-player.css" />

<h3 style="font-size: 18px; padding-bottom:2px;"><?php echo $song->getTitle();?></h3>

<script type="text/javascript">
	var midi_data = <?php
	echo json_encode($midi->getMidData(), JSON_PRETTY_PRINT);
	?>;
</script>
<script type="text/javascript" src="assets/js/lyric-editor.js"></script>
<script type="text/javascript" src="assets/midijs/midi.js"></script>


<div class="modal" tabindex="-1" role="dialog" id="generate-dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Generate Lyric From Vocal</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<div class="ajax-content-loader-case-option">
			<table class="from-table-two-cols" width="100%" cellspacing="0" cellpadding="0">
			<tbody>
			<tr>
			<td>Select Channel</td>
			<td><select name="channel" class="form-control">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
			</select></td>
			</tr>
			</tbody>
			</table>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-primary" id="save-genre" onclick="generateLyricFromVocal(); ">Generate</button>
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
		  </div>
		</div>
	  </div>
	</div>
	
	<style type="text/css">
	<?php
	?>
	</style>
	<script type="text/javascript">
	
	</script>
	<style type="text/css">
	.flex-row {
	  display: flex;  
	}
	.flex-column {
	  flex: 50%;
	  max-height:calc(100vh - 345px);
	  overflow:auto;
	}
	.flex-row .flex-column:not(:first-child) {
	  margin-left: 10px;                       
	}
	.flex-row .flex-column:not(:last-child) {
	  margin-right: 10px;                      
	}
	.ta-lyric-editor{
		border:1px solid #CCCCCC;
		padding:5px;
		width:100%;
		height:50px;
	}
	.flex-column table td{
		position:relative;
	}
	.hilight-green{
		background-color:#ffbebb;
	}
	.hilight{
		background-color:yellow;
	}
	
	.mp-wrapper {
	  display: flex;
	  margin-bottom:20px;
	}
	.mp-wrapper .mp-div  {
	}
	.mp-wrapper .mp-div{
		width:25%;
	}
	.mp-wrapper .mp-div:not(:first-child) {
	  margin-left: 5px;                       
	}
	.mp-wrapper .mp-div:not(:last-child) {
	  margin-right: 5px;                      
	}
	.midi-display{
		width:400px;
	}
	.midi-channel{
		border:1px solid #CCCCCC;
		height:50px;
		display:inline-block;
		width:20px;
		box-sizing:border-box;
		position:relative;
	}
	.midi-channel:after{
		content:attr(data-channel);
		position:absolute;
		bottom:-14px;
		font-size:10px;
		width:100%;
		text-align:center;
	}
	.midi-channel:before{
		content:attr(data-note);
		position:absolute;
		top:-14px;
		font-size:9px;
		width:100%;
		text-align:center;
	}
	.midi-channel > div{
		background-color:#880000;
		position:absolute;
		bottom:0;
		width:100%;
		transition:height 0.015s;
	}
	textarea.rawdata{
		width: 100%;
		box-sizing: border-box;
		height: 200px;
		resize: vertical;
		border: 1px solid #CCCCCC;
		padding: 10px;
	}
	.button-area{
		padding: 5px 0;
	}
	[data-channel="1"] > div{
	}
	</style>
	
	<div class="planet-midi-player" data-is-stoped="true" data-midi-url="files/<?php echo basename($song->getFilePathMidi());?>">
		<div class="mp-wrapper">
			<div class="mp-div waveform">
				<canvas id="canvas" style="width:256px; height:64px" width="256" height="64"></canvas>
			</div>
			<div class="mp-div midi-indicator">
				<div class="midi-display">
					<div class="midi-channel" data-channel="1"><div></div></div>
					<div class="midi-channel" data-channel="2"><div></div></div>
					<div class="midi-channel" data-channel="3"><div></div></div>
					<div class="midi-channel" data-channel="4"><div></div></div>
					<div class="midi-channel" data-channel="5"><div></div></div>
					<div class="midi-channel" data-channel="6"><div></div></div>
					<div class="midi-channel" data-channel="7"><div></div></div>
					<div class="midi-channel" data-channel="8"><div></div></div>
					<div class="midi-channel" data-channel="9"><div></div></div>
					<div class="midi-channel" data-channel="10"><div></div></div>
					<div class="midi-channel" data-channel="11"><div></div></div>
					<div class="midi-channel" data-channel="12"><div></div></div>
					<div class="midi-channel" data-channel="13"><div></div></div>
					<div class="midi-channel" data-channel="14"><div></div></div>
					<div class="midi-channel" data-channel="15"><div></div></div>
					<div class="midi-channel" data-channel="16"><div></div></div>
				</div>
			</div>

			<div class="mp-div mp-control">
				<div class="mp-control-1">
					<button class="mp-prev"><i class="fa fa-backward" aria-hidden="true"></i></button>
					<button class="mp-play"><i class="fa fa-pause" aria-hidden="true"></i><i class="fa fa-play" aria-hidden="true"></i></button>
					<button class="mp-stop"><i class="fa fa-stop" aria-hidden="true"></i></button>
					<button class="mp-next"><i class="fa fa-forward" aria-hidden="true"></i></button>
				</div>	
			</div>
			
			<div class="mp-div mp-progress">
				<div class="mp-timer">
					<div class="mp-duration">
						
					</div>
					<div class="mp-elapsed">
						
					</div>
				</div>
				<div class="mp-progress-bar">
					<div class="mp-progress-bar-container">
						<div class="mp-progress-bar-inner">
						</div>
					</div>
				</div>
				<div class="mp-status-bar">
				</div>
			</div>
		</div>	
	</div>
	
	<div class="flex-row">
	<div class="flex-column lyric-preview-container">
		<div class="raw-area">
			<div><textarea name="rawdata" class="rawdata"><?php echo htmlspecialchars($lyricMidi);?></textarea>
			</div>
			<div class="button-area">
			<input type="button" id="generate" value="Generate" class="btn btn-primary">
			<input type="button" id="replace-lyric" value="Replace Lyric" class="btn btn-success">
			</div>
		</div>
		<div class="lyric-preview"></div>
	</div>
	<div class="flex-column lyric-editor">
	<table class="table" width="100%" border="0">
		<thead>
			<tr>
				<td width="50">Track</td>
				<td width="50">Event</td>
				<td width="80">R Time</td>
				<td width="150">A Time</td>
				<td>Text</td>
			</tr>
		</thead>
		<tbody>	
		</tbody>
	</table>
	</div>
	
	</div>
	<?php


}
?>