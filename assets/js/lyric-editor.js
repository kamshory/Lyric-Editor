Object.size = function(obj) {
var size = 0, key;
for (key in obj) {
	if (obj.hasOwnProperty(key)) size++;
}
return size;
};
String.prototype.explode = function (separator, limit)
{
	var array = this.split(separator);
	if (limit !== undefined && array.length > limit && limit > 1)
	{
		var array3 = [];
		var i;
		var array2 = this.split(separator, limit - 1);
		if(array.length > limit)
		{
			for(i in array)
			{
				if(i >= limit-1)
				{
					array3.push(array[i]);
				}				
			}
		}
		array2.push(array3.join(separator));
		return array2;
	}
	return array;
};
$(document).ready(function(){
	
	$(document).on('change', '.table-check-controller', function(e){
		var checked = $(this)[0].checked;
		$('.table-check-target').each(function(e2){
			$(this)[0].checked = checked;
		});
	});
	$(document).on('change', '.filter-bar select', function(e){
		$(this).closest('form').submit();
	});
	
	$(document).on('click', '.mp-play', function(){
		if($('.planet-midi-player').attr('data-is-stoped') == 'true')
		{
			var url = $('.planet-midi-player').attr('data-midi-url');
			console.log(url);
			MIDIjs.play(url);
			$('.planet-midi-player').attr('data-is-stoped', 'false');
			$('.lyric-preview').find('.lyric-item').removeClass('hilight');
		}
		else
		{
			if($('.planet-midi-player').attr('data-is-playing') == 'true')
			{
				MIDIjs.pause();
				$('.planet-midi-player').attr('data-is-playing', 'false');
			}
			else
			{
				MIDIjs.resume();
				$('.planet-midi-player').attr('data-is-playing', 'true');
			}
		}
	});
	$(document).on('click', '.mp-stop', function(){
		MIDIjs.stop();
		$('.mp-progress-bar-inner').css({'width': '0%'});
		$('.mp-elapsed').text('0:0');
		$('.planet-midi-player').attr('data-is-playing', 'false');
		$('.planet-midi-player').attr('data-is-stoped', 'true');
	});
	$(document).on('click', '.mp-prev', function(){
		playPrev();
	});
	$(document).on('click', '.mp-next', function(){
		playNext();
	});
	
	MIDIjs.message_callback  = function(message){
		$('.mp-status-bar').text(message);
	}
	
	MIDIjs.player_callback = function(message){
		var percent = 100 * message.time / message.duration;
		$('.mp-progress-bar-inner').css({'width': percent+'%'});
		$('.planet-midi-player').attr('data-is-playing', (message.isPlaying?'true':'false'));
		if(message.duration > 0)
		{
			$('.mp-duration').text(toDMS(message.duration));
			$('.mp-elapsed').text(toDMS(message.time));
			updateLyric(message.time);
			updateIndicator(message.time);
		}
		
		
	}
	MIDIjs.on_ended = function(){
		setTimeout(function(){
		$('.planet-midi-player').attr('data-is-playing', 'false');
		$('.planet-midi-player').attr('data-is-stoped', 'true');
		}, 200);
	}
	MIDIjs.on_song_loaded = function(p1, p2, p3, p4, p5)
	{
		$('.mp-song-duration').text(toDMS(p5));	
	}
	MIDIjs.visualization = function(originalBuffer){
		
		visualization(originalBuffer);
	}

	$("#midi-player").on('hide.bs.modal', function(){
		var sys = $('#midi-player').attr('data-system') || 'false';
		if(sys != 'true')
		{
			MIDIjs.stop();
		}
	});
	
});
function visualization(originalBuffer)
{
		var arrayBuff = [];
		var length = 128;
		var peak = 1;
		var i;
		var j;
		var k = -1;
		var sum = 0;
		var l = -1;
		var len = Object.size(originalBuffer);
		var mul = Math.floor(len / length);
		if(mul == 0)
		{
			mul == 1;
		}
		for(i = 0; i < len; i++, l++)
		{
			sum += originalBuffer[i];
			if(l == mul - 1)
			{
				k++;
				l = -1;
				arrayBuff[k] = Math.floor(sum * length / mul);
				sum = 0;
			}
		}
		var canvas = document.querySelector('#canvas');
		var WIDTH = canvas.width;
		var HEIGHT = canvas.height;
		var MID = Math.round(HEIGHT/2);
		
		canvas.width = WIDTH;
		canvas.height = HEIGHT;
		 
		var canvasCtx = canvas.getContext('2d');
	
		canvasCtx.lineWidth = 1;
		canvasCtx.clearRect(0, 0, canvas.width, canvas.height); 
		canvasCtx.strokeStyle = "#FF0000";
		
		var bufferLength = arrayBuff.length;
		var sliceWidth = WIDTH * 1.0 / bufferLength;
		var x = 0;
		
		canvasCtx.beginPath();
		var v, y;
		v = (length - (arrayBuff[i])) / length;
		y = v * HEIGHT;
		canvasCtx.lineTo(x, y-MID);
		for(i = 0; i < bufferLength; i++) 
		{
			v = (length - (arrayBuff[i])) / length;
			y = v * HEIGHT;
			x = i * sliceWidth;
			
			x = Math.round(x);
			canvasCtx.lineTo(x, y-MID);

		}
		canvasCtx.stroke();	
}
function toDMS(input)
{
	var tm = parseInt(input);
	var sec = tm % 60;
	var min = parseInt(Math.floor((tm - sec)/60));
	return min+':'+sec;
}
function edit(id)
{
	$.ajax({
		type:'get',
		dataType:'html',
		url:'ajax-midi.php', 
		data:{action:'select', id:id},
		success:function(data){
			$('.ajax-content-loader-edit-midi').empty().append(data);
			$('#edit').modal('show');			
		}
	});
}
function updateMidi()
{
	var id = $('.ajax-content-loader-edit-midi [name="id"]').val();
	var genre_id = $('.ajax-content-loader-edit-midi [name="genre_id"]').val();
	var artist_id = $('.ajax-content-loader-edit-midi [name="artist_id"]').val();
	var name = $('.ajax-content-loader-edit-midi [name="name"]').val();
	var active = $('.ajax-content-loader-edit-midi [name="active"]')[0].checked?1:0;
	$.ajax({
		type:'post',
		dataType:'json',
		url:'ajax-midi.php', 
		data:{action:'update-midi', id:id, genre_id:genre_id, artist_id:artist_id, name:name, active:active},
		success:function(data){
			$('tr[data-id="'+id+'"]').find('.table-cell').each(function(e){
				var key = $(this).attr('data-key');
				$(this).text(data[key]);
			});
			$('#edit').modal('hide');
		}
	});
}
function dialogChangeGenre()
{
	$.ajax({
		type:'get',
		dataType:'html',
		url:'ajax-midi.php', 
		data:{action:'select-genre-option'},
		success:function(data){
			$('.ajax-content-loader-change-genre').empty().append(data);
			$('#change-genre').modal('show');			
		}
	});
}
function updateGenre()
{
	var genre_id = $('.ajax-content-loader-change-genre [name="genre_id"]').val();
	
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});		
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'update-genre', ids:ids.join(','), genre_id:genre_id},
		success:function(data){
			window.location.reload(); 
		}
	});
}
function dialogChangeArtist()
{
	$.ajax({
		type:'get',
		dataType:'html',
		url:'ajax-midi.php', 
		data:{action:'select-artist-option'},
		success:function(data){
			$('.ajax-content-loader-change-artist').empty().append(data);
			$('#change-artist').modal('show');			
		}
	});
}
function updateArtist()
{
	var artist_id = $('.ajax-content-loader-change-artist [name="artist_id"]').val();
	
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'update-artist', ids:ids.join(','), artist_id:artist_id},
		success:function(data){
			window.location.reload(); 
		}
	});
}
function dialogTrimLeft()
{
	$('#trim-left').modal('show');			
}
function trimLeft()
{
	var text = $('.ajax-content-loader-trim-left [name="text"]').val();
	
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'trim-left', ids:ids.join(','), text:text},
		success:function(data){
			window.location.reload(); 
		}
	});
}
function dialogTrimRight()
{
	$('#trim-right').modal('show');			
}
function trimRight()
{
	var text = $('.ajax-content-loader-trim-right [name="text"]').val();
	
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'trim-right', ids:ids.join(','), text:text},
		success:function(data){
			window.location.reload(); 
		}
	});
}

