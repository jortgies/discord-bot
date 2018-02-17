@extends('templates.master')
@section('content')
    <div class="container">

        <div id="sounds" class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <table id="files" class="row-border hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Filename</th>
                            @if(env('ENABLE_LENGTH_DETECTION', false))
                                <th>Length</th>
                            @endif
                            @if(env('ENABLE_WAVEFORM', false))
                                <th>Waveform</th>
                            @endif
                            <th>Play</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allFiles as $file)
                        <tr>
                            <td>{{$file['id']}}</td>
                            <td>{{$file['name']}}</td>
                            @if(env('ENABLE_LENGTH_DETECTION', false))
                                <td>{{$file['length']}}</td>
                            @endif
                            @if(env('ENABLE_WAVEFORM', false))
                                <td><img src="{{asset($file['waveform'])}}" /></td>
                            @endif
                            <td><button class="btn btn-sm btn-info" ng-click='play("{{$file['name']}}")'><span class="glyphicon glyphicon-play" aria-hidden="true"></span></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-lg btn-info" ng-click="stop()"> Stop </button>
                <button class="btn btn-lg btn-info" ng-click="random()"> Random Sound </button>
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
        <div id="form" class="row">
            <div class="col-sm-6 col-sm-offset-2 text-center">
                <form>
                    <div class="form-group">
                        <input type="text" class="form-control input-lg text-center" placeholder="youtube link" ng-model="formData.url">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" ng-click="playYoutube()">Stream</button>
                </form>
            </div>
        </div>
        <br/>
        <div id="control" class="row">
            <div class="col-sm-6 col-sm-offset-2">
                <select id="selectedGuilds" ng-options="guild as guild.name for guild in guilds" ng-model="voiceGuild" ng-change="loadChannels()"></select>
                <select id="selectedChannel" ng-options="channel as channel.name for channel in channels" ng-model="voiceChannel"></select>
                <br/>
                <button type="submit" class="btn btn-primary btn-lg" ng-click="voiceJoin()">Join Voice Channel</button>
                <button type="submit" class="btn btn-primary btn-lg" ng-click="voiceLeave()">Leave Voice Channel</button>
            </div>
        </div>
    </div>
@endsection