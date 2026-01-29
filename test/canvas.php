<?php
$secondLength=5;
$timelineLeftGap=103;
$timelineRightGap=100;
$timelineLength=30*60+1;
$timelineRealEnd=$timelineLeftGap+$secondLength*$timelineLength;
$canvasWidth=$timelineRealEnd+$timelineRightGap;
?>
<html>
<head>
</head>
<body>
    <canvas id="mycanvas" width="<?= $canvasWidth ?>" height="200" style="border:1px solid #000000;margin-right:7px;">
    </canvas>
    <script type="text/javascript">
        var c = document.getElementById("mycanvas");
        var ctx = c.getContext("2d");
        ctx.lineWidth = 1;
        ctx.moveTo(<?= $timelineLeftGap ?>, 14);
        ctx.lineTo(<?= $timelineLeftGap ?>, 190);
        ctx.stroke();
        ctx.font = "12px Arial";
        let lineSpace=20;
        let textMidV=4;
        let timelineMidV=0;
        let timelineBegin=<?= $timelineLeftGap ?>;
        let timelineEnd=<?= $timelineLeftGap+$secondLength*$timelineLength ?>;
        let data=[
            {name:"Coal",startH:76,period:1.84},
            {name:"Graphite",startH:55,period:5},
            {name:"Iron",startH:80,period:7.23},
            {name:"Iron bar",startH:59,period:15},
            {name:"Steel bar",startH:52,period:45},
            {name:"Steel Plate",startH:43,period:120},
            {name:"Diamond",startH:52,period:5.56},
            {name:"Polished Diamond",startH:3,period:60},
            {name:"Diamond Cutter",startH:16,period:30}
        ];
        let currentLine=20;
        data.forEach(function(res) {
            let textY=currentLine+textMidV;
            let lineY=currentLine+timelineMidV;
            ctx.fillText(res.name, res.startH, textY);
            ctx.moveTo(timelineBegin, lineY);
            ctx.lineTo(timelineEnd, lineY);
            ctx.stroke();
            let periodLength=5*res.period;
            let xVal=timelineBegin+periodLength;
            let i=0;
            for (; xVal<timelineEnd; i++,xVal+=periodLength) {
                ctx.moveTo(xVal, lineY-2);
                ctx.lineTo(xVal, lineY+2);
                ctx.stroke();
            }
            ctx.fillText(""+i, <?= $timelineRealEnd+10 ?>, textY);
            currentLine+=lineSpace;
        });
    </script>
</body>
</html>
