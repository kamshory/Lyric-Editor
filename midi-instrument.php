<?php
use Midi\MidiInstrument;

require_once "inc/auth-with-login-form.php";

if(isset($song))
{
$midi = new MidiInstrument();

$midi->importMid($song->getFilePathMidi());
//$midi->importMid("files/Beraksi.mid");

$list = $midi->getInstrument();

$instrumentName = $midi->getInstrumentList();
?>
<style>
    .selectize-control{
        display: inline-block;
    }
    .selectize-input{
        width: 100%;
        min-width: 300px;
        box-sizing: border-box;
        vertical-align: middle;
    }
    .selectize-control.single .selectize-input::after{
        right: 10px !important;
    }
    input[type="select-one"]{
        box-sizing: border-box;
    }
    .track-label, .channel-label{
        padding: 2px 0;
    }
    
    select{
        max-width: 100%;
        width: 300px;
        box-sizing: border-box;
    }
    
    ul.midi-program{
        padding: 0 0;
        margin: 0;
        list-style-type: none;
        padding-left: 20px;
    }
    ul.midi-program > li{
        padding: 0 0 0 0;
        margin: 0;
    }
    ul.channel-child{
        padding: 0;
        margin: 0;
        list-style-type: none;
        padding-left: 20px;
    }
    ul.channel-child > li{
        padding: 0;
        margin: 0;
    }
</style>
<form action="">
<ul class="midi-program">

<?php
foreach($list->program->parsed as $trackNumber=>$track)
{
    ?>
    <li class="midi-track" data-track-number="<?php echo $trackNumber;?>">
    
    <div class="track-label">Track <?php echo $trackNumber;?></div>
    <?php
    
    $inst = array();
    foreach($track as $index=>$instrument)
    {
        $inst[] = $instrument['program'];
        $ch[] = $instrument['channel'];
    }
    $inst = array_unique($inst);
    $ch = array_unique($ch);
    $parentInst = count($inst) == 1 ? $inst[0] : "";
    ?>
    <div class="select-wrapper" data-track-number="<?php echo $trackNumber;?>">
    
    <?php
    if(count($inst) == 1)
    {
    ?>
    <select class="channel-parent" data-value="<?php echo $parentInst;?>"></select> 
    <button type="button" class="btn btn-primary apply-to-all">Apply To All</button>
    <?php
    }
    ?>
    </div>
    <ul class="channel-child">
    <?php
    foreach($track as $index=>$instrument)
    {
        ?>
        <li class="midi-channel" data-index="<?php echo $index;?>" data-channel-number="<?php echo $instrument['channel'];?>">
            <div>
            <div class="channel-label">Channel <?php echo $instrument['channel'] + 1;?></div>
            <div class="select-wrapper">
            <select class="midi-instrument" data-value="<?php echo $instrument['program'];?>"></select>
            </div>
            </div>
        </li>
        <?php
    }
    ?>
    </ul>
    </li>
    <?php
}

?>
</ul>
<input type="button" class="btn btn-success" value="Update Instrument" onclick="getData();">
</form>
<script>
    let midiProgram = <?php echo json_encode($list->program->parsed);?>; 
    let instrumentList = <?php echo json_encode($instrumentName, JSON_FORCE_OBJECT);?>; 
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="assets/css/selectize.css" />

<script>
    function getData()
    {
        let tracks = document.querySelectorAll('li.midi-track');
        let trackData = [];
        for(let i = 0; i < tracks.length; i++)
        {
            let track = tracks[i];
            let trackNumber = track.getAttribute('data-track-number');
            let channels = track.querySelectorAll('li.midi-channel');
            let channelData = [];
            for(let j = 0; j < channels.length; j++)
            {
                let channel = channels[j];
                let channelNumber = channel.getAttribute('data-channel-number');
                let index = channel.getAttribute('data-index');
                let program = channel.querySelector('select').value;
                channelData.push({
                    'channel':channelNumber,
                    'index':index,
                    'program':program
                });
            }
            trackData[trackNumber] = channelData;
        }
        console.log(trackData);
    }
    window.onload = function(){
        for(let t in midiProgram)
        {
            if(midiProgram.hasOwnProperty(t))
            {
                let track = midiProgram[t];
                for(let i in track)
                {
                    let channel = track[i].channel;
                    let value = track[i].program;
                    appendOptionParent(t, value, instrumentList);
                    appendOptionChild(t, channel, i, value, instrumentList);
                }
            }
        }
        
        let btns = document.querySelectorAll('.apply-to-all');
        for(let i = 0; i < btns.length; i++)
        {
            let btnx = btns[i];
            btnx.addEventListener('click', function(e){  
                let btn = e.target;            
                let par = $(btn).closest('div')[0];
                let value = par.querySelector('select.channel-parent').value;
                let trackNumber = par.getAttribute('data-track-number');
                let grandPar = $(par).closest('li')[0];
                let chs = grandPar.querySelectorAll('ul.channel-child li.midi-channel');
                for(let j = 0; j < chs.length; j++)
                {
                    let ch = chs[j];
                    let channelNumber = ch.getAttribute('data-channel-number');
                    let index = ch.getAttribute('data-index');
                    updateOptionChild(trackNumber, channelNumber, index, value);
                }
            });
        }
    };
    function appendOptionParent(track, value, instList)
    {
        let selector = document.querySelector('li.midi-track[data-track-number="'+track+'"] select.channel-parent');
        if(selector != null)
        {
            selector.appendChild(new Option('— Select One —', ''));
            for(let p in instList)
            {
                if(instList.hasOwnProperty(p))
                {
                    let opt = new Option(p+' — '+instList[p], p);
                    selector.appendChild(opt);
                }
            }
            value = value + ''; // convert to string
            if(value != '')
            {
                selector.value = value;
            }
            $(selector).selectize({
                //sortField: 'text'
            });
        }
    }
    function updateOptionParent(track, value)
    {
        let selector = document.querySelector('li.midi-track[data-track-number="'+track+'"] select.channel-parent');
        if(selector != null)
        {
            value = value + ''; // convert to string
            if(value != '')
            {
                selector.value = value;
            }
            $(selector).selectize()[0].selectize.destroy();
            $(selector).selectize({
                //sortField: 'text'
            });
        }
    }
    function appendOptionChild(track, channel, index, value, instList)
    {
        let selector = document.querySelector('li.midi-track[data-track-number="'+track+'"] ul.channel-child li.midi-channel[data-index="'+index+'"][data-channel-number="'+channel+'"] select');
        for(let p in instList)
        {
            if(instList.hasOwnProperty(p))
            {
                let opt = new Option(p+' — '+instList[p], p);
                selector.appendChild(opt);
            }
        }
        value = value + ''; // convert to string
        if(value != '')
        {
            selector.value = value;
        }
        $(selector).selectize({
            //sortField: 'text'
        });
    }
    function updateOptionChild(track, channel, index, value)
    {
        let selector = document.querySelector('li.midi-track[data-track-number="'+track+'"] ul.channel-child li.midi-channel[data-index="'+index+'"][data-channel-number="'+channel+'"] select');
        if(selector != null)
        {
            $(selector).selectize()[0].selectize.destroy();
            value = value + ''; // convert to string
            if(value != '')
            {
                selector.value = value;
            }           
            $(selector).selectize({
                //sortField: 'text'
            });
        }
    }
</script>
<?php
}
?>