function dialogReplaceAll()
{
	$('#replace-all').modal('show');			
}
function replaceAll()
{
	var search_for = $('.ajax-content-loader-replace-all [name="search_for"]').val();
	var replace_with = $('.ajax-content-loader-replace-all [name="replace_with"]').val();
	var case_insensitive = $('.ajax-content-loader-replace-all [name="case_insensitive"]').val();
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'replace-all', ids:ids.join(','), search_for:search_for, replace_with:replace_with, case_insensitive:case_insensitive},
		success:function(data){
			window.location.reload(); 
		}
	});
}
function dialogChangeCase()
{
	$('#case-option').modal('show');
}
function changeCase()
{
	var case_option = $('.ajax-content-loader-case-option [name="case"]').val();
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	
	$.ajax({
		type:'post',
		url:'ajax-midi.php', 
		data:{action:'change-case', ids:ids.join(','), case_option:case_option},
		success:function(data){
			window.location.reload(); 
		}
	});
}
var current_index = 0;
function playMidi(url, title, artist, genre, duration, tempo)
{
	current_index = getIndex(url, song_list);
	$('.planet-midi-player').attr('data-midi-url', url);
	$('#midi-player').modal('show');	
	$('.mp-song-title').text(title);	
	$('.mp-song-artist').text(artist);	
	$('.mp-song-genre').text(genre);
	$('.mp-song-duration').text(toDMS(duration));
	$('.mp-song-tempo').text(tempo.toFixed(0));
	MIDIjs.play(url);
	loadLyric(url);
}
var lyric_data = {};
var lyric_time_scale = 1;
var lyric_time_offset = 0;
var lyric_interval_write = 4000;
var lyric_duration = 10000;
var lyric_interval_hiligth = 50;
var lyric_last_time_write = 0;
var lyric_last_time_hiligth = 0;
var tempo = 0;
var withLyric = false;
var timebase = 1;
var timeInfo = [];
function loadLyric(url)
{
	lyric_last_time_write = 0;
	lyric_last_time_hiligth = 0;
	withLyric = false;
	$('.lyric').css('display', 'none');
	$.ajax({
		url:'ajax-get-lyric.php',
		type:'get',
		dataType:'json',
		data:{url:url},
		success:function(data){
			var tracks = getLyricTrack(data);
			if(tracks != null && tracks.length > 0)
			{
				lyric_data = data;
				timebase = data.timebase;
				lyric_time_scale = data.timebase * 1000 / data.tempo;
				timeInfo = data.timeinfo;
				withLyric = true;
				$('.lyric').css('display', 'block');
			}
		}
	});
}
function updateLyric(second)
{
	if($('.lyric-preview').find('.lyric-item').length > 0)
	{
		$('.lyric-preview').find('.lyric-item').each(function(e){
			var tm = parseInt($(this).attr('data-atime'));
			if(tm < (second * 1000))
			{
				$(this).addClass('hilight');
			}
		});
	}

}
var noteMap = {
	21:'A0',
	22:'A#0',
	23:'B#0',
	
	24:'C1',
	25:'C#1',
	26:'D1',
	27:'D#1',
	28:'E1',
	29:'F1',
	30:'F#1',
	31:'G1',
	32:'G#1',
	33:'A1',
	34:'A#1',
	35:'B1',
	
	36:'C2',
	37:'C#2',
	38:'D2',
	39:'D#2',
	40:'E2',
	41:'F2',
	42:'F#2',
	43:'G2',
	44:'G#2',
	45:'A2',
	46:'A#2',
	47:'B2',
	
	48:'C3',
	49:'C#3',
	50:'D3',
	51:'D#3',
	52:'E3',
	53:'F3',
	54:'F#3',
	55:'G3',
	56:'G#3',
	57:'A3',
	58:'A#3',
	59:'B3',

	60:'C4',
	61:'C#4',
	62:'D4',
	63:'D#4',
	64:'E4',
	65:'F4',
	66:'F#4',
	67:'G4',
	68:'G#4',
	69:'A4',
	70:'A#4',
	71:'B4',	

	72:'C5',
	73:'C#5',
	74:'D5',
	75:'D#5',
	76:'E5',
	77:'F5',
	78:'F#5',
	79:'G5',
	80:'G#5',
	81:'A5',
	82:'A#5',
	83:'B5',
	
	84:'C6',
	85:'C#6',
	86:'D6',
	87:'D#6',
	88:'E6',
	89:'F6',
	90:'F#6',
	91:'G6',
	92:'G#6',
	93:'A6',
	94:'A#6',
	95:'B6',
	
	96:'C7',
	97:'C#7',
	98:'D7',
	99:'D#7',
	100:'E7',
	101:'F7',
	102:'F#7',
	103:'G7',
	104:'G#7',
	105:'A7',
	106:'A#7',
	107:'B7',
	
	108:'C8',
	109:'C#8',
	110:'D8',
	111:'D#8',
	123:'E8',
	113:'F8',
	114:'F#8',
	115:'G8',
	116:'G#8',
	117:'A8',
	118:'A#8',
	119:'B8',
	
	120:'C9',
	121:'C#9',
	122:'D9',
	123:'D#9',
	124:'E9',
	125:'F9',
	126:'F#9',
	127:'G9',
	128:'G#9'			
};

