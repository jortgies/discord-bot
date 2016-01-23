var discordBot = angular.module('discordBot', []);

function mainController($scope, $http) {
    $scope.formData = {};

    $http.get('/api/sounds/files')
        .success(function(data) {
            $scope.files = data;
            console.log(data);
        })
        .error(function(data) {
            console.log('Error: ' + data);
        });

    $scope.play = function(file) {
        $http.post('/api/sounds/play', { filename: file })
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.sendMessage = function() {
        $http.post('/api/text/send', { guildName: 'HerpDerp', channelName: 'general', text: $scope.formData.text})
            .success(function(data) {
                $scope.formData = {};
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceJoin = function(file) {
        $http.post('/api/voice/join', { guildName: 'HerpDerp', channelName: 'general'})
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };

    $scope.voiceLeave = function(file) {
        $http.get('/api/voice/leave')
            .success(function(data) {
                console.log(data);
            })
            .error(function(data) {
                console.log('Error: ' + data);
            });
    };
}