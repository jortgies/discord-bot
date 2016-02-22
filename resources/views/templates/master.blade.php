<!DOCTYPE html>
<html ng-app="discordBot">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>discordBot</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.8/angular.min.js"></script>
    <link rel="stylesheet" href="{{asset("css/bootstrap.min.css")}}" />
    <link rel="stylesheet" href="{{asset("css/navigation.css")}}" />
    <script src="{{asset("js/jquery-2.2.0.min.js")}}"></script>
    <script src="{{asset("js/bootstrap.min.js")}}"></script>
    <script src="{{asset("js/core.js")}}"></script>
</head>
<body ng-controller="mainController">
    @include("templates.navigation")
    @yield("content")
    @include("templates.footer")
</body>
</html>