function getNoteFromCode(code)
{
	return noteMap[code] || '';
}

function updateIndicator(second)
{
	var milisecond = second * 1000;
	var from = milisecond - 100;
	var to = milisecond + 100;
	
	var note = {};
	var height = 0;
	$('.midi-display').find('.midi-channel').each(function(e){
		var ch_control = $(this).find('div');
		var channel = parseInt($(this).attr('data-channel'));
		one:
		for(var i in lyric_data.note.tracks)
		{
			if(typeof lyric_data.note.tracks[i][channel] != 'undefined')
			{
				ch_control.parent().removeAttr('data-note');
				two:
				for(var j = 0; j < lyric_data.note.tracks[i][channel].length; j++)
				{
					note = lyric_data.note.tracks[i][channel][j];
					if(note.atime >= from && note.atime <= to)
					{
						if(note.event == 'On')
						{
							height = note.velocity * 100 / 255;
							ch_control.parent().attr('data-note', getNoteFromCode(note.note));
						}
						else
						{
							height = 0;
						}
						ch_control.css({'height':height+'%'});
						break one;
					}
				}
			}
		}
		
	});
}
function getLyric(midi)
{
	var lyric = {tracks:[]};
	var line = '';
	var arr = [];
	for(var i in midi.tracks)
	{
		if(midi.tracks[i].length > 0)
		{
			lyric.tracks[i] = [];
			for(var j in midi.tracks[i])
			{
				line = midi.tracks[i][j];
				arr = line.split(' ');
				if(arr[2] == 'Lyric')
				{
					lyric.tracks[i].push(line);
				}
			}
		}
	}
	return lyric;
}
function getTempoData(midi)
{
	var tempo = {tracks:[]};
	var line = '';
	var arr = [];
	for(var i in midi.tracks)
	{
		if(midi.tracks[i].length > 0)
		{
			tempo.tracks[i] = [];
			for(var j in midi.tracks[i])
			{
				line = midi.tracks[i][j];
				arr = line.split(' ');
				if(arr[1] == 'Tempo')
				{
					tempo.tracks[i].push(line);
				}
			}
		}
	}
	return tempo;
}
function getNote(midi, time_data, timebase)
{
	var note = {tracks:[]};
	var line = '';
	var arr = [];
	var notation = {};
	var rtime = 0;
	var atime = 0;
	var channel = 0;
	var tone = 0;
	var velocity = 0;
	var mevent = '';
	for(var i in midi.tracks)
	{
		if(midi.tracks[i].length > 0)
		{
			note.tracks[i] = [];
			for(var j in midi.tracks[i])
			{
				line = midi.tracks[i][j];
				arr = line.split(' ');
				if(arr[1] == 'On' || arr[1] == 'Off')
				{
					//1151 On ch=3 n=54 v=66
					mevent = arr[1];
					rtime = parseInt(arr[0]);
					atime = getTime(midi, rtime, time_data, timebase)
					channel = parseInt(arr[2].substr(3));
					tone = parseInt(arr[3].substr(2));
					velocity = parseInt(arr[4].substr(2));
					notation = {event:mevent, rtime:rtime, atime:atime, channel:channel, note:tone, velocity:velocity};
					if(typeof note.tracks[i][channel] == 'undefined')
					{
						note.tracks[i][channel] = [];
					}
					note.tracks[i][channel].push(notation);
				}
			}
		}
	}
	return note;
}
function getLyricTrack(lyric)
{
	for(var i in lyric.lyric.tracks)
	{
		if(lyric.lyric.tracks[i].length > 0)
		{
			return lyric.lyric.tracks[i];
		}
	}
	return null;
}

