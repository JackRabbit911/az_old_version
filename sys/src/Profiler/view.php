<style type="text/css">
    div#_profiler {
        width: 100%;
        height: 30px;
        background-color: dimgray;
        color: white;
        padding: 6px 10px 2px 10px;
        text-align: center;
        position: fixed;
        bottom: 0;
    }

    div#_queries {
        position: fixed;
        bottom: 30px;
        right: 0px;
        background-color:darkgrey;
        display: none;
        padding: 15px 20px 0px 5px;
    }
</style>

<script>
    function ura() {
        alert("qq");
    }
</script>

<div id="_profiler" onclick="getElementById('_queries').style.display='block'">
    Size: <?=$size?>, 
    Time: <?=$time?>, 
    RAM: <?=$memory?>, 
    Queries count: <?=$queries?>, 
    Queries duration: <?=$duration?>
</div>

<div id="_queries" onclick="this.style.display='none'">
    <ol>
        <? foreach ($profiles as $profile): ?>
            <li><?=$profile?></li>
        <? endforeach; ?>    
    </ol>
</div>
