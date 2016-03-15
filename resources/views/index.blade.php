<!DOCTYPE html>
<html>
  <head>
  
  	<title>9GAG instagram</title>
    <base href="http://9gagtest2.noip.me/" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

	<meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">

</head>
<body>

<div id="header"></div>
<div id="container"></div>


<script src="/js/lib/combined.20150905.js"></script>

<script type="text/javascript">
$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
});
</script>
<script src="/js/build/render.js"></script>
</body>