function getTempo(tm)
{
	var lastTempo = tempo;
	for(var i in timeInfo)
	{
		if(tm < timeInfo[i][0])
		{
			break;
		}
		if(tm >= timeInfo[i][0])
		{
			lastTempo = timeInfo[i][2];
		}
	}
	return lastTempo;
}
var offsetLyric = 0;

function getTime(lyric, time, time_data, timebase)
{
	time_data = time_data || lyric.time;
	timebase = timebase || lyric.timebase;

	var duration = 0;
	var currentTempo = 0;
	var t = 0;
	
	var dt = 0;    
	
	var f = 1 / timebase / 1000000;
	var tm = 0;
	var msg = [];
	one:
	for(var h in time_data.tracks)
	{
		var trk = time_data.tracks[h];
		var mc = trk.length;
		two:
		for (var i = 0; i < mc; i++)
		{
			msg = trk[i].split(' ');
			tm = parseInt(msg[0]);
			if(tm > time)
			{
				break one;
			}
			if (msg[1]=='Tempo')
			{
				dt = tm - t;
				duration += dt * currentTempo * f;
				t = tm;
				currentTempo = parseInt(msg[2]);
			}
		}
	}
	dt = time - t;
	duration += dt * currentTempo * f;
	return duration * 1000;
}
function renderLyric(lyric, range_start, range_stop)
{
	
	var data = getLyricTrack(lyric);
	var lr = [];
	var time = 0;
	for(var i = 0; i < data.length; i++)
	{
		var arr = data[i].explode(' ', 4);
		time = getTime(lyric, parseInt(arr[0]));
		if(time >= range_start && time <= range_stop)
		{
			var txt = arr[3];
			txt = txt.substring(1, txt.length - 1);
			lr.push('<span class="lyric-item" data-time="'+time+'">'+txt+'</span>');
		}
		if(time > range_stop)
		{
			break;
		}
	}
	lr.push('&nbsp;');
	return lr.join('').split('\n').join('<br>');
}
function getIndex(url, song_list)
{
	var idx = 0;
	for(var i = 0; i<song_list.length; i++)
	{
		if(url == song_list[i].url)
		{
			idx = i;
			break;
		}
	}
	return idx;
}
function playNext()
{
	MIDIjs.stop();
	setTimeout(function(){
	$('.planet-midi-player').attr('data-is-playing', 'false');
	$('.planet-midi-player').attr('data-is-stoped', 'true');
	var sl = song_list.length;
	if(current_index >= (sl-1))
	{
		current_index = 0;
	}
	else
	{
		current_index++;
	}
	var song = song_list[current_index];
	playMidi(song.url, song.title, song.artist, song.genre, song.duration, song.tempo)
	}, 100);
}
function playPrev()
{
	MIDIjs.stop();
	setTimeout(function(){
	$('.planet-midi-player').attr('data-is-playing', 'false');
	$('.planet-midi-player').attr('data-is-stoped', 'true');
	var sl = song_list.length;
	if(current_index < 1)
	{
		current_index = sl - 1;
	}
	else
	{
		current_index--;
	}
	var song = song_list[current_index];
	playMidi(song.url, song.title, song.artist, song.genre, song.duration, song.tempo)
	}, 100);
}
var midiUpdate = function(time) {
}
var midiStop = function() {
	$('#midi-player').attr('data-system', 'true');
	$('#midi-player').modal('hide');
}

function startPlaying() {
	$(".midi-player-container").midiPlayer.play(song);
}
function stopPlaying() {
	$(".midi-player-container").midiPlayer.stop();
}
function dialogDownloadMidi()
{
	$('#download-midi').modal('show');
}
function downloadMidi()
{
	var file_name_option = $('.ajax-content-loader-file-name-option [name="file-name-option"]').val();
	var file_name_separator = $('.ajax-content-loader-file-name-option [name="file-name-separator"]').val();
	var file_name_white_space = $('.ajax-content-loader-file-name-option [name="file-name-white-space"]').val();
	var dos = $('.ajax-content-loader-file-name-option [name="dos"]').val() || '0';
	var ids = [];
	$('.table-check-target').each(function(e){
		if($(this)[0].checked)
		{
			ids.push($(this).val());
		}
	});
	window.open('download.php?action=download-midi&ids='+ids+'&file_name_option='+file_name_option+'&file_name_separator='+file_name_separator+'&file_name_white_space='+file_name_white_space+'&dos='+dos);
}