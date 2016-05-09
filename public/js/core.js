var discordBot = angular.module('discordBot', [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

function mainController($scope, $http) {
    $scope.formData = {};
    $scope.guilds = {};
    $scope.channels = {};

    var basePath = 'https://bot.ortgies.it/api';

    $http.get(basePath + '/sounds/files')
        .success(function(data) {
            $scope.files = data;
            console.log(data);
        })
        .error(function(data) {
            console.log('Error: ' + data);
        });

    $http.get(basePath + '/voice/guilds')
        .success(function(data) {
            $scope.guilds = data;
        });

    $scope.loadChannels = function() {
        $http.get(basePath + '/voice/channels/' + $scope.voiceGuild.id)
            .success(function(data) {
                $scope.channels = data;
            });
    };

    $scope.play = function(file) {
        $http.post(basePath + '/sounds/play', { filename: file })
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.stop = function() {
        $http.get(basePath + '/sounds/stop')
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.sendMessage = function() {
        $http.post(basePath + '/text/send', { guildName: 'HerpDerp', channelName: 'general', text: $scope.formData.text})
            .success(function(data) {
                $scope.formData = {};
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceJoin = function() {
        $http.post(basePath + '/voice/join', { guildId: $scope.voiceGuild.id, channelId: $scope.voiceChannel.id})
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceLeave = function() {
        $http.get(basePath + '/voice/leave')
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };
}
