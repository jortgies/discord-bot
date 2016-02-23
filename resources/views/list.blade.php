@extends('templates.master')
@section('content')
    <div class="container">

        <div id="sounds" class="row">
            <div class="col-sm-6 col-sm-offset-2">

                <div class="buttons" style="display: inline;" ng-repeat="file in files">
                    <button class="btn btn-lg btn-info" ng-click="play(file.name)"> <% file.name %></button>
                </div>
                <button class="btn btn-lg btn-info" ng-click="stop()"> Stop </button>

            </div>
        </div>
        <br/>
        <div id="form" class="row">
            <div class="col-sm-6 col-sm-offset-2 text-center">
                <form>
                    <div class="form-group">
                        <input type="text" class="form-control input-lg text-center" placeholder="blabla" ng-model="formData.text">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" ng-click="sendMessage()">Send</button>
                </form>
            </div>
        </div>
        <br/>
        <div id="control" class="row">
            <div class="col-sm-6 col-sm-offset-2">
                <button type="submit" class="btn btn-primary btn-lg" ng-click="voiceJoin()">Join Voice Channel</button>
                <button type="submit" class="btn btn-primary btn-lg" ng-click="voiceLeave()">Leave Voice Channel</button>
            </div>
        </div>
    </div>
@endsection