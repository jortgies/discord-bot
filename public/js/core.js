var discordBot = angular.module('discordBot', [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

function mainController($scope, $http) {
    $scope.formData = {};

    var basePath = 'https://bot.ortgies.it';

    $http.get(basePath + '/api/sounds/files')
        .success(function(data) {
            $scope.files = data;
            console.log(data);
        })
        .error(function(data) {
            console.log('Error: ' + data);
        });

    $scope.play = function(file) {
        $http.post(basePath + '/api/sounds/play', { filename: file })
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.sendMessage = function() {
        $http.post(basePath + '/api/text/send', { guildName: 'HerpDerp', channelName: 'general', text: $scope.formData.text})
            .success(function(data) {
                $scope.formData = {};
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceJoin = function(file) {
        $http.post(basePath + '/api/voice/join', { guildName: 'HerpDerp', channelName: 'general'})
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceLeave = function(file) {
        $http.get(basePath + '/api/voice/leave')
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };
}
