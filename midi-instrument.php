<?php
use Midi\MidiInstrument;

require_once "inc/auth-with-login-form.php";

$midi = new MidiInstrument();

$midi->importMid("files/0652fa7787de636f90e6.mid");

$list = $midi->getInstrument();

$instrumentName = $midi->getInstrumentList();
?>
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
    }
    $inst = array_unique($inst);
    $parentInst = count($inst) == 1 ? $inst[0] : "";
    ?>
    <select class="channel-parent" data-value="<?php echo $parentInst;?>"></select> <button>Apply To All</button>
    <ul class="channel-child">
    <?php
    foreach($track as $index=>$instrument)
    {
        ?>
        <li class="midi-channel" data-index="<?php echo $index;?>" data-channel-number="<?php echo $instrument['channel'];?>">
            <div class="channel-label">Channel <?php echo $instrument['channel'] + 1;?></div>
            <select class="midi-instrument" data-value="<?php echo $instrument['program'];?>"></select>
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
<script>
    let midiProgram = <?php echo json_encode($list->program->parsed);?>; 
    let instrumentList = <?php echo json_encode($instrumentName, JSON_FORCE_OBJECT);?>; 
</script>
<script>
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
                    appendOptionChild(t, channel, i, value, instrumentList);
                }
            }
        }
    };
    function appendOptionChild(track, channel, index, value, instList)
    {
        console.log(track, channel, index, value);
        let selector = document.querySelector('li.midi-track[data-track-number="'+track+'"] ul.channel-child li.midi-channel[data-index="'+index+'"][data-channel-number="'+channel+'"] select');
        for(let p in instList)
        {
            if(instList.hasOwnProperty(p))
            {
                let opt = new Option(instList[p], p);
                //let opt = document.createElement('option');
                //opt.value = p;
                //opt.innerHTML = instList[p];
                selector.appendChild(opt);
            }
        }
        selector.value = value;
    }
</script>