<?php
include_once 'allStaff.class.php';
$staff = new allStaff();
$start = $staff->timerStart();
$che = true;

// Check FindPeople
$result = exec('php FindPeopleTest.php');
if ($result != 'ok') $che = false;

if ($che) {
    $line1 = 'All';
    $line2 = 'Tests';
    $line3 = 'Passed!!!';
    $err = '';
}
else {
    $line1 = 'Shit';
    $line2 = 'happened';
    $line3 = 'bro(';
    $err = 'red_h1';
}
$time = $staff->timerStop($start);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Tester page</title>

    <link href='http://fonts.googleapis.com/css?family=Luckiest+Guy' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/2.1.0/normalize.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>

<div id="intro">
    <h1 id="title" class="<?=$err?>">
        <span id="title-line1" class="title-line"><?=$line1?></span>
        <span id="title-line2" class="title-line"><?=$line2?></span>
        <span id="title-line3" class="title-line"><?=$line3?></span>
    </h1>
</div>

<div id="content-wrapper">
    <div class="space"></div>
    <div id="fly-it"><h2>In time:</h2></div>
    <div id="spin-it"><h2><?=$time?></h2></div>
    <div class="space"></div>
    <div class="space" id="fade-it"><h3>The end.</h3></div>
</div>

<script type="text/javascript" src="js/greensock/TweenMax.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/jquery-1.9.1.min.js"><\/script>')</script>
<script src="js/jquery.lettering-0.6.1.min.js"></script>
<script src="js/jquery.superscrollorama.js"></script>
<script>
    $(document).ready(function() {
        $('body').css('visibility','visible');

        // hide content until after title animation
        $('#content-wrapper').css('display','none');

        // lettering.js to split up letters for animation
        $('#title-line1').lettering();
        $('#title-line2').lettering();
        $('#title-line3').lettering();

        // TimelineLite for title animation, then start up superscrollorama when complete
        (new TimelineLite({onComplete:initScrollAnimations}))
            .from( $('#title-line1 span'), .3, {delay: 1, css:{right:'1000px'}, ease:Back.easeOut})
            .from( $('#title-line2'), .3, {css:{top:'1000px',opacity:'0'}, ease:Expo.easeOut})
            .append([
                TweenMax.from( $('#title-line3 .char1'), .25+Math.random(), {css:{top: '-200px', right:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char2'), .25+Math.random(), {css:{top: '300px', right:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char3'), .25+Math.random(), {css:{top: '-400px', right:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char4'), .25+Math.random(), {css:{top: '-200px', left:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char6'), .25+Math.random(), {css:{top: '-200px', left:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char7'), .25+Math.random(), {css:{top: '-200px', left:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char8'), .25+Math.random(), {css:{top: '-200px', left:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char9'), .25+Math.random(), {css:{top: '-200px', left:'1000px'}, ease:Elastic.easeOut}),
                TweenMax.from( $('#title-line3 .char5'), .25+Math.random(), {css:{top: '200px', left:'1000px'}, ease:Elastic.easeOut})
            ])
            .to( $('#title-info'), .3, {css:{opacity:.99, 'margin-top':0}, delay:-1, ease:Quad.easeOut});


        function initScrollAnimations() {
            $('#content-wrapper').css('display','block');
            var controller = $.superscrollorama();

            // title tweens
            $('.title-line span').each(function() {
                controller.addTween(10, TweenMax.to(this, .5, {css:{top: Math.random()*-200-600, left: (Math.random()*1000)-500, rotation:Math.random()*720-360, 'font-size': Math.random()*300+150}, ease:Quad.easeOut}),200);
            });
            controller.addTween(10, TweenMax.to($('#title-line1'), .75, {css:{top: 600}, ease:Quad.easeOut}),200);
            controller.addTween(10, TweenMax.to($('#title-line2'), .75, {css:{top: 200}, ease:Quad.easeOut}),200);
            controller.addTween(10, TweenMax.to($('#title-line3'), .75, {css:{top: -100}, ease:Quad.easeOut},200));

            // individual element tween examples
            controller.addTween('#fade-it', TweenMax.from( $('#fade-it'), .5, {css:{opacity: 0}}));
            controller.addTween('#fly-it', TweenMax.from( $('#fly-it'), .25, {css:{right:'1500px'}, ease:Quad.easeInOut}));
            controller.addTween('#spin-it', TweenMax.from( $('#spin-it'), .25, {css:{opacity:0, rotation: 720}, ease:Quad.easeOut}));
            controller.addTween('#scale-it', TweenMax.fromTo( $('#scale-it'), .25, {css:{opacity:0, fontSize:'20px'}, immediateRender:true, ease:Quad.easeInOut}, {css:{opacity:1, fontSize:'240px'}, ease:Quad.easeInOut}));
            controller.addTween('#smush-it', TweenMax.fromTo( $('#smush-it'), .25, {css:{opacity:0, 'letter-spacing':'30px'}, immediateRender:true, ease:Quad.easeInOut}, {css:{opacity:1, 'letter-spacing':'-10px'}, ease:Quad.easeInOut}), 0, 100); // 100 px offset for better timing

        }
    });
</script>

</body></html>
